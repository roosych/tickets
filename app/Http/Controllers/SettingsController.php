<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\Departments\StoreDepartmentRequest;
use App\Http\Requests\Settings\Departments\UpdateDepartmentRequest;
use App\Enums\TicketStatusEnum;
use App\Models\Department;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function users()
    {
        $users = User::query()
            ->with('departmentRelation')
            ->withCount(['ticketsByExecutor as open_tickets_count' => fn ($q) => $q->whereIn('status', [
                TicketStatusEnum::OPENED->value,
                TicketStatusEnum::IN_PROGRESS->value,
            ])])
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $users->count(),
            'active' => $users->where('active', true)->count(),
            'inactive' => $users->where('active', false)->count(),
            'hidden' => $users->where('visible', false)->count(),
            'approvers' => $users->where('is_approver', true)->count(),
            'inactive_with_tickets' => $users->where('active', false)->where('open_tickets_count', '>', 0)->count(),
        ];

        $stats['no_department'] = $users->where('active', true)->whereNull('department_id')->count();

        $departments = Department::query()->orderBy('name')->get(['id', 'name', 'active']);

        return view('cabinet.settings.users', compact('users', 'stats', 'departments'));
    }

    public function departments()
    {
        $departments = Department::query()
            ->with(['manager', 'users'])
            ->withCount([
                'tags',
                'tickets as open_tickets_count' => fn ($q) => $q->whereIn('status', [
                    TicketStatusEnum::OPENED->value,
                    TicketStatusEnum::IN_PROGRESS->value,
                ]),
            ])
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $departments->count(),
            'active' => $departments->where('active', true)->count(),
            'inactive' => $departments->where('active', false)->count(),
            'no_manager' => $departments->whereNull('manager_id')->count(),
            'users' => $departments->sum(fn ($d) => $d->users->count()),
        ];

        return view('cabinet.settings.departments.index', compact('departments', 'stats'));
    }

    public function storeDepartment(StoreDepartmentRequest $request)
    {
        $department = Department::create($request->validated());

        return redirect()
            ->route('cabinet.settings.departments.show', $department)
            ->with('success', 'Департамент создан. Назначьте менеджера и сотрудников.');
    }

    // Страница конкретного департамента
    public function show(Department $department)
    {
        $openStatuses = [TicketStatusEnum::OPENED->value, TicketStatusEnum::IN_PROGRESS->value];

        // Состав отдела, включая неактивных и скрытых
        $members = $this->memberQuery($department)->get();

        // Кого можно добавить: активные и видимые сотрудники не из этого отдела
        $candidates = User::query()
            ->with('departmentRelation')
            ->withCount(['ticketsByExecutor as open_tickets_count' => fn ($q) => $q->whereIn('status', $openStatuses)])
            ->where('active', true)
            ->where('visible', true)
            ->where(fn ($q) => $q->whereNull('department_id')->orWhere('department_id', '!=', $department->id))
            ->when($department->manager_id, fn ($q) => $q->where('id', '!=', $department->manager_id))
            ->orderBy('name')
            ->get();

        // Руководители других отделов — их нельзя перетаскивать
        $otherManagers = Department::query()
            ->whereNotNull('manager_id')
            ->where('id', '!=', $department->id)
            ->pluck('name', 'manager_id');

        // Кандидаты в менеджеры: активные участники + все активные видимые
        $managerOptions = $members->where('active', true)
            ->merge($candidates)
            ->when($department->manager, fn ($c) => $c->push($department->manager))
            ->unique('id')
            ->sortBy('name');

        $department->loadCount([
            'tags',
            'roles',
            'tickets as total_tickets_count',
            'tickets as open_tickets_count' => fn ($q) => $q->whereIn('status', $openStatuses),
        ]);

        // Менеджер может числиться в другом отделе — в счётчиках и блокировке удаления его не учитываем
        $ownMembers = $members->where('department_id', $department->id);

        $stats = [
            'members_active' => $ownMembers->where('active', true)->count(),
            'members_total' => $ownMembers->count(),
            'open_tickets' => $department->open_tickets_count,
            'tags' => $department->tags_count,
            'roles' => $department->roles_count,
        ];

        $deleteBlockers = array_values(array_filter([
            $ownMembers->count() ? "сотрудников: {$ownMembers->count()}" : null,
            $department->total_tickets_count ? "тикетов: {$department->total_tickets_count}" : null,
            $department->tags_count ? "тегов: {$department->tags_count}" : null,
            $department->roles_count ? "ролей: {$department->roles_count}" : null,
        ]));

        return view('cabinet.settings.departments.show', compact(
            'department', 'members', 'candidates', 'otherManagers', 'managerOptions', 'stats', 'deleteBlockers'
        ));
    }

    public function addMembers(Request $request, Department $department)
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        // Руководитель другого отдела тоже может быть перенесён: свой прежний отдел
        // он продолжит видеть через переключатель, так как остаётся там менеджером
        $ids = User::whereIn('id', $data['user_ids'])
            ->where(fn ($q) => $q->whereNull('department_id')->orWhere('department_id', '!=', $department->id))
            ->pluck('id')
            ->all();

        if ($ids) {
            User::whereIn('id', $ids)->update(['department_id' => $department->id]);
        }

        $rows = $this->memberQuery($department)->whereIn('id', $ids)->get()
            ->map(fn ($user) => view('cabinet.settings.departments.partials.member-row', ['user' => $user, 'department' => $department])->render())
            ->all();

        return response()->json([
            'added' => $ids,
            'rows' => $rows,
            'blocked' => [],
            'total' => User::where('department_id', $department->id)->count(),
        ]);
    }

    public function removeMember(Department $department, User $user)
    {
        if ($user->department_id != $department->id) {
            return response()->json(['message' => 'Сотрудник уже не состоит в этом отделе.'], 422);
        }
        if ($department->manager_id == $user->id) {
            return response()->json(['message' => 'Нельзя убрать менеджера. Сначала назначьте другого.'], 422);
        }

        $user->update(['department_id' => null]);

        return response()->json([
            'removed' => $user->id,
            'total' => User::where('department_id', $department->id)->count(),
        ]);
    }

    private function memberQuery(Department $department)
    {
        return User::query()
            ->with('departmentRelation')
            ->withCount(['ticketsByExecutor as open_tickets_count' => fn ($q) => $q->whereIn('status', [
                TicketStatusEnum::OPENED->value,
                TicketStatusEnum::IN_PROGRESS->value,
            ])])
            ->withCount('tickets as created_tickets_count')
            ->where(function ($q) use ($department) {
                $q->where('department_id', $department->id);
                if ($department->manager_id) {
                    $q->orWhere('id', $department->manager_id);
                }
            })
            ->orderByDesc('active')
            ->orderBy('name');
    }

    public function destroy(Department $department)
    {
        $blockers = [
            User::where('department_id', $department->id)->exists(),
            $department->tickets()->exists(),
            $department->tags()->exists(),
            $department->roles()->exists(),
        ];

        if (in_array(true, $blockers, true)) {
            return redirect()
                ->route('cabinet.settings.departments.show', $department)
                ->with('error', 'Департамент нельзя удалить: в нём есть сотрудники, тикеты, теги или роли.');
        }

        $department->delete();

        return redirect()
            ->route('cabinet.settings.departments')
            ->with('success', 'Департамент удалён');
    }


    public function store(UpdateDepartmentRequest $request, Department $department)
    {
        $data = $request->validated();

        $oldManagerId = $department->manager_id;
        $newManagerId = $data['manager_id'] ?? null;

        DB::transaction(function () use ($department, $data, $oldManagerId, $newManagerId) {
            $department->update([
                'name' => $data['name'],
                'manager_id' => $newManagerId,
                'active' => $data['active'],
                'tg_chat_id' => $data['tg_chat_id'] ?? null,
                'tg_topic_id' => $data['tg_topic_id'] ?? null,
            ]);

            // Менеджер без отдела становится сотрудником этого отдела. Если он уже числится в другом
            // отделе — не переносим: этот отдел будет доступен ему через переключатель в шапке.
            if ($newManagerId) {
                User::whereKey($newManagerId)->whereNull('department_id')->update(['department_id' => $department->id]);
            }

            if ($oldManagerId != $newManagerId) {
                $allPermissionIds = Permission::pluck('id')->toArray();

                // Права у прежнего менеджера снимаем, только если он больше нигде не руководит
                $stillManages = $oldManagerId && Department::where('manager_id', $oldManagerId)->exists();
                if ($oldManagerId && ! $stillManages && ($data['revoke_old_manager'] ?? true)) {
                    User::find($oldManagerId)?->permissions()->detach($allPermissionIds);
                }

                User::find($newManagerId)?->permissions()->syncWithoutDetaching($allPermissionIds);
            }
        });

        return redirect()->route('cabinet.settings.departments.show', $department)
            ->with('success', 'Департамент успешно обновлен');
    }

    public function toggleUserSetting(Request $request, $userId, $setting)
    {
        $user = User::findOrFail($userId);

        if (!in_array($setting, ['visible', 'active', 'email_notify', 'tg_notify', 'is_approver'], true)) {
            return response()->json(['error' => 'Неправильная настройка'], 400);
        }

        $user->$setting = $request->has('value')
            ? $request->boolean('value')
            : !$user->$setting;
        $user->save();

        return response()->json(['success' => true, 'value' => $user->$setting]);
    }

    public function updateUserDepartment(Request $request, User $user)
    {
        $data = $request->validate([
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);
        $newId = $data['department_id'] ?? null;

        $user->department_id = $newId;
        $user->save();
        $user->load('departmentRelation');

        return response()->json([
            'department_id' => $newId,
            'html' => view('cabinet.settings.partials.user-department-cell', compact('user'))->render(),
        ]);
    }
}
