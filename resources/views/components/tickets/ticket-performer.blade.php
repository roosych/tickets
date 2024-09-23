@if($users->count() > 0)
    <div class="symbol-group symbol-hover flex-nowrap">
        @foreach($users as $key => $user)
            @if($key < 5)
                <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="{{$user->name}}" data-bs-original-title="{{$user->name}}">
                    <img alt="avatar" src="{{$user->avatar}}"
                         data-bs-toggle="modal"
                         data-bs-target="#attach_users_modal"
                         data-ticket-id="{{ $ticket->id }}"
                    >
                </div>
            @endif
            @if($loop->last && $loop->count > 5)
                <a href="#" class="symbol symbol-35px symbol-circle"
                   data-bs-toggle="modal"
                   data-bs-target="#attach_users_modal"
                   data-ticket-id="{{ $ticket->id }}"
                >
                    <span class="symbol-label bg-light text-gray-400 fs-8 fw-bold">+{{$loop->count - 5}}</span>
                </a>
            @endif
        @endforeach
    </div>
    @else
    <div id="select_users_{{$ticket->id}}">
        <a href="javascript:void(0);" class="text-gray-500 border border-gray-300 border-dashed rounded py-1 px-3"
           data-bs-toggle="modal"
           data-bs-target="#attach_users_modal"
           data-ticket-id="{{ $ticket->id }}">
            Выбрать
        </a>
    </div>
@endif
