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
        // Проверяем, что список получателей не пуст
        if (!empty($event->recipients)) {
            foreach ($event->recipients as $recipientId) {
                $user = User::find($recipientId);
                if ($user) {
                    SendTicketNotification::dispatch($user, $event->ticket, $event->action, $event->additionalData);
                }
            }
        }

        // Отправка в тг группу
        SendTicketTelegramNotification::dispatch($event->ticket, $event->action, $event->initiator, $event->additionalData);
    }
}
