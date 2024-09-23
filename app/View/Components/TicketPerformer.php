<?php

namespace App\View\Components;

use App\Models\Ticket;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class TicketPerformer extends Component
{
    public Collection $users;
    public Ticket $ticket;

    public function __construct(Collection $users, Ticket $ticket)
    {
        $this->users = $users;
        $this->ticket = $ticket;
    }

    public function render(): View|Closure|string
    {
        return view('components.tickets.ticket-performer');
    }
}
