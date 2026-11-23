<?php

namespace App\Policies;

use App\Attributes\PolicyNameAttribute;
use App\Attributes\PolicyPermissionNameAttribute;
use App\Models\Role;
use App\Models\User;

#[PolicyNameAttribute(['az' => 'Vəzifələr', 'en' => 'Roles', 'ru' => 'Роли'])]
class RolePolicy
{
    #[PolicyPermissionNameAttribute(['az' => 'Yaratmaq', 'en' => 'Create', 'ru' => 'Создание'])]
    public function create(User $user): bool
    {
        return \App\Services\DepartmentAccess::isPrivileged($user) || $user->hasPermissions('create', Role::class);
    }

    #[PolicyPermissionNameAttribute(['az' => 'Baxmaq', 'en' => 'Show', 'ru' => 'Просмотр'])]
    public function show(User $user, Role $role): bool
    {
        // если роль относится к департаменту (через менеджера)
        // и есть ли у роли юзера доступ (через трейт HasPermissions)
        return \App\Services\DepartmentAccess::canViewRoles($user)
            && ($user->is_admin || (int) $role->department_id === (int) $user->getDepartmentId());
    }

    #[PolicyPermissionNameAttribute(['az' => 'Silmək', 'en' => 'Delete', 'ru' => 'Удаление'])]
    public function delete(User $user): bool
    {
        // Раньше проверялось несуществующее полномочие 'close' — удаление ролей было доступно только через сервер без проверки
        return \App\Services\DepartmentAccess::isPrivileged($user) || $user->hasPermissions('delete', Role::class);
    }
}
