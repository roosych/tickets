<?php

namespace App\View\Components;

use App\Models\Ticket;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TicketStatusBadge extends Component
{
    public string $status;
    public string $color;

    public function __construct(string $status, string $color)
    {
        $this->status = $status;
        $this->color = $color;
    }

    public function render(): View|Closure|string
    {
        return view('components.tickets.ticket-status-badge');
    }
}
