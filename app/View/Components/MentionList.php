<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MentionList extends Component
{
    public $mentions;

    public function __construct($mentions)
    {
        $this->mentions = $mentions;
    }

    public function render()
    {
        return view('components.mention-list');
    }
}
