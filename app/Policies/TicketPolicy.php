<?php

namespace App\Policies;

use App\Attributes\PolicyNameAttribute;
use App\Attributes\PolicyPermissionNameAttribute;
use App\Models\Ticket;
use App\Models\User;

#[PolicyNameAttribute(['az' => 'Tiketlər', 'en' => 'Tickets', 'ru' => 'Тикеты'])]
class TicketPolicy
{
//    #[PolicyPermissionNameAttribute(['az' => 'Yaratmaq', 'en' => 'Create', 'ru' => 'Создание'])]
//    public function create(User $user): bool
//    {
//        return $user->hasPermissions('create', Ticket::class);
//    }

//    #[PolicyPermissionNameAttribute(['az' => 'Baxmaq', 'en' => 'Show', 'ru' => 'Просмотр'])]
//    public function show(User $user, Ticket $ticket): bool
//    {
//        return $user->hasPermissions('show', Ticket::class);
//    }

    #[PolicyPermissionNameAttribute(['az' => 'Bağlamaq', 'en' => 'Close', 'ru' => 'Закрытие'])]
    public function close(User $user): bool
    {
        return $user->hasPermissions('close', Ticket::class);
    }

    #[PolicyPermissionNameAttribute(['az' => 'Təyinat', 'en' => 'Assign', 'ru' => 'Назначение'])]
    public function assign(User $user): bool
    {
        return $user->hasPermissions('assign', Ticket::class);
    }
}
