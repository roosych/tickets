<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\TicketHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
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
            ->subject('💬 Новый комментарий')
            ->greeting('Комментарий')
            ->line('Пользователь ' . $this->comment->creator->name . ' прокомментировал тикет #' . $this->comment->ticket->id)
            ->line('"'.$this->comment->text.'"')
            ->action('Посмотреть тикет', url('/cabinet/tickets/' . $this->comment->ticket->id));
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
