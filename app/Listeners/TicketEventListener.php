<?php

namespace App\Listeners;

use App\Events\TicketEvent;
use App\Jobs\SendTicketNotification;
use App\Jobs\SendTicketTelegramNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketEventListener implements ShouldQueue
{
    public function handle(TicketEvent $event): void
    {
        // Отправка на почту
        foreach ($event->recipients as $recipient) {
            $user = User::find($recipient['id']);
            SendTicketNotification::dispatch($user, $event->ticket, $event->action, $event->additionalData);
        }

        // Отправка в тг группу
        SendTicketTelegramNotification::dispatch($event->ticket, $event->action, $event->initiator, $event->additionalData);
    }
}
