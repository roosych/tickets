<?php

namespace App\Models;

use App\Enums\TicketApprovalRequestStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'uuid',
        'ticket_id',
        'creator_id',
        'approver_id',
        'status',
        'comment',
        'approve_token',
        'deny_token',
    ];

    protected $casts = [
        'status' => TicketApprovalRequestStatusEnum::class,
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // Создатель запроса
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ApprovalRequestHistory::class);
    }

}
