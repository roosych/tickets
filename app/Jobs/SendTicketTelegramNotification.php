<?php

namespace App\Jobs;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendTicketTelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Ticket $ticket;
    private string $action;
    public ?User $initiator;
    private ?array $additionalData;

    public function __construct(Ticket $ticket, string $action, ?User $initiator, ?array $additionalData = null)
    {
        $this->ticket = $ticket;
        $this->action = $action;
        $this->initiator = $initiator;
        $this->additionalData = $additionalData;
    }

    public function handle(): void
    {
        // Если приватный - не отправляем
        if ($this->ticket->isPrivate()) {
            return;
        }

        Telegram::sendMessage([
            'chat_id' => $this->ticket->department->tg_chat_id,
            'message_thread_id' => $this->ticket->department->tg_topic_id,
            'text' => $this->getMessage(),
            'parse_mode' => 'HTML',
        ]);
    }

    private function getMessage(): ?string
    {
        return match($this->action) {
            'created' => View::make('telegram-messages.tickets.created', ['ticket' => $this->ticket,])->render(),
            'assigned' => View::make('telegram-messages.tickets.assigned', [
                'ticket' => $this->ticket,
                'initiator' => $this->initiator,
            ])->render(),
            'status_updated' => View::make('telegram-messages.tickets.status_changed', [
                'ticket' => $this->ticket,
                'initiator' => $this->initiator,
            ])->render(),
            'commented' => View::make('telegram-messages.tickets.commented', [
                'ticket' => $this->ticket,
                'comment' => Comment::find($this->additionalData['comment_id'])
            ])->render(),

            default => null,
        };
    }
}
