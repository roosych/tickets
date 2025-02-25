<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketRating extends Model
{
    protected $fillable = [
        'ticket_id', 'user_id', 'rating', 'comment',
    ];
}
