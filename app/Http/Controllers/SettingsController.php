<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\Departments\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function users()
    {
        $users = User::all();
        return view('cabinet.settings.users', compact('users'));
    }

    public function departments()
    {
        $departments = Department::all();
        return view('cabinet.settings.departments.index', compact('departments'));
    }

    // Страница конкретного департамента
    public function show(Department $department)
    {
        $managerId = $department->manager?->id; // null если менеджера нет

        $users = User::query()
            ->where(function ($query) use ($managerId) {
                $query->whereNull('department_id');
                if ($managerId !== null) {
                    $query->orWhere('department_id', '!=', $managerId);
                }
            })
            ->where('visible', true)
            ->where('active', true)
            ->get();

        return view('cabinet.settings.departments.show', compact('department', 'users'));
    }


    public function store(UpdateDepartmentRequest $request, Department $department)
    {
        $data = $request->validated();

        // Сохраняем ID текущего менеджера
        $oldManagerId = $department->manager_id;

        // Получаем текущих сотрудников департамента (до изменений)
        $currentDepartmentUserIds = User::where('department_id', $department->id)
            ->pluck('id')
            ->toArray();

        // Подготавливаем массив пользователей, которые должны остаться
        $usersToKeep = $currentDepartmentUserIds;

        // Добавляем выбранных пользователей, если они есть
        if (isset($data['users']) && is_array($data['users'])) {
            $usersToKeep = array_merge($usersToKeep, $data['users']);
        }

        // Убираем дубликаты
        $usersToKeep = array_unique($usersToKeep);

        // Если менеджер есть и его нет в массиве — добавляем
        if (!empty($data['manager_id']) && !in_array($data['manager_id'], $usersToKeep)) {
            $usersToKeep[] = $data['manager_id'];
        }

        // Обновление менеджера департамента
        $department->update([
            'name' => $data['name'],
            'manager_id' => $data['manager_id'] ?? null,
            'active' => $data['active'],
        ]);

        // Если менеджер изменился, переназначаем права
        if ($oldManagerId != $data['manager_id']) {
            $allPermissionIds = Permission::pluck('id')->toArray();

            if ($oldManagerId) {
                $oldManager = User::find($oldManagerId);
                if ($oldManager) {
                    $oldManager->permissions()->detach($allPermissionIds);
                }
            }

            $newManager = User::find($data['manager_id']);
            if ($newManager) {
                $newManager->permissions()->syncWithoutDetaching($allPermissionIds);
            }
        }

        // ✅ Используем транзакцию для безопасности
        DB::transaction(function () use ($usersToKeep, $department) {
            // Сначала убираем всех пользователей, которых нет в списке на сохранение
            User::where('department_id', $department->id)
                ->whereNotIn('id', $usersToKeep)
                ->update(['department_id' => null]);

            // Привязываем выбранных пользователей
            User::whereIn('id', $usersToKeep)
                ->update(['department_id' => $department->id]);
        });

        return redirect()->route('cabinet.settings.departments.show', $department)->with('success', 'Департамент успешно обновлен');
    }


    public function toggleUserSetting(Request $request, $userId, $setting)
    {
        $user = User::findOrFail($userId);

        if (!in_array($setting, ['visible', 'active', 'email_notify', 'tg_notify'])) {
            return response()->json(['error' => 'Неправильная настройка'], 400);
        }

        $user->$setting = !$user->$setting;
        $user->save();

        return response()->json(['success' => true, 'value' => $user->$setting]);
    }
}
