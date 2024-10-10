<?php

namespace App\Listeners;

use App\Events\TicketEvent;
use App\Jobs\SendTicketNotification;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TicketEventListener implements ShouldQueue
{
    private TelegramNotificationService $telegramService;

    public function __construct(TelegramNotificationService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(TicketEvent $event): void
    {
        // Отправляем уведомление в Telegram
        $message = $this->telegramService->formatTicketMessage(
            $event->action, // 'created', 'updated', 'status_changed', etc.
            $event->ticket,
            $event->initiator
        );
        $this->telegramService->sendMessage($message);


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
