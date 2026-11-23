@php($isManager = $user->id == $department->manager_id)
<tr data-member-id="{{$user->id}}" data-name="{{$user->name}}" data-open="{{$user->open_tickets_count}}">
    <td>
        <div class="d-flex align-items-center">
        <div class="symbol symbol-circle symbol-40px overflow-hidden me-3 flex-shrink-0">
            @if($user->avatar)
                <div class="symbol-label">
                    <img src="{{$user->avatar}}" alt="{{$user->name}}" class="w-100" />
                </div>
            @else
                <div class="symbol-label fs-3 bg-light-dark text-dark">{{get_initials($user->name)}}</div>
            @endif
        </div>
        <div class="d-flex flex-column">
            <span class="text-gray-800 fw-semibold">
                {{$user->name}}
                @if($isManager)
                    <span class="badge badge-light-primary ms-2"><i class="ki-outline ki-star fs-7 text-primary me-1"></i>менеджер</span>
                @endif
                @unless($user->active)
                    <span class="badge badge-light-danger ms-2">неактивен</span>
                @endunless
                @unless($user->visible)
                    <span class="badge badge-light ms-2">скрыт</span>
                @endunless
            </span>
            <span class="text-muted fs-7">{{$user->position}}</span>
        </div>
        </div>
    </td>
    <td class="text-nowrap">
        @if($user->last_login)
            @php($lastLogin = \Illuminate\Support\Carbon::parse($user->last_login))
            <span class="text-gray-800">{{$lastLogin->format('d.m.Y H:i')}}</span>
            <div class="text-muted fs-8">{{$lastLogin->diffForHumans()}}</div>
        @else
            <span class="text-muted">не входил(а)</span>
        @endif
    </td>
    <td class="text-end">
        @if($user->created_tickets_count)
            <span class="badge badge-light-primary">{{$user->created_tickets_count}}</span>
        @else
            <span class="text-muted">0</span>
        @endif
    </td>
    <td class="text-end">
        @unless($isManager)
            <button type="button" class="btn btn-sm btn-light-danger" data-remove-member>Убрать</button>
        @endunless
    </td>
</tr>
