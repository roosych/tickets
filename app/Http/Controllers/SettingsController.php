<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\Departments\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

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
        //dd($department->id);
        $data = $request->validated();

        // Сохраняем ID текущего менеджера для проверки изменений
        $oldManagerId = $department->manager_id;

        // Получаем текущих сотрудников департамента (до изменений)
        $currentDepartmentUserIds = User::where('department_id', $department->id)
            ->pluck('id')
            ->toArray();

        // Подготавливаем массив пользователей, которые должны остаться
        $usersToKeep = [];

        // Добавляем выбранных пользователей, если они есть
        if (isset($data['users']) && is_array($data['users'])) {
            $usersToKeep = $data['users'];
        }

        // Если новый менеджер был одним из сотрудников, его могли убрать из массива users[]
        // при смене роли. Убедимся, что он присутствует в списке.
        if (!in_array($data['manager_id'], $usersToKeep)) {
            $usersToKeep[] = $data['manager_id'];
        }

        // Если меняем менеджера на одного из сотрудников, сохраняем остальных сотрудников
        if (in_array($data['manager_id'], $currentDepartmentUserIds)) {
            // Если передан пустой массив users[], считаем, что нужно сохранить всех текущих сотрудников
            if (empty($data['users'])) {
                $usersToKeep = $currentDepartmentUserIds;
            }
        }

        // Обновление менеджера департамента
        $department->update([
            'name' => $data['name'],
            'manager_id' => $data['manager_id'] ?? null,
            'active' => $data['active'],
        ]);

        // Проверяем, изменился ли менеджер
        if ($oldManagerId != $data['manager_id']) {
            // Получаем все доступные разрешения
            $allPermissionIds = Permission::pluck('id')->toArray();

            // Удаляем все разрешения у старого менеджера, если он существует
            if ($oldManagerId !== null) {
                $oldManager = User::find($oldManagerId);
                if ($oldManager) {
                    $oldManager->permissions()->detach($allPermissionIds);
                }
            }

            // Добавляем все разрешения новому менеджеру
            $newManager = User::find($data['manager_id']);
            if ($newManager) {
                $newManager->permissions()->syncWithoutDetaching($allPermissionIds);
            }
        }

        // Обновляем department_id для всех, кто должен остаться
        User::whereIn('id', $usersToKeep)->update(['department_id' => $department->id]);

        // Удаляем привязку у тех, кого нет в списке на сохранение
        User::where('department_id', $department->id)
            ->whereNotIn('id', $usersToKeep)
            ->update(['department_id' => null]);

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
