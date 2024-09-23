<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔔 Новый тикет #' .$this->ticket->id)
            ->greeting('Новый тикет')
            ->line('Пользователь '.$this->ticket->creator->name.' создал новый тикет #' .$this->ticket->id.'.')
            ->action('Посмотреть тикет', url('/cabinet/tickets/' . $this->ticket->id))
            ->line('"'.$this->ticket->text.'"')
            ->line('Время действовать! 🚀');
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
