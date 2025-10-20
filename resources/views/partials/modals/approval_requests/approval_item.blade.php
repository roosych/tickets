<div class="d-flex align-items-center justify-content-between mb-5 approval-request" id="approval-request-{{ $request->id }}">
    <div class="d-flex align-items-center">
        <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
            @if($request->approver->avatar)
                <div class="symbol-label cursor-default">
                    <img src="{{ $request->approver->avatar }}" alt="{{ $request->approver->name }}" class="w-100" />
                </div>
            @else
                <div class="symbol-label fs-3 bg-light-dark text-gray-800">
                    {{ get_initials($request->approver->name) }}
                </div>
            @endif
        </div>

        <div class="fw-semibold">
            <p class="fw-bold text-gray-900 mb-0">{{ $request->approver->name }}</p>
            <div class="text-gray-500">{{ $request->approver->email }}</div>
        </div>
    </div>

    <div>
        @if(auth()->id() === $request->approver->id && $request->status->is(\App\Enums\TicketApprovalRequestStatusEnum::PENDING))
            <a href="javascript:void(0);"
               class="btn btn-sm btn-success me-2 approve"
               data-id="{{ $request->id }}"
               data-token="{{ $request->approve_token }}">
                Одобрить
            </a>
            <a href="javascript:void(0);"
               class="btn btn-sm btn-danger deny"
               data-id="{{ $request->id }}"
               data-token="{{ $request->deny_token }}">
                Отклонить
            </a>

            <div id="status-{{ $request->id }}" class="mt-2"></div>

        @else
            <span class="badge badge-light-{{ $request->status->color() }} fw-bold fs-7">
                {{ $request->status->label() }}
            </span>
        @endif
    </div>
</div>

