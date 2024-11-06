<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TicketEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $action,
        public Collection $recipients,
        public ?User $initiator,
        public ?array $additionalData = null
    )
    {

    }
}
