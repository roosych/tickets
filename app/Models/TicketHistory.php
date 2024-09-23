<?php

namespace App\Models;

use App\Enums\TicketActionEnum;
use App\Enums\TicketStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistory extends Model
{
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $fillable = [
        'user_id', 'ticket_id',
        'action', 'status', 'comment',
    ];

    protected $casts = [
        'action' => TicketActionEnum::class,
        'status' => TicketStatusEnum::class,
    ];
}
