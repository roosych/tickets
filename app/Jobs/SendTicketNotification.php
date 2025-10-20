<?php

namespace App\Jobs;

use App\Models\ApprovalRequest;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Notifications\TicketApprovalRequestNotification;
use App\Notifications\TicketCommentNotification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Notifications\UserAssignedToTicketNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendTicketNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;
    private Ticket $ticket;
    private string $action;
    private ?array $additionalData;

    public function __construct(User $user, Ticket $ticket, string $action, ?array $additionalData = null)
    {
        $this->user = $user;
        $this->ticket = $ticket;
        $this->action = $action;
        $this->additionalData = $additionalData;
    }

    public function handle(): void
    {
        $notification = $this->getNotification();

        if ($notification) {
            $this->user->notify($notification);
        }
    }

    private function getNotification()
    {
        return match($this->action) {
            'created' => new TicketCreatedNotification($this->ticket),

            'commented' => new TicketCommentNotification($this->ticket,
                Comment::find($this->additionalData['comment_id'])),

            'status_updated' => new TicketStatusUpdatedNotification(
                TicketHistory::find($this->additionalData['ticket_history_id'])),

            'assigned' => new UserAssignedToTicketNotification($this->ticket),

            // Новый action для approval
            'approval_requested' => isset($this->additionalData['approval_request'])
                ? new TicketApprovalRequestNotification(
                    $this->ticket,
                    $this->additionalData['approval_request']
                )
                : null,
//            'approval_updated' => new \App\Notifications\TicketApprovalUpdatedNotification(
//                $this->ticket,
//                $this->additionalData['approval_request_id']
//            ),

            default => null,
        };
    }
}
