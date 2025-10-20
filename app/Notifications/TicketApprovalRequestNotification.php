<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketApprovalRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Ticket $ticket;
    protected ApprovalRequest $approvalRequest;

    public function __construct(Ticket $ticket, ApprovalRequest $approvalRequest)
    {
        $this->ticket = $ticket;
        $this->approvalRequest = $approvalRequest;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔔 Запрос на одобрение тикета #' . $this->ticket->id)
            ->greeting('Запрос на одобрение тикета')
            ->line('Пользователь ' . $this->ticket->creator->name . ' просит Вас одобрить тикет #' . $this->ticket->id . '.')
            ->line($this->approvalRequest->comment ? 'Комментарий: ' . $this->approvalRequest->comment : '')
            ->action('Посмотреть', route('approval.show', $this->approvalRequest->uuid))
            ->line('Пожалуйста, примите решение в ближайшее время.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'approval_request_id' => $this->approvalRequest->id,
            'action' => 'approval_requested',
        ];
    }
}
