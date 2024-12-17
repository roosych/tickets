<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPrivateTicketAccess
{
    public function handle(Request $request, Closure $next)
    {
        $ticket = $request->route('ticket'); // Получаем тикет из роута

        if ($ticket->isPrivate() && !auth()->user()->isManager()) {
            abort(403, 'Access denied to private ticket');
        }

        return $next($request);
    }
}
