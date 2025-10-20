<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\TicketApprovalRequestStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\Approval\StoreRequest;
use App\Models\ApprovalRequest;
use App\Models\Ticket;
use App\Services\ApprovalRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApproveRequestController extends Controller
{
    /**
     * @throws \Exception
     */
    public function store(StoreRequest  $request, Ticket $ticket, ApprovalRequestService $service)
    {
        $this->authorize('view', $ticket);

        $validated = $request->validated();

        $service->createRequests($ticket, [
            'approvers' => $validated['approvers'],
            'comment' => $validated['approval_request_comment'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    public function show(string $uuid)
    {
        $approvalRequest = ApprovalRequest::where('uuid', $uuid)
            ->with(['ticket.creator', 'approver'])
            ->firstOrFail();

        return view('approval.show', [
            'approvalRequest' => $approvalRequest,
            'ticket' => $approvalRequest->ticket,
        ]);
    }

    public function approveAjax(Request $request, $token)
    {
        return $this->handleAjax($token, 'approve_token', TicketApprovalRequestStatusEnum::APPROVED, true);
    }

    public function denyAjax(Request $request, $token)
    {
        return $this->handleAjax($token, 'deny_token', TicketApprovalRequestStatusEnum::DENIED, true);
    }

    protected function handleAjax(string $token, string $column, TicketApprovalRequestStatusEnum $action, bool $checkPolicy = false)
    {
        $approval = ApprovalRequest::where($column, $token)
            ->where('status', TicketApprovalRequestStatusEnum::PENDING)
            ->first();

        if (!$approval) {
            return response('The link is invalid or already used.', 403);
        }

        $ticket = $approval->ticket;

        // Проверка политики, только если действие идёт из интерфейса (авторизованный пользователь)
        if ($checkPolicy && auth()->check()) {
            $user = auth()->user();

            if ($user->cannot('canApprovalRequest', $ticket)) {
                return response('You do not have permission to perform this action.', 403);
            }

            // Доп. проверка — чтобы этот пользователь действительно был назначен как approver
            if ($approval->approver_id !== $user->id) {
                return response('You are not the approver for this ticket.', 403);
            }
        }

        DB::transaction(function () use ($approval, $action, $column) {
            // Обновляем статус заявки
            $approval->update(['status' => $action]);

            // Создаём запись в истории
            $approval->histories()->create([
                'status' => $action,
                'changed_by' => auth()->id(), // если действие из интерфейса
                //'comment' => '';
            ]);

            // Инвалидируем противоположную ссылку
            $otherColumn = $column === 'approve_token' ? 'deny_token' : 'approve_token';
            if ($approval->$otherColumn) {
                ApprovalRequest::where($otherColumn, $approval->$otherColumn)
                    ->where('status', TicketApprovalRequestStatusEnum::PENDING)
                    ->update(['status' => TicketApprovalRequestStatusEnum::EXPIRED]);
            }
        });

        // Генерируем HTML бейдж
        $statusEnum = $approval->status instanceof TicketApprovalRequestStatusEnum
            ? $approval->status
            : TicketApprovalRequestStatusEnum::from($approval->status);

        $badgeHtml = sprintf(
            '<span class="badge badge-light-%s fw-bold fs-7">%s</span>',
            $statusEnum->color(),
            $statusEnum->label()
        );

        return response()->json([
            'badgeHtml' => $badgeHtml,
            'ticket_id' => $approval->ticket_id,
        ]);
    }

    public function approve(ApprovalRequest $approvalRequest, string $token)
    {
        if ($approvalRequest->approve_token !== $token) {
            abort(403, 'Token error');
        }

        DB::transaction(function () use ($approvalRequest) {
            // Обновляем статус
            $approvalRequest->update([
                'status' => TicketApprovalRequestStatusEnum::APPROVED,
            ]);

            // Записываем в историю
            $approvalRequest->histories()->create([
                'status' => TicketApprovalRequestStatusEnum::APPROVED,
                'changed_by' => $approvalRequest->approver->id,
                //'comment' => '',
            ]);
        });

        return redirect()->route('approval.show', $approvalRequest->uuid);
    }

    public function deny(ApprovalRequest $approvalRequest, string $token)
    {
        if ($approvalRequest->deny_token !== $token) {
            abort(403, 'Token error');
        }

        DB::transaction(function () use ($approvalRequest) {
            // Обновляем статус
            $approvalRequest->update([
                'status' => TicketApprovalRequestStatusEnum::DENIED,
            ]);

            // Записываем в историю
            $approvalRequest->histories()->create([
                'status' => TicketApprovalRequestStatusEnum::DENIED,
                'changed_by' => $approvalRequest->approver->id,
                //'comment' => '',
            ]);
        });

        return redirect()->route('approval.show', $approvalRequest->uuid);
    }

}
