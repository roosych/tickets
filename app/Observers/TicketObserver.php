<?php

namespace App\Observers;

use App\Enums\TicketStatusEnum;
use App\Http\Controllers\Api\IntegrationTicketController;
use App\Jobs\SendIntegrationWebhook;
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

        $this->notifyIntegration($ticket);
    }

    /**
     * Тикет внешней системы изменили здесь (исполнитель, статус, срок) — сообщаем ей вебхуком после коммита.
     * Изменения, пришедшие от самой внешней системы через API, обратно не отправляем.
     */
    private function notifyIntegration(Ticket $ticket): void
    {
        $changed = array_keys(array_intersect_key($ticket->getChanges(), array_flip(['executor_id', 'status', 'due_date'])));

        if ($ticket->external_system === null || $changed === [] || ! config('services.integration.webhook_url')) {
            return;
        }

        if (request()->user()?->tokenCan(IntegrationTicketController::ABILITY)) {
            return;
        }

        SendIntegrationWebhook::dispatch($ticket->id, $changed)->afterCommit();
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
