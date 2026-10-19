@extends('layouts.app')

@section('title', trans('common.sent_tickets.title'))

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">{{trans('common.mainpage')}}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">{{trans('sidebar.tickets.sent.text')}}</li>
    </ul>
@endsection

@section('content')
    <div class="card-body pt-0">
            <div class="d-flex flex-wrap align-items-center gap-3 mb-9">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" id="sent_search" class="form-control ps-12 w-250px" placeholder="{{trans('tickets.table.search')}}" autocomplete="off">
                </div>
                <div class="w-250px">
                    <select id="sent_dept" class="form-select" data-control="select2" data-placeholder="{{trans('tickets.table.all_depts')}}">
                        <option></option>
                        <option value="all">{{trans('tickets.table.all_depts')}}</option>
                        @foreach($sentDepartments as $dept)
                            <option value="{{$dept->id}}">{{$dept->name}}</option>
                        @endforeach
                    </select>
                </div>
                <label class="form-check form-switch form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" id="sent_overdue">
                    <span class="form-check-label fw-semibold text-gray-700">{{trans('tickets.table.only_overdue')}}</span>
                </label>
            </div>
            <div class="row g-9">
                <div class="col-md-4 col-lg-12 col-xl-4">
                    <div class="mb-9">
                        <div class="d-flex flex-stack">
                            <div class="fw-bold fs-4">
                                {{trans('tickets.sent.opened')}}
                                <span class="fs-6 text-gray-500 ms-2 sent-col-count">
                                    {{count($openTickets)}}
                                </span>
                            </div>
                        </div>
                        <div class="h-3px w-100 bg-dark"></div>
                    </div>
                    <a href="#" class="btn btn-success er w-100 fs-6 px-8 py-4 mb-10"
                       data-bs-toggle="modal"
                       data-bs-target="#kt_modal_new_ticket">
                        <i class="ki-outline ki-plus-square fs-2"></i>{{trans('tickets.table.create_ticket')}}
                    </a>
                    @foreach($openTickets as $ticket)
                        <x-sent-ticket-item :ticket="$ticket"></x-sent-ticket-item>
                    @endforeach
                </div>
                <div class="col-md-4 col-lg-12 col-xl-4">
                    <div class="mb-9">
                        <div class="d-flex flex-stack">
                            <div class="fw-bold fs-4">
                                {{trans('tickets.sent.in_progress')}}
                                <span class="fs-6 text-gray-500 ms-2 sent-col-count">
                                    {{count($inProgressTickets)}}
                                </span>
                            </div>
                        </div>
                        <div class="h-3px w-100 bg-warning"></div>
                    </div>
                   @foreach($inProgressTickets as $ticket)
                        <x-sent-ticket-item :ticket="$ticket"></x-sent-ticket-item>
                   @endforeach
                </div>
                <div class="col-md-4 col-lg-12 col-xl-4">
                    <div class="mb-9">
                        <div class="d-flex flex-stack">
                            <div class="fw-bold fs-4">
                                {{trans('tickets.sent.done')}}
                                <span class="fs-6 text-gray-500 ms-2 sent-col-count">
                                    {{count($doneTickets)}}
                                </span>
                            </div>
                        </div>
                        <div class="h-3px w-100 bg-primary"></div>
                    </div>
                    @foreach($doneTickets as $ticket)
                        <x-sent-ticket-item :ticket="$ticket"></x-sent-ticket-item>
                    @endforeach
                </div>
            </div>

            @if($archivedTickets->isNotEmpty())
                <div class="card card-flush mt-10">
                    <div class="card-header pt-5">
                        <h3 class="card-title">
                            <span class="card-label fw-bold text-gray-800">{{trans('tickets.table.archive')}}</span>
                            <span class="text-muted fs-7 ms-3">{{trans('tickets.table.archive_hint')}}</span>
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-3">
                            <tbody class="fw-semibold text-gray-600">
                            @foreach($archivedTickets as $ticket)
                                <tr class="clickable-row cursor-pointer" data-href="{{ route('cabinet.tickets.show', $ticket) }}">
                                    <td class="ps-3"><a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold">#{{$ticket->id}}</a></td>
                                    <td>{{Str::limit(strip_tags($ticket->text), 70)}}</td>
                                    <td>{{$ticket->department?->name}}</td>
                                    <td class="text-nowrap">{{$ticket->created_at->isoFormat('D MMM YYYY')}}</td>
                                    <td><x-ticket-status-badge :status="$ticket->status->label()" :color="$ticket->status->color()"></x-ticket-status-badge></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
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
    @include('partials.modals.tickets.cancel')
    @include('partials.modals.tickets.close_rating')
@endpush

@push('custom_js')
    <script>
        $(function () {
            const $cards = $('.clickable-card');
            function applyFilters() {
                const q = $('#sent_search').val().trim().toLowerCase();
                const dept = $('#sent_dept').val();
                const overdueOnly = $('#sent_overdue').is(':checked');
                $cards.each(function () {
                    const $c = $(this);
                    const ok = (!q || String($c.data('search')).includes(q))
                        && (!dept || dept === 'all' || String($c.data('dept')) === dept)
                        && (!overdueOnly || $c.data('overdue') === 1);
                    $c.toggle(ok);
                });
                $('.sent-col-count').each(function () {
                    $(this).text($(this).closest('.col-md-4').find('.clickable-card:visible').length);
                });
            }
            $('#sent_search').on('keyup', applyFilters);
            $('#sent_dept').on('change', applyFilters);
            $('#sent_overdue').on('change', applyFilters);
            $('.clickable-row').on('click', function (e) {
                if ($(e.target).closest('a, button').length) return;
                window.location.href = $(this).data('href');
            });
        });
    </script>
    <script src="{{asset('assets/js/custom/tickets/table.js')}}"></script>
    <script src="{{asset('assets/js/custom/tickets/create.js')}}"></script>
    <script src="{{asset('assets/js/custom/initFileUpload.js')}}"></script>

    <script>
        //filepond
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        FilePond.registerPlugin(FilePondPluginFileValidateSize);

        const uploadManager = FileUploadManager.create({
            pondSelector: '.my-pond',
            submitBtnSelector: '#create_ticket_form_submit',
            formSelector: '#kt_modal_new_ticket_form',
            modalSelector: '#kt_modal_new_ticket',

            uploadRoute: '{{ route('cabinet.files.upload') }}',
            deleteRoute: '{{ route('cabinet.files.delete') }}',
            deleteTempFolderRoute: '{{ route('cabinet.files.delete-temp-folder') }}',

            csrfToken: '{{ csrf_token() }}',
            requireFilesForSubmit: false,

            labelFileTypeNotAllowed: '{{ trans('tickets.create_modal.format_error') }}',
            labelMaxFileSizeExceeded: '{{ trans('tickets.create_modal.size_limit') }}',
            labelIdle: '{{ trans('tickets.create_modal.attachments_hint') }}'
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
                        Swal.fire('{{trans('common.swal.success_title')}}', '{{trans('common.swal.success_text')}}', 'success');
                        window.location.href = '{{route('cabinet.tickets.sent')}}';
                    } else {
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', 'common.swal.error_text', 'error');
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
                    Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                },
            });
        });

        //close ticket
        let ticket_id;
        $('.close_ticket').on('click', function (e) {
            ticket_id = $(this).data('id');
        });

        $('#close_ticket_submit').click(function (e) {
            e.preventDefault();
            $('#close_ticket_id').val(ticket_id);
            applyWait($('body'));
            $.ajax({
                url: '{{route('cabinet.tickets.close', ':id')}}'.replace(':id', ticket_id),
                method: "POST",
                headers: {'X-CSRF-TOKEN': '{{csrf_token()}}'},
                data: $('#close_ticket_form').serialize(),
                success: function(response)
                {
                    if (response.success) {
                        $('#close_ticket_form')[0].reset();
                        $('#close_ticket_modal').modal('hide');
                        $('.ticket_item_' + ticket_id).remove();
                        removeWait($('body'));
                        Swal.fire({
                            title: '{{trans('common.swal.success_title')}}',
                            html: 'Тикет <b>"' + ticket_id + '"</b> закрыт.',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    } else {
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
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
                        errorMessage = `<p class="mb-0">${response.responseJSON.message}</p>`;
                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    }
                    Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                },
                complete: function () {
                    removeWait($('body'));
                }
            });
        });

        // cancel ticket
        let ticketId;
        $('.cancel-ticket-btn').on('click', function (e) {
            ticketId = $(this).data('ticket_id');
        });
        $('#cancel_ticket_submit').click(function (e) {
            e.preventDefault();
            let button = $(this);
            console.log(button)
            $('#cancel_ticket_id').val(ticketId);
            applyWait($('body'));
            $.ajax({
                url: "{{route('cabinet.tickets.cancel')}}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: $('#cancel_ticket_form').serialize(),
                success: function (response) {
                    if(response.success) {
                        removeWait($('body'));
                        window.location.reload();
                        Swal.fire('{{trans('common.swal.success_title')}}', '{{trans('common.swal.success_text')}}', 'success');
                    } else {
                        Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
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
                        errorMessage = `<p class="mb-0">${response.responseJSON.message}</p>`;
                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    }
                    Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                },
                complete: function () {
                    removeWait($('body'));
                }
            });
        });

    </script>
    @stack('js_from_modal')
    @stack('scripts')
@endpush
