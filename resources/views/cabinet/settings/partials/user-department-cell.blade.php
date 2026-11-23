<a href="#" class="d-inline-flex flex-column text-start" data-dept-edit
   data-user-id="{{$user->id}}"
   data-user-name="{{$user->name}}"
   data-dept-id="{{$user->department_id}}"
   data-ad-dept="{{$user->department}}"
   title="Изменить департамент">
    @if($user->departmentRelation)
        <span class="text-gray-800 text-hover-primary">
            {{$user->departmentRelation->name}}
            <i class="ki-outline ki-pencil fs-7 text-muted ms-1"></i>
        </span>
    @else
        <span class="badge badge-light-warning align-self-start">не привязан</span>
        @if($user->department)
            <span class="text-muted fs-7 mt-1">AD: {{$user->department}}</span>
        @endif
    @endif
</a>
