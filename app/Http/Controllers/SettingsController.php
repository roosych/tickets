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
        $managerId = $department->manager?->id; // null если менеджера нет

        $users = User::query()
//            ->where(function ($query) use ($managerId) {
//                $query->whereNull('department_id');
//                if ($managerId !== null) {
//                    $query->orWhere('department_id', '!=', $managerId);
//                }
//            })
            ->where('visible', true)
            ->where('active', true)
            ->get();

        return view('cabinet.settings.departments.show', compact('department', 'users'));
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
        $department->update([
            'name' => $data['name'],
            'manager_id' => $data['manager_id'] ?? null,
            'active' => $data['active'],
        ]);

        // Переназначаем права, если менеджер изменился
        if ($oldManagerId != $data['manager_id']) {
            $allPermissionIds = Permission::pluck('id')->toArray();

            if ($oldManagerId) {
                $oldManager = User::find($oldManagerId);
                $oldManager?->permissions()->detach($allPermissionIds);
            }

            $newManager = User::find($data['manager_id']);
            $newManager?->permissions()->syncWithoutDetaching($allPermissionIds);
        }

        // Обновляем департамент для пользователей
        DB::transaction(function () use ($usersToKeep, $department) {
            // Отключаем всех пользователей из департамента
            User::where('department_id', $department->id)
                ->update(['department_id' => null]);

            // Обновляем каждого пользователя по очереди
            foreach ($usersToKeep as $userId) {
                User::where('id', $userId)->update(['department_id' => $department->id]);
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
