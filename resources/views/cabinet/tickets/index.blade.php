@extends('layouts.app')

@section('title', 'Тикеты отдела')

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
                <a href="#" class="btn btn-success"
                   data-bs-toggle="modal"
                   data-bs-target="#kt_modal_new_ticket">
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
                        <th>Исполнитель</th>
                        <th class="text-end min-w-100px">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    @foreach($tickets as $ticket)
                        <tr class="position_row_{{$ticket->id}}">
                            <td>
                                <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold">#{{$ticket->id}}</a>
                            </td>
                            <td class="d-flex align-items-center border-bottom-1">
                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                    <a href="{{route('cabinet.users.show', $ticket->creator)}}" target="_blank">
                                        @if($ticket->creator->avatar)
                                            <div class="symbol-label">
                                                <img src="{{$ticket->creator->avatar}}" alt="{{$ticket->creator->name}}" class="w-100" />
                                            </div>
                                        @else
                                            <div class="symbol-label fs-3 bg-light-dark text-dark">
                                                {{get_initials($ticket->creator->name)}}
                                            </div>
                                        @endif
                                    </a>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="{{route('cabinet.users.show', $ticket->creator)}}" target="_blank" class="text-gray-800 text-hover-primary mb-1">
                                        {{$ticket->creator->name}}
                                    </a>
                                    <span>{{$ticket->creator->department}}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{$ticket->priority->class}} fw-bold fs-7">
                                    {{$ticket->priority->getNameByLocale()}}
                                </span>
                            </td>
                            <td>
                                {{\Carbon\Carbon::parse($ticket->created_at)->isoFormat('D MMMM, HH:mm')}}
                            </td>
                            <td id="ticket_status_{{$ticket->id}}">
                                <x-ticket-status-badge :status="$ticket->status->label()" :color="$ticket->status->color()"></x-ticket-status-badge>
                            </td>
                            <td>
                                <div class="">
                                    <div class="performers_symbols_{{$ticket->id}} symbol-group symbol-hover flex-nowrap">
                                        <x-ticket-performer :user="$ticket->performer" :ticket="$ticket"></x-ticket-performer>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="my-3 ms-9">
                                    <a href="{{route('cabinet.tickets.show', $ticket)}}" class="btn btn-icon btn-active-light-primary w-30px h-30px">
                                        <span data-bs-toggle="tooltip" data-bs-trigger="hover" aria-label="Подробнее" data-bs-original-title="Подробнее">
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </span>
                                    </a>

                                    @if(! $ticket->status->is(App\Enums\TicketStatusEnum::COMPLETED))
                                        <button class="btn btn-icon btn-active-light-primary w-30px h-30px ms-3 switch_{{$ticket->id}}" data-bs-toggle="tooltip" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" aria-label="Изменить статус" data-bs-original-title="Изменить статус">
                                            <i class="ki-outline ki-switch fs-3"></i>
                                        </button>
                                    @if(!$ticket->status->is(App\Enums\TicketStatusEnum::CANCELED) && ! $ticket->status->is(App\Enums\TicketStatusEnum::DONE))
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold w-150px py-3" data-kt-menu="true">
                                                @if(! $ticket->status->is(App\Enums\TicketStatusEnum::IN_PROGRESS))
                                                    <div class="menu-item px-3">
                                                        <a href="javascript:void(0);" class="in_progress_btn menu-link px-3" data-ticket_id="{{$ticket->id}}">В процессе</a>
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
                                        @endif
                                    @endif

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
                            <h4 class="text-gray-900 fw-bold mb-0">Нет тикетов</h4>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

@endsection



@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/plugins/filepond.min.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.jquery.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-type.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-size.js')}}"></script>
@endpush

@push('modals')
    @include('partials.modals.tickets.create')
    @include('partials.modals.tickets.complete')
    @include('partials.modals.tickets.attach_user')
@endpush

@push('custom_js')
    <script src="{{asset('assets/js/custom/tickets/table.js')}}"></script>
    <script src="{{asset('assets/js/custom/tickets/create.js')}}"></script>

    <script>
        //filepond
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        FilePond.registerPlugin(FilePondPluginFileValidateSize);

        $('.my-pond').filepond({
            server: {
                process: {
                    url: '{{ route('cabinet.files.upload') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    onload: (response) => {
                        let responseJson = JSON.parse(response);
                        if (responseJson.folder) {
                            // Получаем существующий массив папок из localStorage
                            let uploadedFolders = JSON.parse(localStorage.getItem('uploadedFolders')) || [];
                            // Добавляем новую папку в массив
                            uploadedFolders.push(responseJson.folder);
                            // Сохраняем обновленный массив в localStorage
                            localStorage.setItem('uploadedFolders', JSON.stringify(uploadedFolders));
                        }
                    },
                    onerror: (response) => {
                        console.error('Ошибка загрузки файла:', response);
                    }
                },
                revert: {
                    url: '{{ route('cabinet.files.delete') }}',
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            },
            allowMultiple: true,
            acceptedFileTypes: [
                'image/png',
                'image/jpg',
                'image/jpeg',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            labelFileTypeNotAllowed: 'Не поддерживаемый тип файла',
            maxFileSize: '5MB',
            labelMaxFileSizeExceeded: 'Файл слишком большой',
            labelIdle: 'Перетащите файлы сюда или нажмите, чтобы загрузить.'
        });


        let token = $('meta[name="_token"]').attr('content');

        //create ticket
        $('#create_ticket_form_submit').click(function (e) {
            e.preventDefault();
            let form = $('#kt_modal_new_ticket_form');
            let button = $(this);
            applyWait($('body'));
            $.ajax({
                url: "{{route('cabinet.tickets.store')}}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: form.serialize(),
                success: function (response) {
                    if(response.status === 'success') {
                        removeWait($('body'));
                        Swal.fire('Все прошло успешно!', '{{trans('common.swal.success_text')}}', 'success');
                        window.location.href = '{{route('cabinet.tickets.index')}}';
                    } else {
                        removeWait($('body'));
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

        // in progress
        $(document).on('click', '.in_progress_btn', function() {
            const button = $(this);
            button.blur();
            const ticketId = button.data('ticket_id');
            applyWait($('body'));
            const url = '{{ route('cabinet.tickets.inprogress', ':id') }}'.replace(':id', ticketId);
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    ticketId: ticketId,
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    console.log(response)
                    if (response.success) {
                        $('#ticket_status_' + ticketId).html(response.html);
                        $('#select_users_' + ticketId).html(response.html2);
                        button.remove();
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
                complete: function () {
                    removeWait($('body'));
                }
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
            e.preventDefault();
            let _ticket_id = $('#ticket_id').val();
            applyWait($('body'));
            $.ajax({
                url: "{{route('cabinet.tickets.complete')}}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: form.serialize(),
                success: function (response) {
                    if(response.success) {
                        removeWait($('body'));
                        $('#ticket_status_' + _ticket_id).html(response.html);
                        $('.switch_' + _ticket_id + ', .move_' + _ticket_id).remove();
                        $('#complete_ticket_modal').modal('hide');
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
                complete: function () {
                    removeWait($('body'));
                }
            });
        });

        // get ticket performer
        $('#attach_users_modal').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget);  // Кнопка, которая вызвала модалку
            let ticketId = button.data('ticket-id');  // ID тикета
            let modal = $(this);
            modal.find('.user-radio').addClass('d-none');
            modal.find('.spinner-border').removeClass('d-none');
            modal.find('#ticket-id').val(ticketId);
            $.ajax({
                url: '{{ route('cabinet.tickets.performers', ':id') }}'.replace(':id', ticketId),
                method: 'GET',
                success: function(response) {
                    let performer = response.performer ? response.performer['id'] : null;
                    modal.find('.user-radio').each(function() {
                        let $radio = $(this);
                        let userId = parseInt($radio.val());
                        let isChecked = performer === userId;
                        $radio.prop('checked', isChecked)
                            .removeClass('d-none')
                            .siblings('.spinner-border').addClass('d-none');
                    });
                },
                error: function() {
                    Swal.fire('Произошла ошибка!', 'Попробуйте еще раз.', 'error');
                    modal.find('.user-radio').removeClass('d-none');
                    modal.find('.spinner-border').addClass('d-none');
                }
            });
        });

        //attach users
        $('#attach_user_submit').click(function (e) {
            e.preventDefault();
            let button = $(this);
            let ticket_id = $('#ticket-id').val();
            let performersSymbols = $('.performers_symbols_' + ticket_id);
            let form = $('#attach_users_form');
            applyWait($('body'));
            $.ajax({
                url: '{{ route('cabinet.tickets.attach_users') }}',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': token},
                data: form.serialize(),
                success: function (response) {
                    if(response.status === 'success') {
                        performersSymbols.html(response.html);
                        $('#attach_users_modal').modal('toggle');
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
                    } else if (response.status === 403) {
                        Swal.fire('Произошла ошибка!', response.responseJSON.message, 'error');
                    } else {
                        Swal.fire('Произошла ошибка!', errorMessage, 'error');
                    }
                },
                complete: function () {
                    removeWait($('body'));
                }
            });
        })

        // close modal events
        $('#kt_modal_new_ticket').on('hidden.bs.modal', function () {
            let folders = localStorage.getItem('uploadedFolders') || [];

            if (folders.length > 0) {
                $.ajax({
                    url: '{{ route('cabinet.files.delete') }}',
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    contentType: 'application/json',
                    data: JSON.stringify(folders),
                    success: function(response) {
                        const pondElement = $('.my-pond')[0];
                        const pondInstance = FilePond.find(pondElement);
                        if (pondInstance) {
                            pondInstance.removeFiles();
                        }

                        $('#kt_modal_new_ticket_form')[0].reset();
                        localStorage.removeItem('uploadedFolders');
                    },
                    error: function(xhr) {
                        console.error('Ошибка при удалении файлов:', xhr);
                    }
                });
            }
        });

    </script>
@endpush
