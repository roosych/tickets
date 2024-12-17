@if($user)
    <div class="d-flex align-items-center">
        <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
            @if($ticket->performer->avatar)
                <div class="symbol-label cursor-default">
                    <img src="{{$ticket->performer->avatar}}" alt="{{$ticket->performer->name}}" class="w-100" />
                </div>
            @else
                <div class="symbol-label fs-3 bg-light-dark text-gray-800">
                    {{get_initials($ticket->performer->name)}}
                </div>
            @endif
        </div>

        <div class="pe-5">
            <div class="d-flex align-items-center flex-wrap gap-1">
                <p class="fw-bold text-gray-900 mb-0">
                    {{$ticket->performer->name}}
                </p>
            </div>
            <div>
                <span class="text-muted fw-semibold">
                    {{$ticket->performer->email}}
                </span>
            </div>
        </div>
    </div>
@else
    <div id="select_users_{{$ticket->id}}">
        @can('assign', $ticket)
            <a href="javascript:void(0);" class="symbol symbol-35px symbol-circle border border-gray-300 border-dashed ms-0"
               @if($ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED) || $ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED))
                   disabled
               @else
                   data-bs-toggle="modal"
               data-bs-target="#attach_users_modal"
               data-ticket-id="{{ $ticket->id }}"
                @endif>
            <span class="symbol-label bg-light text-gray-400 fs-8 fw-bold" style="width: 34px;height: 34px">
                +
            </span>
            </a>
            <span class="text-muted fw-semibold ms-2">
                {{trans('tickets.sent.performer_empty')}}
            </span>
        @else
            не выбран
        @endcan
    </div>
@endif
