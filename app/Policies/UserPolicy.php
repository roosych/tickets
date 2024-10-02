<?php

namespace App\Policies;

use App\Attributes\PolicyNameAttribute;
use App\Attributes\PolicyPermissionNameAttribute;
use App\Models\User;

#[PolicyNameAttribute(['az' => 'İstifadəçilər', 'en' => 'Users', 'ru' => 'Пользователи'])]
class UserPolicy
{
    #[PolicyPermissionNameAttribute(['az' => 'İcazələr', 'en' => 'Accesses', 'ru' => 'Доступы'])]
    public function accesses(User $authUser, User $user): bool
    {
        return $authUser->hasPermissions('accesses', User::class);
    }
}
