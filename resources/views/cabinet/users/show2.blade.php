@extends('layouts.app')

@section('title', 'Пользователь')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">
                {{trans('common.mainpage')}}
            </a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.dept.users.index')}}" class="text-muted text-hover-primary">
                Сотрудники отдела
            </a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">{{$user->name}}</li>
    </ul>
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid me-lg-15 order-2 order-lg-1 mb-10 mb-lg-0">
            @unless($user->active)
                <div class="alert alert-warning d-flex align-items-center mb-6">
                    <i class="ki-outline ki-information-5 fs-2 text-warning me-3"></i>
                    <div>
                        Сотрудник неактивен.
                        @if($stuckCount > 0)
                            У него остались незакрытые тикеты (<strong>{{$stuckCount}}</strong>) — их нужно переназначить.
                        @endif
                    </div>
                </div>
            @endunless
            <div class="row g-5 mb-6">
                @foreach([
                    ['Новые', $counts[\App\Enums\TicketStatusEnum::OPENED->value] ?? 0, 'gray-800'],
                    ['В работе', $counts[\App\Enums\TicketStatusEnum::IN_PROGRESS->value] ?? 0, 'warning'],
                    ['Просрочено', $overdueCount, 'danger'],
                    ['Выполнено', ($counts[\App\Enums\TicketStatusEnum::DONE->value] ?? 0) + ($counts[\App\Enums\TicketStatusEnum::COMPLETED->value] ?? 0), 'success'],
                ] as [$label, $value, $color])
                    <div class="col-6 col-md-3">
                        <div class="card card-flush h-100">
                            <div class="card-body py-5">
                                <div class="fs-2hx fw-bold text-{{$color}}">{{$value}}</div>
                                <div class="text-gray-600 fw-semibold fs-6">{{$label}}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row g-5 mb-6">
                @foreach([
                    ['Всего тикетов', $metrics['total'], 'gray-800'],
                    ['Выполнено в срок', $metrics['on_time_percent'] === null ? '—' : $metrics['on_time_percent'] . '%', 'primary'],
                    ['Среднее время выполнения', formatMinutes($metrics['avg_minutes']), 'gray-800'],
                ] as [$label, $value, $color])
                    <div class="col-6 col-md-4">
                        <div class="card card-flush h-100">
                            <div class="card-body py-5">
                                <div class="fs-2 fw-bold text-{{$color}} lh-1 mb-2">{{$value}}</div>
                                <div class="text-gray-600 fw-semibold fs-7">{{$label}}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $ticketTabs = [
                    'user_tickets_active' => ['В работе', $activeTickets],
                    'user_tickets_finished' => ['Выполненные', $finishedTickets],
                    'user_tickets_canceled' => ['Отменённые', $canceledTickets],
                ];
            @endphp
            <div class="card card-flush mb-6 mb-xl-9">
                <div class="card-header pt-5">
                    <div class="card-title"><h2 class="mb-0">Тикеты сотрудника</h2></div>
                    <div class="card-toolbar">
                        <form method="GET" class="d-flex align-items-center gap-2">
                            <div class="position-relative">
                                <i class="ki-outline ki-calendar fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                                <input type="text" id="user_period" name="period" value="{{$period}}"
                                       class="form-control ps-12 w-250px" placeholder="{{trans('common.reports.period.all_time')}}"
                                       autocomplete="off" readonly>
                            </div>
                            @if($period !== '')
                                <a href="{{url()->current()}}" class="btn btn-icon btn-light" data-bs-toggle="tooltip" title="Сбросить">
                                    <i class="ki-outline ki-cross fs-2"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-semibold mb-5">
                        @foreach($ticketTabs as $id => [$title, $list])
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 {{$loop->first ? 'active' : ''}}" data-bs-toggle="tab" href="#{{$id}}">
                                    {{$title}} <span class="badge badge-light ms-2">{{$list->count()}}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content">
                        @foreach($ticketTabs as $id => [$title, $list])
                            <div class="tab-pane fade {{$loop->first ? 'show active' : ''}}" id="{{$id}}" role="tabpanel">
                                @if($list->isEmpty())
                                    <div class="text-gray-600 py-5">Нет тикетов</div>
                                @else
                                    <div class="table-responsive mh-500px scroll-y">
                                        <table class="table align-middle table-row-dashed fs-6 gy-3">
                                            <thead>
                                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                                                    <th>Тикет</th>
                                                    <th>Автор</th>
                                                    <th>Приоритет</th>
                                                    <th>Статус</th>
                                                    <th>Создан</th>
                                                    <th>Дедлайн</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-700 fw-semibold">
                                                @foreach($list as $ticket)
                                                    <tr>
                                                        <td class="min-w-250px">
                                                            <a href="{{route('cabinet.tickets.show', $ticket)}}" class="fw-bold text-gray-900 text-hover-primary" target="_blank">#{{$ticket->id}}</a>
                                                            <div class="text-gray-600 fw-normal">{{\Illuminate\Support\Str::limit(strip_tags($ticket->text), 80)}}</div>
                                                        </td>
                                                        <td>{{$ticket->creator?->name}}</td>
                                                        <td>
                                                            @if($ticket->priority)
                                                                <span class="badge badge-light-{{$ticket->priority->class}}">{{$ticket->priority->getNameByLocale()}}</span>
                                                            @endif
                                                        </td>
                                                        <td><span class="badge badge-light-{{$ticket->status->color()}}">{{$ticket->status->label()}}</span></td>
                                                        <td class="text-nowrap">{{$ticket->created_at->isoFormat('D MMM YYYY, HH:mm')}}</td>
                                                        <td class="text-nowrap">
                                                            @if($ticket->due_date)
                                                                <span class="{{$loop->parent->first && $ticket->isDue() ? 'text-danger fw-bold' : ''}}">{{$ticket->due_date->isoFormat('D MMM YYYY, HH:mm')}}</span>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

@can('accesses', $user)
            @if(
                auth()->user()->getDepartmentId() === $user->getDepartmentId() &&
                $user->getDepartment() &&
                $user->getDepartment()->active
            )
        <div class="card card-flush pt-4 mb-6 mb-xl-9">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="mb-0">Разрешения</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-5">
                    <li class="nav-item">
                        <a class="nav-link text-active-primary d-flex align-items-center pb-5 active"
                           data-bs-toggle="tab" href="#user_roles">
                            Роли
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary d-flex align-items-center pb-5"
                           data-bs-toggle="tab" href="#user_permissions">
                            Прямые полномочия
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="user_roles" role="tabpanel">
                        <form id="attach_roles_form" method="POST">
                            @csrf
                            <div class="pt-5">
                                @forelse(auth()->user()->getDepartment()->roles as $item)
                                    <div class="mb-5">
                                        <label class="form-check form-check-custom form-check-solid align-items-start">
                                            <input class="form-check-input me-3 mt-1" type="checkbox" name="roles[]" value="{{$item->id}}"
                                                {{ is_array($user->roles->pluck('id')->toArray())
                                                 &&
                                                 in_array($item->id, $user->roles->pluck('id')->toArray())
                                                  ? 'checked' : '' }}
                                            >
                                            <span class="form-check-label d-flex flex-column align-items-start">
                                    <a href="{{route('cabinet.dept.roles.show', $item)}}" class="fw-bold text-gray-800 text-hover-primary fs-5 mb-0" target="_blank">
                                        {{$item->name}}
                                    </a>
                                <span class="text-muted fs-6">
                                    Разрешений: {{count($item->permissions)}}
                                </span>
                            </span>
                                        </label>
                                    </div>
                                @empty
                                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed  p-6">
                                        <div class="d-flex flex-stack flex-grow-1 ">
                                            <div class=" fw-semibold">
                                                <div class="fs-6 text-gray-700">
                                                    Вы можете присваивать сотруднику роль, но у Вашего отдела еще нет созданных ролей.
                                                    Для создания перейдите по
                                                    <a href="{{route('cabinet.dept.roles')}}" class="fw-bold" target="_blank">ссылке</a>.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                            <div class="pt-5">
                                <button id="attach_roles_submit" type="submit" class="btn btn-primary">Сохранить</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="user_permissions" role="tabpanel">
                        <form id="attach_permissions_form" method="POST">
                            @csrf
                            <div class="fv-row">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                                        <tbody class="text-gray-600 fw-semibold">
                                        <tr>
                                            <td class="fs-5 fw-bold text-gray-800">Полный доступ
                                                <span class="ms-2" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="Доступ на все имеющиеся функции">
                                        <i class="ki-outline ki-information-5 fs-7"></i>
                                </span></td>
                                            <td>
                                                <label class="form-check form-check-custom form-check-sm form-check-solid me-9">
                                                    <input class="form-check-input" type="checkbox" value="" id="select_all_permissions">
                                                    <span class="form-check-label">Выбрать все</span>
                                                </label>
                                            </td>
                                        </tr>
                                        @foreach($groupedPermissions as $permission => $items)
                                            <tr>
                                                <td class="text-gray-800">{{$permission}}</td>
                                                <td>
                                                    <div class="d-flex">
                                                        @foreach($items as $item)
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                                <input class="form-check-input permission_checkbox" type="checkbox" value="{{$item->id}}" name="permissions[]"
                                                                    {{ is_array($user->permissions->pluck('id')->toArray())
                                                                     &&
                                                                     in_array($item->id, $user->permissions->pluck('id')->toArray())
                                                                      ? 'checked' : '' }}
                                                                />
                                                                <span class="form-check-label">{{$item->name}}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <div class="pt-5">
                                        <button id="attach_permissions_submit" type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endcan

        </div>

        <div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xl-300px mb-10 order-1 order-lg-2">
            <div class="card card-flush pt-3 mb-0" data-kt-sticky="true" data-kt-sticky-name="subscription-summary" data-kt-sticky-offset="{default: false, lg: '200px'}" data-kt-sticky-width="{lg: '250px', xl: '300px'}" data-kt-sticky-left="auto" data-kt-sticky-top="150px" data-kt-sticky-animation="false" data-kt-sticky-zindex="95">
                <div class="card-body pt-0 fs-6">
                    <div class="">
                        <div class="d-flex flex-center flex-column py-5">
                            <div class="symbol symbol-100px symbol-circle mb-7">
                                @if($user->avatar)
                                    <img src="{{$user->avatar}}" alt="{{$user->name}}">
                                @else
                                    <div class="symbol-label fs-3 bg-light-dark text-dark">
                                        {{get_initials($user->name)}}
                                    </div>
                                @endif
                            </div>
                            <p class="fs-3 text-gray-800 fw-bold mb-3">
                                {{$user->name}}
                            </p>
                            <div class="mb-3">
                                <div class="badge badge-lg badge-light-secondary d-inline">
                                    {{$user->position}}
                                </div>
                                @unless($user->active)
                                    <div class="badge badge-lg badge-light-danger d-inline ms-1">Неактивен</div>
                                @endunless
                            </div>
                            <a href="{{route('cabinet.reports.tickets', ['filter' => ['executor_id' => $user->id]])}}" class="btn btn-sm btn-light-primary">
                                <i class="ki-outline ki-chart-simple fs-4"></i>Полный отчёт
                            </a>

                        </div>
                        <div class="separator separator-dashed mb-7"></div>
                        <div class="fs-6">
                            <div class="fw-bold mt-5">Отдел</div>
                            <div class="text-gray-600">{{$user->department}}</div>
                            <div class="fw-bold mt-5">Email</div>
                            <div class="text-gray-600">{{$user->email}}</div>
                            @if($user->mobile)
                                <div class="fw-bold mt-5">Номер</div>
                                <div class="text-gray-600">{{$user->mobile}}</div>
                            @endif
                            @if($user->pager)
                                <div class="fw-bold mt-5">3CX</div>
                                <div class="text-gray-600">{{$user->pager}}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('custom_js')
    <script>
        let token = $('meta[name="_token"]').attr('content');

        $('#user_period').daterangepicker({
            autoUpdateInput: false,
            ranges: {
                "{{trans('common.reports.period.today')}}": [moment(), moment()],
                "{{trans('common.reports.period.yesterday')}}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                "{{trans('common.reports.period.7_days')}}": [moment().subtract(6, 'days'), moment()],
                "{{trans('common.reports.period.30_days')}}": [moment().subtract(29, 'days'), moment()],
                "{{trans('common.reports.period.this_month')}}": [moment().startOf('month'), moment().endOf('month')],
            },
            locale: {
                customRangeLabel: "{{trans('common.reports.period.custom_date')}}",
                format: 'DD.MM.YYYY',
                firstDay: 1,
                applyLabel: "{{trans('common.reports.apply_btn')}}",
                cancelLabel: "{{trans('common.roles.buttons.cancel')}}",
            },
        }, function (start, end) {
            $('#user_period').val(start.format('DD.MM.YYYY') + ' - ' + end.format('DD.MM.YYYY'));
            $('#user_period').closest('form').submit();
        });

        // check all checkbox
        $("#select_all_permissions").click(function() {
            $(".form-check-input").prop("checked", $(this).prop("checked"));
        });
        $(".form-check-input").click(function() {
            if (!$(this).prop("checked")) {
                $("#select_all_permissions").prop("checked", false);
            }
        });


        //attach roles
        $('#attach_roles_submit').click(function (e) {
            let form = $('#attach_roles_form');
            e.preventDefault();
            applyWait($('body'));
            $.ajax({
                url: "{{route('cabinet.users.attach_roles', $user)}}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: form.serialize(),
                success: function (response) {
                    removeWait($('body'));
                    if(response.success) {
                        Swal.fire('Все прошло успешно!', '{{trans('common.swal.success_text')}}', 'success');
                        window.location.reload();
                    } else {
                        Swal.fire('Произошла ошибка!', '{{trans('common.swal.error_text')}}', 'error');
                    }
                },
                error: function (response) {
                    let errorMessage = '';
                    if (response.status === 422) {
                        const errors = response.responseJSON.errors;
                        for (const key in errors) {
                            errorMessage += `<p class="mb-0">${errors[key][0]}</p>`;
                        }
                    }
                    removeWait($('body'));
                    Swal.fire('Произошла ошибка!', errorMessage, 'error');
                },
            });
        });

        //attach permissions
        $('#attach_permissions_submit').click(function (e) {
            let form = $('#attach_permissions_form');
            e.preventDefault();
            applyWait($('body'));
            $.ajax({
                url: "{{route('cabinet.users.attach_permissions', $user)}}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: form.serialize(),
                success: function (response) {
                    removeWait($('body'));
                    if(response.success) {
                        Swal.fire('Все прошло успешно!', '{{trans('common.swal.success_text')}}', 'success');
                        window.location.reload();
                    } else {
                        Swal.fire('Произошла ошибка!', '{{trans('common.swal.error_text')}}', 'error');
                    }
                },
                error: function (response) {
                    let errorMessage = '';
                    if (response.status === 422) {
                        const errors = response.responseJSON.errors;
                        for (const key in errors) {
                            errorMessage += `<p class="mb-0">${errors[key][0]}</p>`;
                        }
                    }
                    removeWait($('body'));
                    Swal.fire('Произошла ошибка!', errorMessage, 'error');
                },
            });
        });
    </script>
@endpush
