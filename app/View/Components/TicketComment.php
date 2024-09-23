<?php

namespace App\View\Components;

use App\Models\Comment;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TicketComment extends Component
{
    public Comment $comment;

    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    public function render(): View|Closure|string
    {
        return view('components.tickets.ticket-comment');
    }
}
