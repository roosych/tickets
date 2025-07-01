<?php

namespace App\Notifications;

use App\Enums\TicketStatusEnum;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReminderNotification extends Notification
{
    use Queueable;

    protected Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
        // Получаем последнюю запись со статусом "done"
        $this->ticketHistory = $ticket->histories()
            ->where('status', TicketStatusEnum::DONE)
            ->latest('created_at')
            ->first();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('🔔 Ожидается ваша проверка по тикету #' . $this->ticket->id)
            ->greeting('Тикет #' . $this->ticket->id . ' ожидает закрытия');

        if ($this->ticketHistory && $this->ticketHistory->comment) {
            $mail->line('💬 ' . $this->ticket->performer->name . ':')
                ->line('"' . $this->ticketHistory->comment . '"');
        }

        return $mail
            ->action('Посмотреть тикет', url('/cabinet/tickets/' . $this->ticket->id))
            ->line('Пожалуйста, проверьте результат и закройте тикет, если всё в порядке. ✅');
    }

}
