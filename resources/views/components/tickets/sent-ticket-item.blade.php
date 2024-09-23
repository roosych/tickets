<div class="card mb-6 mb-xl-9 ticket_item_{{$ticket->id}}">
    <div class="card-body">
        <div class="d-flex flex-stack mb-3">
            <div class="badge badge-light">
                {{$ticket->department->name}}
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-icon btn-color-light-dark btn-active-light-primary close_ticket"
                data-id="{{$ticket->id}}"
                >
                    <i class="ki-outline ki-lock-2 fs-2"></i>
                </button>
            </div>
        </div>

        <div class="mb-2">
            <a href="{{route('cabinet.tickets.show', $ticket)}}" class="fs-4 fw-bold mb-1 text-gray-900 text-hover-primary">
                #{{$ticket->id}}
            </a>
        </div>

        <div class="fs-6 fw-semibold text-gray-600 mb-5">
            {{$ticket->text}}
        </div>

        <div class="d-flex flex-stack flex-wrap">
            @if($ticket->performers->count() > 0)
                <div class="symbol-group symbol-hover flex-nowrap">
                    @foreach($ticket->performers as $key => $user)
                        @if($key < 5)
                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="{{$user->name}}" data-bs-original-title="{{$user->name}}">
                                <img alt="avatar" src="{{$user->avatar}}">
                            </div>
                        @endif
                        @if($loop->last && $loop->count > 5)
                            <a href="javascript:void(0);" class="symbol symbol-35px symbol-circle">
                                <span class="symbol-label bg-light text-gray-400 fs-8 fw-bold">+{{$loop->count - 5}}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @else
                <div>
                    <span class="text-gray-500 border border-gray-300 border-dashed rounded py-1 px-3">
                        Нет исполнителей
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