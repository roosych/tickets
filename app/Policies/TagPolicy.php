<?php

namespace App\Policies;

use App\Attributes\PolicyNameAttribute;
use App\Attributes\PolicyPermissionNameAttribute;
use App\Models\Tag;
use App\Models\User;

#[PolicyNameAttribute(['az' => 'Teqlər', 'en' => 'Tags', 'ru' => 'Теги'])]
class TagPolicy
{
    #[PolicyPermissionNameAttribute(['az' => 'Yaratmaq', 'en' => 'Create', 'ru' => 'Создание'])]
    public function create(User $user): bool
    {
        return $user->hasPermissions('create', Tag::class);
    }

    #[PolicyPermissionNameAttribute(['az' => 'Silmək', 'en' => 'Delete', 'ru' => 'Удаление'])]
    public function delete(User $user): bool
    {
        return $user->hasPermissions('delete', Tag::class);
    }
}
