<?php

namespace App\Policies;

use App\Attributes\PolicyNameAttribute;
use App\Attributes\PolicyPermissionNameAttribute;
use App\Models\Ticket;
use App\Models\User;

#[PolicyNameAttribute(['az' => 'Tiketlər', 'en' => 'Tickets', 'ru' => 'Тикеты'])]
class TicketPolicy
{
    #[PolicyPermissionNameAttribute(['az' => 'Bağlamaq', 'en' => 'Close', 'ru' => 'Закрытие'])]
    public function close(User $user, Ticket $ticket): bool
    {
        // Создатель тикета может закрыть
        if ($ticket->creator && $ticket->creator->id === $user->id) {
            return true;
        }

        // Пользователи из департамента создателя с разрешением на закрытие
        if ($ticket->creator && $ticket->creator->getDepartmentId() === $user->getDepartmentId()) {
            return $user->hasPermissions('close', Ticket::class);
        }

        // Пользователи из департамента тикета с разрешением на закрытие
        if ($ticket->department && $ticket->department->id === $user->getDepartmentId()) {
            return $user->hasPermissions('close', Ticket::class);
        }
        return false;
    }

    #[PolicyPermissionNameAttribute(['az' => 'Təyinat', 'en' => 'Assign', 'ru' => 'Назначение'])]
    public function assign(User $user, Ticket $ticket): bool
    {
        return $ticket->department // Проверка, что департамент не null
            && $user->getDepartmentId() === $ticket->department->id
            && $user->hasPermissions('assign', Ticket::class);
    }

    #[PolicyPermissionNameAttribute(['az' => 'Şərh etmək', 'en' => 'Comment', 'ru' => 'Комментирование'])]
    public function comment(User $user, Ticket $ticket): bool
    {
        // Создатель тикета может комментировать
        if ($ticket->creator->id === $user->id) {
            return true;
        }

        // Исполнитель тикета может комментировать
        if ($ticket->performer && $ticket->performer->id === $user->id) {
            return true;
        }

        // Пользователь имеет разрешение на комментирование и департамент пользователя совпадает с департаментом тикета
        return $user->hasPermissions('comment', Ticket::class)
            && $user->getDepartmentId() === $ticket->department->id;
    }
}
