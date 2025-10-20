<?php

namespace App\Models;

use App\Enums\TicketApprovalRequestStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequestHistory extends Model
{
    protected $fillable = [
        'approval_request_id',
        'changed_by',
        'status',
        'comment',
    ];

    protected $casts = [
        'status' => TicketApprovalRequestStatusEnum::class,
    ];

    // Связь с запросом на одобрение
    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    // Кто совершил действие
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
