<?php

namespace App\Enums;

enum TicketActionEnum :string
{
    case UPDATE_STATUS = 'update_status';
    case COMMENTED = 'commented';
}
