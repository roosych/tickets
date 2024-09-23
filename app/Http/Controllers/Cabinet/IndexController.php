<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke()
    {
        //$users = User::where('manager', auth()->user()->manager)->get();
        $tickets = Ticket::paginate(10);
        return view('cabinet.index', compact('tickets'));
    }
}
