<?php

namespace App\Policies;

use App\Attributes\PolicyNameAttribute;
use App\Attributes\PolicyPermissionNameAttribute;
use App\Models\Role;
use App\Models\User;

#[PolicyNameAttribute(['az' => 'Hesabatlar', 'en' => 'Reports', 'ru' => 'Отчеты'])]
class ReportPolicy
{
    #[PolicyPermissionNameAttribute(['az' => 'Əməkdaşlar', 'en' => 'Employees', 'ru' => 'Сотрудники'])]
    public function users(User $user): bool
    {
        return $user->hasPermissions('users', 'report');
    }

    #[PolicyPermissionNameAttribute(['az' => 'Departametlər', 'en' => 'Departments', 'ru' => 'Департаменты'])]
    public function depts(User $user): bool
    {
        return $user->hasPermissions('depts', 'report');
    }

    #[PolicyPermissionNameAttribute(['az' => 'Taqlar', 'en' => 'Tags', 'ru' => 'Теги'])]
    public function tags(User $user): bool
    {
        return $user->hasPermissions('tags', 'report');
    }
}
