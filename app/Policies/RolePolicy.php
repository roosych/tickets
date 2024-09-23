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
        return $user->hasPermissions('create', Role::class);
    }

    #[PolicyPermissionNameAttribute(['az' => 'Baxmaq', 'en' => 'Show', 'ru' => 'Просмотр'])]
    public function show(User $user, Role $role): bool
    {
        // если роль относится к департаменту (через менеджера)
        // и есть ли у роли юзера доступ (через трейт HasPermissions)
        return $role->department_id === $user->head->getDepartment->id
            && $user->hasPermissions('show', Role::class);
    }

    #[PolicyPermissionNameAttribute(['az' => 'Silmək', 'en' => 'Delete', 'ru' => 'Удаление'])]
    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissions('close', Role::class);
    }
}
