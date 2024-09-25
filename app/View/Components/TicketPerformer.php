<?php

namespace App\View\Components;

use App\Models\Ticket;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TicketPerformer extends Component
{
    public ?User $user;
    public Ticket $ticket;

    public function __construct(?User $user, Ticket $ticket)
    {
        $this->user = $user;
        $this->ticket = $ticket;
    }

    public function render(): View|Closure|string
    {
        return view('components.tickets.ticket-performer');
    }
}
