<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;

class TicketAuthorizationService
{
    public function canAccess(Ticket $ticket, $user)
    {
        return $this->hasPermission($user, 'show', $ticket);
//        return $this->hasPermission($user, 'show', $ticket)
//            && $this->isInSameDepartmentOrCreator($ticket, $user);
        return $this->isInSameDepartmentOrCreator($ticket, $user);
    }

    public function canComment(Ticket $ticket, $user): bool
    {
        return $this->isTicketCreatorOrPerformer($ticket, $user);
    }

    public function canClose(Ticket $ticket, $user): bool
    {
        return $this->hasPermission($user, 'close', $ticket)
            && $this->isInSameDepartmentOrCreator($ticket, $user);
    }

    public function canAttachUser(Ticket $ticket, $user): bool
    {
        return $this->hasPermission($user, 'show', $ticket)
            && $this->isInSameDepartment($ticket, $user);
    }


    // Общие вспомогательные методы для сокращения дублирования
    protected function hasPermission($user, string $permission, Ticket $ticket): bool
    {
        return $user->hasPermissions($permission, Ticket::class);
    }



}
