<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected TicketHistory $ticketHistory;

    public function __construct(TicketHistory $ticketHistory)
    {
        $this->ticketHistory = $ticketHistory;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🏷️ Статус тикета #' .$this->ticketHistory->ticket->id. ' изменен')
            ->greeting('Изменение статуса')
            ->line('Пользователь ' . $this->ticketHistory->user->name . ' изменил статус тикета #' . $this->ticketHistory->ticket->id . ' на "' . trans($this->ticketHistory->status->label()) . '"')
            ->action('Посмотреть тикет', url('/cabinet/tickets/' . $this->ticketHistory->ticket->id));
            //->line('"'.$this->ticketHistory->ticket->text.'"')
            //->line('Время действовать! 🚀');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
