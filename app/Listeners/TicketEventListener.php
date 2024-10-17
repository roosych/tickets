<?php

namespace App\Listeners;

use App\Events\TicketEvent;
use App\Jobs\SendTicketNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TicketEventListener implements ShouldQueue
{
    public function handle(TicketEvent $event): void
    {
        $delay = 0; // Начальная задержка
        foreach ($event->recipients as $user) {
            // Отправка уведомления с увеличивающейся задержкой
            SendTicketNotification::dispatch($user, $event->ticket, $event->action, $event->additionalData)
                ->delay(now()->addSeconds($delay));
            // Увеличиваем задержку на 5 секунд для следующего получателя
            $delay += 5;
        }
    }
}
