@extends('layouts.app')

@section('title', 'Тикеты моего отдела')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">Главная</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">Тикеты отдела</li>
    </ul>
@endsection

@section('content')
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                    <input type="text" data-dept-tickets-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Поиск..." />
                </div>
            </div>
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                @if(count($tickets))
                    <div class="w-100 mw-250px">
                        <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Приоритет" data-kt-ecommerce-priority-filter="priority">
                            <option></option>
                            <option value="all">Все приоритеты</option>
                            @foreach($priorities as $item)
                                <option value="{{$item->getNameByLocale()}}">{{$item->getNameByLocale()}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-100 mw-150px">
                        <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Все статусы" data-dept-tickets-filter="status">
                            <option></option>
                            <option value="all">Все статусы</option>
                            @foreach($statusLabels as $item)
                                <option value="{{$item}}">{{$item}}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                    <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#kt_modal_new_ticket">
                        <i class="ki-outline ki-plus-square fs-2"></i>Создать тикет
                    </a>
            </div>
        </div>
        <div class="card-body pt-0">
            @if(count($tickets))
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="dept_tickets_table">
                    <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="">Тикет</th>
                        <th class="min-w-125px">Автор</th>
                        <th class="min-w-125px">Приоритет</th>
                        <th class="min-w-125px">Дата создания</th>
                        <th>Статус</th>
                        <th>Исполнители</th>
                        <th class="text-end min-w-100px">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    @foreach($tickets as $ticket)
                        <tr class="position_row_1">
                            <td>
                                <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold">#{{$ticket->id}}</a>
                            </td>
                            <td class="d-flex align-items-center border-0">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                    <a href="#">
                                        <div class="symbol-label">
                                            <img src="{{$ticket->creator->getAvatar()}}" alt="{{$ticket->creator->name}}" class="w-100" />
                                        </div>
                                    </a>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="#" class="text-gray-800 text-hover-primary mb-1">
                                        {{$ticket->creator->name}}
                                    </a>
                                    <span>{{$ticket->creator->email}}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{$ticket->priority->class}} fw-bold fs-7">
                                    {{$ticket->priority->getNameByLocale()}}
                                </span>
                            </td>
                            <td>
                                {{\Carbon\Carbon::parse($ticket->created_at)->isoFormat('D.MM.YYYY HH:mm')}}
                            </td>
                            <td>
                                <span class="badge badge-light-{{$ticket->status->color()}} fw-bold fs-7">
                                    {{$ticket->status}}
                                </span>
                            </td>
                            <td>
                                @if($ticket->performers->count() > 0)
                                    <div class="symbol-group symbol-hover flex-nowrap">
                                        @foreach($ticket->performers as $key => $user)
                                            @if($key < 3)
                                                <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="{{$user->name}}" data-bs-original-title="{{$user->name}}">
                                                    <img alt="avatar" src="{{$user->getAvatar()}}">
                                                </div>
                                            @endif
                                            @if($loop->last && $loop->count > 3)
                                                <a href="#" class="symbol symbol-35px symbol-circle" data-bs-toggle="modal" data-bs-target="#kt_modal_view_users">
                                                    <span class="symbol-label bg-light text-gray-400 fs-8 fw-bold">+{{$loop->count - 3}}</span>
                                                </a>
                                            @endif
                                        @endforeach

                                    </div>
                                @else
                                    @if(! $ticket->status->is(App\Enums\TicketStatusEnum::COMPLETED))
                                        <a href="#" class="text-gray-500 border border-gray-300 border-dashed rounded py-1 px-3 mx-3 mb-3">
                                            Выбрать
                                        </a>
                                    @endif
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="my-3 ms-9">
                                    @if(! $ticket->status->is(App\Enums\TicketStatusEnum::COMPLETED))
                                        <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_new_card">
                                            <span data-bs-toggle="tooltip" data-bs-trigger="hover" aria-label="Добавить исполнителя" data-bs-original-title="Добавить исполнителя">
                                                <i class="ki-outline ki-user-tick fs-3"></i>
                                            </span>
                                        </button>
                                    @endif
                                    <a href="{{route('cabinet.tickets.show', $ticket)}}" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3">
                                        <span data-bs-toggle="tooltip" data-bs-trigger="hover" aria-label="Подробнее" data-bs-original-title="Подробнее">
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </span>
                                    </a>
                                        @if(! $ticket->status->is(App\Enums\TicketStatusEnum::COMPLETED))
                                            <button disabled class="btn btn-icon btn-active-light-primary w-30px h-30px me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_new_card">
                                                <span data-bs-toggle="tooltip" data-bs-trigger="hover" aria-label="Перенаправить" data-bs-original-title="Перенаправить">
                                                    <i class="ki-outline ki-entrance-left fs-3"></i>
                                                </span>
                                            </button>
                                        @endif
                                    <button class="btn btn-icon btn-active-light-primary w-30px h-30px" data-bs-toggle="tooltip" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" aria-label="Изменить статус" data-bs-original-title="Изменить статус">
                                        <i class="ki-outline ki-switch fs-3"></i>
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold w-150px py-3" data-kt-menu="true">
                                        @if(! $ticket->status->is(App\Enums\TicketStatusEnum::IN_PROGRESS))
                                            <div class="menu-item px-3">
                                                <a href="#" class="in_progress_btn menu-link px-3" data-id="{{$ticket->id}}">В процессе</a>
                                            </div>
                                        @endif
                                        @if(! $ticket->status->is(App\Enums\TicketStatusEnum::COMPLETED))
                                            <div class="menu-item px-3">
                                                <a href="#" class="complete_btn menu-link px-3" data-id="{{$ticket->id}}" data-bs-toggle="modal" data-bs-target="#complete_ticket_modal">
                                                    Выполнен
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 my-5">
                    <i class="ki-outline ki-information fs-2tx text-warning me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1 ">
                        <div class=" fw-semibold">
                            <h4 class="text-gray-900 fw-bold">У Вашего департамента нет тикетов</h4>
                            <div class="fs-6 text-gray-700 ">текст</div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

@endsection



@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('modals')
    @include('partials.modals.tickets.create')
    @include('partials.modals.tickets.complete')
@endpush

@push('custom_js')
    <script src="{{asset('assets/js/custom/tickets/table.js')}}"></script>
    <script src="{{asset('assets/js/custom/tickets/create.js')}}"></script>

    <script>
        let token = $('meta[name="_token"]').attr('content');

        //create ticket
        $('#create_ticket_form_submit').click(function (e) {
            let form = $('#kt_modal_new_ticket_form');
            e.preventDefault();
            $.ajax({
                url: "{{route('cabinet.tickets.store')}}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: form.serialize(),
                success: function (response) {
                    if(response.status === 'success') {
                        window.location.href = '{{route('cabinet.dept.tickets')}}';
                        Swal.fire('Все прошло успешно!', '{{trans('common.swal.success_text')}}', 'success');
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
                    Swal.fire('Произошла ошибка!', errorMessage, 'error');
                },
            });
        });

        // completed ticket
        let form = $('#complete_ticket_form');
        $('.complete_btn').on('click', function () {
            let ticket_id = $(this).attr('data-id');
            form[0].reset();
            $('#ticket_id').val(ticket_id);
        });
        $('#complete_ticket_submit').click(function (e) {
            //let overlay = $('#loaderOverlay');
            e.preventDefault();
            //overlay.show();
            $.ajax({
                url: "{{route('cabinet.tickets.complete')}}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: form.serialize(),
                success: function (response) {
                    //overlay.hide();
                    if(response.status === 'success') {
                        window.location.href = '{{route('cabinet.dept.tickets')}}';
                        Swal.fire('Все прошло успешно!', '{{trans('common.swal.success_text')}}', 'success');
                    } else {
                        Swal.fire('Произошла ошибка!', '{{trans('common.swal.error_text')}}', 'error');
                    }
                },
                error: function (response) {
                    //overlay.hide();
                    let errorMessage = '';
                    if (response.status === 422) {
                        const errors = response.responseJSON.errors;
                        for (const key in errors) {
                            errorMessage += `<p class="mb-0">${errors[key][0]}</p>`;
                        }
                    }
                    Swal.fire('Произошла ошибка!', errorMessage, 'error');
                },
            });
        });
    </script>
@endpush
