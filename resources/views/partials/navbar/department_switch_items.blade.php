@foreach($myDepartments as $dept)
    <div class="menu-item px-3">
        <form method="POST" action="{{route('cabinet.current_department.switch')}}">
            @csrf
            <input type="hidden" name="department_id" value="{{$dept->id}}">
            <button type="submit" class="menu-link w-100 border-0 bg-transparent px-3 py-2 {{$dept->id == $currentDeptId ? 'active' : ''}}">
                <span class="menu-title d-flex flex-column align-items-start text-start">
                    <span>{{$dept->name}}</span>
                    <span class="fs-8 fw-normal text-muted">
                        @if($dept->manager_id == auth()->id())
                            {{trans('common.department_switch.manager')}}
                        @endif
                        @unless($dept->active)
                            {{ $dept->manager_id == auth()->id() ? '·' : '' }} {{trans('common.department_switch.inactive')}}
                        @endunless
                    </span>
                </span>
                @if($dept->id == $currentDeptId)
                    <i class="ki-outline ki-check fs-3 text-primary ms-2"></i>
                @endif
            </button>
        </form>
    </div>
@endforeach
