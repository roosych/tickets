<?php

namespace App\Enums;

enum TicketActionEnum :string
{
    case UPDATE_STATUS = 'update_status';
    case COMMENTED = 'commented';
    case ASSIGN_USER = 'assign_user';
    case CREATE_CHILD = 'create_child';
    case VIEWED = 'viewed';
    case UPDATE_DEADLINE = 'update_deadline';

    public function is(self $action): bool
    {
        return $this === $action;
    }

    public function label(): string
    {
        return trans('tickets.actions.'.$this->value);
    }
}
