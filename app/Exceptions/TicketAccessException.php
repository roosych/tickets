<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TicketAccessException extends Exception
{
    public function __construct($message = "У вас нет прав на доступ к тикету")
    {
        parent::__construct($message);
    }

    public function render($request): JsonResponse
    {
        return response()->json(['error' => $this->getMessage()], 403);
    }
}
