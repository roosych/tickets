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

        $departments = $users->pluck('departmentRelation')->filter()->unique('id')->sortBy('name')->values();

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

        // Все текущие участники (в т.ч. неактивные и скрытые) + доступные для добавления
        $users = User::query()
            ->with('departmentRelation')
            ->withCount(['ticketsByExecutor as open_tickets_count' => fn ($q) => $q->whereIn('status', $openStatuses)])
            ->where(function ($query) use ($department) {
                $query->where('department_id', $department->id)
                    ->orWhere(fn ($q) => $q->where('visible', true)->where('active', true));
            })
            ->orderBy('name')
            ->get();

        $department->loadCount([
            'tags',
            'roles',
            'tickets as total_tickets_count',
            'tickets as open_tickets_count' => fn ($q) => $q->whereIn('status', $openStatuses),
        ]);

        $members = $users->where('department_id', $department->id);
        $memberTotal = User::where('department_id', $department->id)->count();

        $stats = [
            'members_active' => $members->where('active', true)->count(),
            'members_total' => $memberTotal,
            'open_tickets' => $department->open_tickets_count,
            'tags' => $department->tags_count,
            'roles' => $department->roles_count,
        ];

        $deleteBlockers = array_values(array_filter([
            $memberTotal ? "сотрудников: {$memberTotal}" : null,
            $department->total_tickets_count ? "тикетов: {$department->total_tickets_count}" : null,
            $department->tags_count ? "тегов: {$department->tags_count}" : null,
            $department->roles_count ? "ролей: {$department->roles_count}" : null,
        ]));

        return view('cabinet.settings.departments.show', compact('department', 'users', 'stats', 'deleteBlockers'));
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

        //dd($data);
        // Сохраняем ID текущего менеджера
        $oldManagerId = $department->manager_id;

        // Список пользователей из формы
        $usersToKeep = $data['users'] ?? [];

        // Добавляем менеджера, если его нет в списке
        if (!empty($data['manager_id']) && !in_array($data['manager_id'], $usersToKeep)) {
            $usersToKeep[] = $data['manager_id'];
        }

        // Убираем дубликаты
        $usersToKeep = array_unique($usersToKeep);

        // Обновление менеджера департамента
        $newManagerId = $data['manager_id'] ?? null;

        $department->update([
            'name' => $data['name'],
            'manager_id' => $newManagerId,
            'active' => $data['active'],
            'tg_chat_id' => $data['tg_chat_id'] ?? null,
            'tg_topic_id' => $data['tg_topic_id'] ?? null,
        ]);

        // Переназначаем права, если менеджер изменился
        if ($oldManagerId != $newManagerId) {
            $allPermissionIds = Permission::pluck('id')->toArray();

            if ($oldManagerId && ($data['revoke_old_manager'] ?? true)) {
                $oldManager = User::find($oldManagerId);
                $oldManager?->permissions()->detach($allPermissionIds);
            }

            $newManager = $newManagerId ? User::find($newManagerId) : null;
            $newManager?->permissions()->syncWithoutDetaching($allPermissionIds);
        }

        // Меняем только тех, кто реально добавлен или убран (остальных не трогаем)
        DB::transaction(function () use ($usersToKeep, $department) {
            $currentIds = User::where('department_id', $department->id)->pluck('id')->all();

            $toRemove = array_diff($currentIds, $usersToKeep);
            if ($toRemove) {
                User::whereIn('id', $toRemove)->update(['department_id' => null]);
            }

            $toAdd = array_diff($usersToKeep, $currentIds);
            if ($toAdd) {
                User::whereIn('id', $toAdd)->update(['department_id' => $department->id]);
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
}
