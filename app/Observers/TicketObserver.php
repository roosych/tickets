<?php

namespace App\Observers;

use App\Enums\TicketStatusEnum;
use App\Models\Ticket;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // Проверяем, изменился ли статус на "completed"
        if ($ticket->isDirty('status') && $ticket->status === TicketStatusEnum::COMPLETED) {
            $ticket->completed_at = now();
        }

        // Проверяем, изменился ли статус на "in progress"
        if ($ticket->isDirty('status') && $ticket->status === TicketStatusEnum::IN_PROGRESS) {
            $ticket->in_progress_at = now();
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
