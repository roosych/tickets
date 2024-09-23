<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;

class TicketDepartmentAuthorizationException extends AuthorizationException
{
    public function __construct()
    {
        parent::__construct('Тикет не принадлежит вашему департаменту');
    }
}
