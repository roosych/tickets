<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Support\Facades\View;

class TelegramMessageService
{
    public function getTicketCreatedMessage(Ticket $ticket): string
    {
        return View::make('telegram-messages.tickets.created', ['ticket' => $ticket])->render();
    }

    public function getTicketAssignedMessage(Ticket $ticket): string
    {
        return View::make('telegram-messages.tickets.assigned', ['ticket' => $ticket])->render();
    }

    public function getTicketStatusChangedMessage(Ticket $ticket): string
    {
        return View::make('telegram-messages.tickets.status_changed', ['ticket' => $ticket])->render();
    }

    public function getTicketCommentedMessage(Ticket $ticket, Comment $comment): string
    {
        return View::make('telegram-messages.tickets.commented', ['ticket' => $ticket, 'comment' => $comment])->render();
    }
}
