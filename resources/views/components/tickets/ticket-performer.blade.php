@if($user)
    <div class="symbol-group symbol-hover flex-nowrap">
        <div class="symbol symbol-35px symbol-circle"
             data-bs-toggle="tooltip"
             aria-label="{{$user->name}}"
             data-bs-original-title="{{$user->name}}">
            <img alt="avatar" src="{{$user->avatar}}"
                 @if($ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED) || $ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED))
                     disabled
                 @else
                 @can('assign', $ticket)
                 data-bs-toggle="modal"
                 data-bs-target="#attach_users_modal"
                 data-ticket-id="{{ $ticket->id }}"
                 @endcan
                @endif
                >
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
        @else
        не выбран
        @endcan
    </div>
@endif
