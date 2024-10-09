
    <div class="col-md-4 role_card_item_{{$role->id}}">
        <div class="card card-flush h-md-100">
            <div class="card-header">
                <div class="card-title">
                    <h2>{{$role->name}}</h2>
                </div>
            </div>

            <div class="card-body pt-1">
                <div class="fw-bold text-gray-600 mb-5">
                    Сотрудников: {{count($role->users)}}
                </div>

                <div class="d-flex flex-column text-gray-600">
                    @forelse($role->permissions as $permission)
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>{{$permission->group}} / {{$permission->name}}</div>
                        @if($loop->iteration == 2 && $loop->remaining > 0)
                            <div class="d-flex align-items-center py-2">
                                <span class="bullet bg-primary me-3"></span>
                                <em>и еще {{$loop->remaining}} ...</em>
                            </div>
                            @break
                        @endif
                    @empty
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>
                            <em>нет разрешений</em>
                        </div>
                    @endforelse

                </div>
            </div>

            <div class="card-footer flex-wrap pt-0">
                @can('show', $role)
                    <a href="{{route('cabinet.dept.roles.show', $role->id)}}" class="btn btn-light btn-active-primary my-1 me-2">
                        Подробнее
                    </a>
                @endcan
                @can('delete', \App\Models\Role::class)
                    <a href="javascript:void(0);" class="btn btn-light btn-light-danger btn-active-danger my-1 delete_role" data-name="{{$role->name}}" data-id="{{$role->id}}">
                        Удалить
                    </a>
                @endcan
            </div>
        </div>
    </div>
