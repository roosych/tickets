@extends('layouts.app')

@section('title', 'Департаменты')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">
                Главная
            </a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            Департаменты
        </li>
    </ul>
@endsection

@section('content')
    <div class="row g-5 mb-6">
        @foreach([
            ['Департаментов', $stats['total'], 'gray-800', 'all'],
            ['Исполнители тикетов', $stats['active'], 'success', 'active'],
            ['Не исполняют тикеты', $stats['inactive'], 'gray-800', 'inactive'],
            ['Без менеджера', $stats['no_manager'], $stats['no_manager'] ? 'warning' : 'gray-800', 'no_manager'],
            ['Сотрудников', $stats['users'], 'primary', 'all'],
        ] as [$label, $count, $color, $key])
            <div class="col-6 col-xl">
                <a href="#" class="card card-flush h-100 hover-elevate-up" data-dept-filter="{{$key}}">
                    <div class="card-body py-5">
                        <div class="fs-2 fw-bold text-{{$color}} lh-1 mb-2">{{$count}}</div>
                        <div class="text-gray-600 fw-semibold fs-7">{{$label}}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid">
            <div class="card pt-4 mb-6 mb-xl-9">

                <div class="card-header border-0">
                    <div class="card-title">
                        <h3 class="fw-bold mb-0">
                            Департаменты
                        </h3>
                    </div>
                    <div class="card-toolbar gap-3">
                        <div class="d-flex align-items-center position-relative">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                            <input type="text" id="deptSearch" class="form-control form-control-solid w-250px ps-12"
                                   placeholder="Поиск по департаменту или менеджеру" />
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_create_department">
                            <i class="ki-outline ki-plus fs-2"></i>Создать департамент
                        </button>
                    </div>
                </div>

                <div id="departments_accordion" class="card-body pt-0">
                    @foreach($departments as $department)
                        <div class="py-0 dept-item"
                             data-active="{{(int) $department->active}}"
                             data-no-manager="{{(int) !$department->manager_id}}"
                             data-search="{{mb_strtolower($department->name.' '.$department->manager?->name)}}">
                            <div class="py-3 d-flex flex-stack flex-wrap">
                                <div class="d-flex align-items-center collapsible collapsed rotate"
                                     data-bs-toggle="collapse"
                                     href="#departments_accordion_{{$department->id}}"
                                     role="button"
                                     aria-expanded="false"
                                     aria-controls="departments_accordion_{{$department->id}}">
                                    <div class="me-3 rotate-90">
                                        <i class="ki-outline ki-right fs-3"></i>
                                    </div>
                                    <div class="me-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-semibold ms-5">
                                                <a href="javascript:void(0);" class="fs-5 fw-bold text-gray-900">
                                                   @if($department->active)
                                                       <span>
                                                           <i class="ki-duotone ki-star text-warning fs-4 me-1"></i>
                                                       </span>
                                                   @endif
                                                   {{$department->name}}
                                                   <span class="text-muted fw-semibold">({{ $department->users->count() }})</span>
                                                </a>
                                                @if($department->open_tickets_count)
                                                    <span class="badge badge-light-info ms-2" title="Открытых тикетов">
                                                        {{ $department->open_tickets_count }} откр. тикетов
                                                    </span>
                                                @endif
                                                <span class="badge badge-light ms-2" title="Тегов">
                                                    {{ $department->tags_count }} тегов
                                                </span>
                                                <div class="fs-6 text-muted">
                                                    @if($department->manager)
                                                        <a href="{{route('cabinet.users.show', $department->manager)}}">
                                                            {{$department->manager->name}}
                                                        </a>
                                                    @else
                                                        <span class="badge badge-light-warning">менеджер не указан</span>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex my-3 ms-9">
                                    <a href="{{route('cabinet.settings.departments.show', $department)}}" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3">
                                        <span data-bs-toggle="tooltip" data-bs-trigger="hover" title="Редактировать">
                                            <i class="ki-outline ki-setting-2 fs-3"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div id="departments_accordion_{{$department->id}}" class="collapse fs-6 ps-10"
                                 data-bs-parent="#departments_accordion"
                            >
                                @if($department->users->isNotEmpty())
                                    <table class="table align-middle table-row-dashed fs-6 gy-5" >
                                        <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-125px">{{trans('users.name')}}</th>
                                            <th class="min-w-125px">{{trans('users.position')}}</th>
                                            <th class="min-w-125px">{{trans('users.last_login')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                        @foreach($department->users as $user)
                                            <tr>
                                                <td class="d-flex align-items-center border-bottom-0">
                                                    <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                                        <a href="{{route('cabinet.users.show', $user)}}" target="_blank">
                                                            @if($user->avatar)
                                                                <div class="symbol-label">
                                                                    <img src="{{$user->avatar}}" alt="{{$user->name}}" class="w-100" />
                                                                </div>
                                                            @else
                                                                <div class="symbol-label fs-3 bg-light-dark text-dark">
                                                                    {{get_initials($user->name)}}
                                                                </div>
                                                            @endif
                                                        </a>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <a href="{{route('cabinet.users.show', $user)}}"
                                                           class="text-gray-800 text-hover-primary mb-1"
                                                           target="_blank"
                                                        >
                                                            {{$user->name}}
                                                        </a>
                                                        <span>{{$user->email}}</span>
                                                    </div>
                                                </td>
                                                <td>{{$user->position}}</td>
                                                <td>
                                                    @if($user->last_login)
                                                        {{\Carbon\Carbon::parse($user->last_login)->isoFormat('D MMMM YYYY, HH:mm')}}
                                                    @else
                                                        {{trans('users.not_logined')}}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="border border-dashed rounded p-3 mb-5">
                                        <span class="text-muted mb-2">
                                            Никого нет :(
                                        </span>
                                    </div>
                                @endif

                            </div>
                        </div>

                        @if (!$loop->last)
                            <div class="separator separator-dashed"></div>
                        @endif

                    @endforeach
                    <div id="deptEmpty" class="text-muted text-center py-5 d-none">Ничего не найдено</div>
                </div>


            </div>
        </div>
    </div>
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('modals')
    <div class="modal fade" id="kt_modal_create_department" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <form method="POST" action="{{route('cabinet.settings.departments.create')}}">
                    @csrf
                    <div class="modal-header">
                        <h3 class="fw-bold mb-0">Новый департамент</h3>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Название</label>
                            <input type="text" name="name" value="{{old('name')}}"
                                   class="form-control @error('name', 'createDepartment') is-invalid @enderror"
                                   placeholder="Например, IT-отдел" maxlength="255" required />
                            @error('name', 'createDepartment')
                                <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>
                        <div class="d-flex flex-stack">
                            <div class="me-5">
                                <div class="fw-semibold fs-6">Исполняет тикеты</div>
                                <div class="fs-7 text-muted">Отдел будет отмечен звёздочкой и доступен для назначения тикетов</div>
                            </div>
                            <input type="hidden" name="active" value="0">
                            <div class="form-check form-check-solid form-check-custom form-switch">
                                <input class="form-check-input w-45px h-30px" type="checkbox" name="active" value="1"
                                       {{old('active') ? 'checked' : ''}}>
                            </div>
                        </div>
                        <div class="fs-7 text-muted mt-6">Менеджера и сотрудников можно назначить после создания.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('custom_js')
    <script>
        $(function () {
            @if($errors->createDepartment->any())
                new bootstrap.Modal(document.getElementById('kt_modal_create_department')).show();
            @endif

            let mode = 'all';

            function apply() {
                const q = ($('#deptSearch').val() || '').toLowerCase().trim();
                let visible = 0;
                $('.dept-item').each(function () {
                    const $el = $(this);
                    let ok = !q || String($el.attr('data-search')).includes(q);
                    if (mode === 'active') ok = ok && $el.attr('data-active') === '1';
                    if (mode === 'inactive') ok = ok && $el.attr('data-active') === '0';
                    if (mode === 'no_manager') ok = ok && $el.attr('data-no-manager') === '1';
                    $el.toggle(ok);
                    $el.next('.separator').toggle(ok);
                    if (ok) visible++;
                });
                $('#deptEmpty').toggleClass('d-none', visible > 0);
            }

            $('#deptSearch').on('keyup', apply);
            $('[data-dept-filter]').on('click', function (e) {
                e.preventDefault();
                mode = $(this).data('dept-filter');
                apply();
            });
        });
    </script>
@endpush
