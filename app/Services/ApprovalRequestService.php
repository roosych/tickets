<?php

namespace App\Services;

use App\Enums\TicketApprovalRequestStatusEnum;
use App\Events\TicketEvent;
use App\Jobs\SendTicketNotification;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApprovalRequestService
{
    public function createRequests(Ticket $ticket, array $data): void
    {
        $approvers = User::whereIn('id', $data['approvers'])
            ->where('is_approver', true)
            ->get();

        if ($approvers->isEmpty()) {
            throw new \Exception('Нет доступных пользователей для одобрения.');
        }

        DB::transaction(function () use ($ticket, $approvers, $data) {
            foreach ($approvers as $approver) {
                $approvalRequest = $ticket->approvalRequests()->create([
                    'approver_id'   => $approver->id,
                    'creator_id'    => Auth::id(),
                    'approve_token' => Str::uuid(),
                    'deny_token'    => Str::uuid(),
                    'status'        => TicketApprovalRequestStatusEnum::PENDING,
                    'comment'       => $data['comment'] ?? null,
                ]);

                // Записываем историю одобрения
                $approvalRequest->histories()->create([
                    'changed_by' => Auth::id(),
                    'status'     => TicketApprovalRequestStatusEnum::PENDING,
                    'comment'    => $data['comment'] ?? null,
                ]);

                // Отправляем уведомление каждому согласующему
                SendTicketNotification::dispatch(
                    $approver,
                    $ticket,
                    'approval_requested',
                    ['approval_request' => $approvalRequest] // передаём объект
                );

            }
        });
    }
}
