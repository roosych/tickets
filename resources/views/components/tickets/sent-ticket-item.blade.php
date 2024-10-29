<div class="card mb-6 mb-xl-9 ticket_item_{{$ticket->id}}">
    <div class="card-body">
        <div class="d-flex flex-stack mb-3">
            <div class="badge badge-light">
                {{Str::limit($ticket->department->name, 40)}}
            </div>
            <div>
                @if($ticket->status->is(\App\Enums\TicketStatusEnum::DONE)
                    && $ticket->creator->id === auth()->id())
                    <button type="button" class="btn btn-sm btn-icon btn-color-light-dark btn-active-light-success close_ticket"
                            data-id="{{$ticket->id}}"
                    >
                        <i class="ki-outline ki-check-square fs-2"></i>
                    </button>
                @endif

                @if($ticket->creator->id === auth()->id())
                    <button type="button" class="btn btn-sm btn-icon btn-color-light-dark btn-active-light-danger cancel-ticket-btn"
                            data-ticket_id="{{$ticket->id}}"
                            data-id="{{$ticket->id}}"
                            data-bs-toggle="modal"
                            data-bs-target="#cancel_ticket_modal">
                    <i class="ki-outline ki-cross-square fs-2"></i>
                    </button>
                @endif

            </div>
        </div>

        <div class="mb-2">
            <a href="{{route('cabinet.tickets.show', $ticket)}}" class="fs-4 fw-bold mb-1 me-2 text-gray-900 text-hover-primary">
                #{{$ticket->id}}
            </a>
            <x-ticket-status-badge :status="$ticket->status->label()" :color="$ticket->status->color()"></x-ticket-status-badge>
        </div>

        <div class="fs-6 fw-semibold text-gray-600 mb-5">
            {{Str::limit($ticket->text, 80)}}
        </div>

        <div class="d-flex flex-stack flex-wrap">
            @if($ticket->performer)
                <div class="symbol-group symbol-hover flex-nowrap">
                    <div class="symbol symbol-35px symbol-circle"
                         data-bs-toggle="tooltip"
                         aria-label="{{$ticket->performer->name}}"
                         data-bs-original-title="{{$ticket->performer->name}}">
                        <img alt="avatar" src="{{$ticket->performer->avatar}}">
                    </div>
                </div>
            @else
                <div>
                    <span class="text-gray-500 border border-gray-300 border-dashed rounded py-1 px-3">
                        {{trans('tickets.sent.performer_empty')}}
                    </span>
                </div>
            @endif

            <div class="d-flex my-1">
                <div class="border border-dashed border-gray-300 d-flex align-items-center rounded py-2 px-3 ms-3">
                    <i class="ki-outline ki-message-text-2 fs-3"></i>
                    <span class="ms-1 fs-7 fw-bold text-gray-600">{{count($ticket->comments)}}</span>
                </div>
            </div>
        </div>
    </div>
</div>
