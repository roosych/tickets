<?php

namespace App\View\Components;

use App\Models\Ticket;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SentTicketItem extends Component
{
    public Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function render(): View|Closure|string
    {
        return view('components.tickets.sent-ticket-item');
    }
}
