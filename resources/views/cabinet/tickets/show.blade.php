@extends('layouts.app')

@section('title', trans('common.tickets.title').': ' . '#'.$ticket->id)

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">{{trans('common.mainpage')}}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.tickets.index')}}" class="text-muted text-hover-primary">
                {{trans('common.tickets.title')}}
            </a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">#{{$ticket->id}}</li>
    </ul>
@endsection

@section('content')
    @php
        $me = auth()->user();
        $st = $ticket->status;
        $isFinal = $st->is(\App\Enums\TicketStatusEnum::COMPLETED) || $st->is(\App\Enums\TicketStatusEnum::CANCELED);
        $isOwnDept = $me->getDepartmentId() === $ticket->department->id;
        $parentCanceled = $ticket->parent && $ticket->parent->status->is(\App\Enums\TicketStatusEnum::CANCELED);

        // Кто и когда видит кнопки (те же условия, что проверяет сервер)
        $canCancel = ! $isFinal && ! $parentCanceled && $me->can('cancel', $ticket);
        $canReopen = $st->is(\App\Enums\TicketStatusEnum::DONE) && $me->can('close', $ticket);
        $canClose = $st->is(\App\Enums\TicketStatusEnum::DONE) && $me->can('close', $ticket);
        $canStart = $st->is(\App\Enums\TicketStatusEnum::OPENED) && (! $ticket->performer || $ticket->performer->id === $me->id) && $isOwnDept;
        $canComplete = $st->is(\App\Enums\TicketStatusEnum::IN_PROGRESS) && $ticket->performer && $ticket->performer->id === $me->id;
        $canChangeDeadline = ($st->is(\App\Enums\TicketStatusEnum::OPENED) || $st->is(\App\Enums\TicketStatusEnum::IN_PROGRESS)) && $me->can('updateDeadline', $ticket);
        $canRequestApproval = ! $isFinal && $me->can('canApprovalRequest', $ticket);
        $canComment = ! $isFinal && $me->can('comment', $ticket);
        $hasMenu = $canCancel || $canReopen;
        $approvalRequestsById = $ticket->approvalRequests->keyBy('id');

        // Хронология тикета
        $statusTimes = $ticket->histories->where('action', \App\Enums\TicketActionEnum::UPDATE_STATUS);
        $firstTakenAt = $statusTimes->where('status', \App\Enums\TicketStatusEnum::IN_PROGRESS)->sortBy('created_at')->first()?->created_at;
        $doneAt = $ticket->doneAt();
        $closedAt = $st->is(\App\Enums\TicketStatusEnum::COMPLETED) ? $ticket->statusChangedAt(\App\Enums\TicketStatusEnum::COMPLETED) : null;
        $canceledAt = $st->is(\App\Enums\TicketStatusEnum::CANCELED) ? $ticket->statusChangedAt(\App\Enums\TicketStatusEnum::CANCELED) : null;
        $finishedAt = $closedAt ?? $canceledAt ?? $doneAt;
        $reactionMin = $firstTakenAt ? (int) $ticket->created_at->diffInMinutes($firstTakenAt) : null;
        $workMin = ($firstTakenAt && $doneAt && $doneAt->gte($firstTakenAt)) ? (int) $firstTakenAt->diffInMinutes($doneAt) : null;
        $totalMin = (int) ($finishedAt ? $ticket->created_at->diffInMinutes($finishedAt) : $ticket->created_at->diffInMinutes(now()));
    @endphp

    <div class="row g-xxl-9">
        <div class="col-xxl-8">
            <div class="card">
@include('partials.tickets.show.header')
                <div class="card-body">

                    @if($ticket->parent)
                        @if($ticket->parent->status->is(App\Enums\TicketStatusEnum::CANCELED))
                            @include('partials.notices.ticket-status', [
                                'icon' => 'ki-lock-2',
                                'color' => 'danger',
                                'title' => trans('tickets.parent_ticket_cancelled'),
                                'message' => $ticket->parent->getCanceledTicketComment()
                            ])
                        @endif
                    @endif

                    @if($ticket->status->is(App\Enums\TicketStatusEnum::COMPLETED))
                        @include('partials.notices.ticket-status', [
                            'icon' => 'ki-lock-2',
                            'color' => 'success',
                            'title' => trans('tickets.statuses.ticket_done'),
                            'message' => null
                        ])
                    @endif

                    @if($ticket->status->is(App\Enums\TicketStatusEnum::CANCELED))
                        @include('partials.notices.ticket-status', [
                            'icon' => 'ki-lock-2',
                            'color' => 'danger',
                            'title' => trans('tickets.statuses.ticket_canceled'),
                            'message' => $ticket->getCanceledTicketComment()
                        ])
                    @endif

                        <div class="badge badge-lg badge-light-secondary mb-4">
                            <div class="d-flex align-items-center flex-wrap">
                                @if($ticket->parent && !$ticket->parent->isPrivate())
                                {{trans('tickets.table.main_ticket')}}: <a href="{{route('cabinet.tickets.show', $ticket->parent->id)}}" class="text-dgray-800 text-hover-primary ms-1" target="_blank">
                                        #{{$ticket->parent_id}}
                                    </a>
                                @else
                                    {{$ticket->creator->department}}
                                    <i class="ki-outline ki-right fs-2 text-gray-800 mx-1"></i>
                                    {{$ticket->department->name}}
                                @endif
                            </div>
                        </div>

                    <div class="d-flex flex-wrap justify-content-between gap-5">
                        <div>
                            <div class="d-flex text-gray-800 fw-bold fs-12 mb-3 ms-1">
                                {{trans('tickets.table.creator')}}:
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                    @if($ticket->creator->avatar)
                                        <div class="symbol-label cursor-default">
                                            <img src="{{$ticket->creator->avatar}}" alt="{{$ticket->creator->name}}" class="w-100" />
                                        </div>
                                    @else
                                        <div class="symbol-label fs-3 bg-light-dark text-gray-800">
                                            {{get_initials($ticket->creator->name)}}
                                        </div>
                                    @endif
                                </div>

                                <div class="pe-5">
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        <p class="fw-bold text-gray-900 mb-0">
                                            {{$ticket->creator->name}}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-muted fw-semibold">
                                            {{$ticket->creator->email}}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-muted fw-semibold">
                                            {{$ticket->creator->pager}}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex text-gray-800 fw-bold fs-12 mb-3 ms-1">
                                {{trans('tickets.responsible')}}:
                                @if($ticket->status->is(\App\Enums\TicketStatusEnum::IN_PROGRESS) || $ticket->status->is(\App\Enums\TicketStatusEnum::OPENED))
                                    @if($ticket->performer)
                                        @can('assign', $ticket)
                                            <a href="javascript:void(0);"
                                               data-bs-toggle="modal"
                                               data-bs-target="#attach_users_modal"
                                               data-ticket-id="{{ $ticket->id }}">
                                                <i class="ki-outline ki-arrows-loop text-gray-800 fs-2 ms-2 mt-1"></i>
                                            </a>
                                        @endcan
                                    @endif
                                @endif

                            </div>
                            <div class="performers_symbols symbol-group symbol-hover flex-nowrap">
                                <div class="performer_wrapper">
                                    <x-ticket-performer-full :user="$ticket->performer" :ticket="$ticket"></x-ticket-performer-full>
                                </div>
                            </div>
                        </div>
                    </div>

@include('partials.tickets.show.deadline_time')
                        <div class="py-5 mt-2">
                            <p>{!! nl2br(e($ticket->text)) !!}</p>
                        </div>

                    @if($ticket->media->isNotEmpty())
                        <div class="my-5 pb-5">
                            @foreach($ticket->media as $item)
                                <div class="d-flex flex-aligns-center pe-10 pe-lg-20 mb-3">
                                    <img alt="{{$item->filename}}" class="w-40px me-3" src="{{ asset('assets/media/extensions/'.$item->extension.'.png') }}">
                                    <div class="ms-1 fw-semibold">
                                        <a href="{{ route('cabinet.tickets.media.download', $item) }}" class="fs-6 text-hover-primary fw-bold" target="_blank">
                                            {{ $item->filename }}
                                        </a>
                                        <div class="text-gray-500">
                                            {{ bytes_to_mb($item->size) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif


                    @if($ticket->department->id === auth()->user()->getDepartmentId())
                        @if($departmentTags->isNotEmpty())
                            <select class="form-select form-select-solid"
                                    @if($ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED)
                                        || $ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED))
                                        disabled
                                    @endif
                                    name="tags"
                                    data-control="select2"
                                    data-close-on-select="false"
                                    data-placeholder="{{trans('tickets.table.tags')}}"
                                    data-allow-clear="true" multiple="multiple">
                                <option></option>
                                @foreach($departmentTags as $tag)
                                    <option
                                        value="{{$tag->id}}" {{ in_array($tag->id, $ticket->tags->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{$tag->text}}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed  p-6">
                                <div class="d-flex flex-stack flex-grow-1 ">
                                    <div class=" fw-semibold">
                                        <div class="fs-6 text-gray-700">
                                            {{trans('tickets.tags_text1')}}
                                            @can('create', \App\Models\Tag::class)
                                                {{trans('tickets.tags_text2')}}
                                                <a href="{{route('cabinet.tags.index')}}" class="fw-bold" target="_blank">{{trans('tickets.tags_text_link')}}</a>.
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                        <div class="card card-flush mt-8 py-2">
                        <div class="card-header">
                            <div class="card-title flex-column">
                                <h3 class="fs-5 fw-bold mb-1">
                                    Одобрения тикета
                                </h3>
                                <div class="fs-6 text-gray-500">
                                    Решения ответственных сотрудников
                                </div>
                            </div>

                            @if($canRequestApproval)
                                <div class="card-toolbar">
                                    <button class="btn btn-sm btn-light-primary btn-active-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approved_users_modal">
                                        <i class="ki-outline ki-send fs-2"></i>
                                        Отправить запрос
                                    </button>
                                </div>
                            @endif
                        </div>

                        @if($ticket->approvalRequests->count())
                            <div class="card-body d-flex flex-column p-8 py-3">
                                @foreach($ticket->approvalRequests as $request)
                                    @include('partials.modals.approval_requests.approval_item', ['request' => $request])
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>
            @include('partials.tickets.show.children')
        </div>

        @include('partials.tickets.show.feed')
    </div>
@endsection

@push('modals')
    @if(!$ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED)
        && !$ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED))
        @include('partials.modals.tickets.create-sub')
    @endif
    @include('partials.modals.tickets.complete')
    @include('partials.modals.tickets.cancel')
    @include('partials.modals.tickets.attach_user')
    @if($ticket->status->is(\App\Enums\TicketStatusEnum::DONE) && $ticket->requiresRating())
        @include('partials.modals.tickets.close_rating')
    @endif
    @if($ticket->status->is(\App\Enums\TicketStatusEnum::OPENED) && $ticket->creator->id === auth()->id())
        {{--@include('partials.modals.tickets.edit')--}}
    @endif
    @if($ticket->status->is(\App\Enums\TicketStatusEnum::OPENED) || $ticket->status->is(\App\Enums\TicketStatusEnum::IN_PROGRESS))
        @include('partials.modals.tickets.update_deadline')
    @endif
    @include('partials.modals.approval_requests.users')
@endpush

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/filepond.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/plugins/tribute.css')}}" rel="stylesheet" type="text/css" />
    <style>
        #comment_filepond .filepond--drop-label, #comment_filepond .filepond--panel-root {
            display: none;
        }
    </style>
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.jquery.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-type.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-size.js')}}"></script>
    <script src="{{asset('assets/js/custom/initFileUpload.js')}}"></script>
    <script src="{{asset('assets/js/plugins/tribute.js')}}"></script>
@endpush

@push('custom_js')
    <style>
        .feed-hide-views .feed-item[data-type="view"] { display: none; }
        .feed-item.feed-filtered { display: none; }
    </style>
    <script>
        $(function () {
            const $feed = $('#chat-messages');
            $('#feed_show_views').on('change', function () {
                $feed.toggleClass('feed-hide-views', !this.checked);
            });
            $('[data-feed-filter]').on('click', function () {
                const f = $(this).data('feed-filter');
                const groups = {comment: ['comment'], status: ['status', 'approval', 'view']};
                $('[data-feed-filter]').removeClass('active');
                $(this).addClass('active');
                $feed.find('.feed-item').each(function () {
                    const t = $(this).data('type');
                    $(this).toggleClass('feed-filtered', f !== 'all' && !groups[f].includes(t));
                });
                $feed[0].scrollTop = $feed[0].scrollHeight;
            });
        });
    </script>

    <script>
        let mentions = {!! $mentions !!};
        let tribute = new Tribute({
            values: mentions
        });

        let selectedUsers = [];
        let tributeInput = document.getElementById('tribute');
        tribute.attach(tributeInput);
        tributeInput.addEventListener('tribute-replaced', (event) => {
            const selectedUser = event.detail.item.original;
            selectedUsers.push({
                id: selectedUser.id,
                marker: `@${selectedUser.value}`
            });
            //console.log('Added:', selectedUsers);
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Проверяем удаление упоминаний при изменении текста
        tributeInput.addEventListener('input', debounce((event) => {
            const currentText = tributeInput.value;
            selectedUsers = selectedUsers.filter(user =>
                currentText.includes(user.marker)
            );
            //console.log('Updated:', selectedUsers);
        }, 300));
    </script>
    <script>
        // Регистрируем плагины FilePond
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

        let commentUploadManager = null;

        $(document).ready(function() {
            // Создаём менеджер один раз, но НЕ инициализируем FilePond сразу
            // (инициализация будет при создании менеджера)
            commentUploadManager = FileUploadManager.create({
                pondSelector: '.comment_attach',
                submitBtnSelector: '#submit-comment-btn',
                formSelector: '#comment-form',
                tempFolderInputSelector: '#comment_temp_folder',

                uploadRoute: '{{ route('cabinet.files.upload') }}',
                deleteRoute: '{{ route('cabinet.files.delete') }}',
                deleteTempFolderRoute: '{{ route('cabinet.files.delete-temp-folder') }}',

                csrfToken: '{{ csrf_token() }}',

                requireFilesForSubmit: false,
                autoGenerateTempFolder: true,
                cleanupOnModalClose: false,

                labelFileTypeNotAllowed: '{{ trans('tickets.create_modal.format_error') }}',
                labelMaxFileSizeExceeded: '{{ trans('tickets.create_modal.size_limit') }}',
                labelIdle: '{{ trans('tickets.create_modal.attachments_hint') }}',

                onFileAdd: (file, uploadedFiles) => {
                    console.log('Файл добавлен к комментарию:', file.filename);
                }
            });

            // По клику на кнопку открываем диалог выбора файлов
            $('.show-filepond').on('click', function(e) {
                e.preventDefault();
                // Триггер клика по input FilePond, чтобы открыть окно выбора файлов
                commentUploadManager.pondInstance.browse();
            });
        });
    </script>

    <script>
        let token = $('meta[name="_token"]').attr('content');

        function scrollToBottom() {
            let chatMessages = document.getElementById('chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        window.onload = scrollToBottom;

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
                        window.location.href = '{{route('cabinet.tickets.show', $ticket->id)}}';
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
                    }
                    removeWait($('body'));
                    Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                },
            });
        });

        // send comment
        $('#send_comment_btn').click(function (e) {
            e.preventDefault();
            let form = $('#send_comment_form');
            let button = $(this);
            applyWait(button);
            $.ajax({
                url: "{{route('cabinet.tickets.comment.store', $ticket)}}",
                method: 'POST',
                data: {
                    _token: '{{csrf_token()}}',
                    text: $('#tribute').val(),
                    mentions: selectedUsers.map(user => user.id),
                    temp_folder: $('#comment_temp_folder').val(),
                },
                success: function (response) {
                    console.log(response)
                    if(response.status === 'success') {
                        let newMessage = $(response.html).hide();
                        $('.empty_activity').remove();
                        $('#chat-messages').append(newMessage);
                        newMessage.fadeIn('slow');
                        scrollToBottom();
                        form.find('textarea').val('');
                        selectedUsers = [];
                    } else {
                        Swal.fire('{{trans('common.swal.error_title')}}', response.error, 'error');
                    }
                },
                error: function (response) {
                    let errorMessage = '';
                    if (response.status === 422) {
                        const errors = response.responseJSON.errors;
                        for (const key in errors) {
                            errorMessage += `<p class="mb-0">${errors[key][0]}</p>`;
                        }
                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    } else if (response.status === 403) {
                        Swal.fire('{{trans('common.swal.error_title')}}', response.responseJSON.message, 'error');
                    } else {
                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    }
                },
                complete: function () {
                    removeWait(button);
                    $('.comment_attach').remove();
                }
            });
        });

        // in progress
        $(document).on('click', '.start-task-btn', function() {
            const button = $(this);
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
                    if (response.success) {
                        $('.ticket_status_label').html(response.html);
                        $('.performer_wrapper').fadeOut(200, function() {
                            $(this).html(response.html2).fadeIn(200);
                        });
                        button.removeClass('btn-light-warning start-task-btn').addClass('btn-light-primary');
                        button.html('<i class="ki-outline ki-timer fs-2"></i> {{trans('tickets.buttons.done_ticket')}}');
                        button.blur();
                        button.prop('disabled', false);
                        button.attr('data-bs-original-title', '{{trans('tickets.buttons.done_ticket')}}');
                        button.attr('data-bs-toggle', 'modal');
                        button.attr('data-bs-target', '#complete_ticket_modal');
                    } else {
                        button.html('<i class="ki-outline ki-timer fs-2"></i>{{trans('tickets.buttons.start_ticket')}}').prop('disabled', false);
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
                    }
                    button.html('<i class="ki-outline ki-timer fs-2"></i>{{trans('tickets.buttons.done_ticket')}}').prop('disabled', false);
                    Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                },
                complete: function () {
                    removeWait($('body'));
                }
            });
        });

        //reject
        $(document).on('click', '.reject_ticket', function() {
            const button = $(this);
            const ticketId = parseInt(button.data('ticket-id'));

            Swal.fire({
                html: `{{trans('common.swal.reject_ticket')}}`,
                icon: "info",
                buttonsStyling: false,
                showCancelButton: true,
                confirmButtonText: "{{trans('common.swal.yes')}}",
                cancelButtonText: '{{trans('common.swal.no')}}',
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: 'btn fw-bold btn-active-light-primary'
                }
            }).then(async (result) => {
                if (result.value) {
                    try {
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
                                if (response.success) {
                                    window.location.href = '{{$backUrl}}';
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
                                }
                                removeWait($('body'));
                                Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                            },
                        });
                    } catch (error) {
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
                    }
                }
            });
        })

        // completed ticket
        let form = $('#complete_ticket_form');
        $('#complete_ticket_submit').click(function (e) {
            e.preventDefault();
            $('#ticket_id').val({{$ticket->id}});
            let button = $(this);
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
                        window.location.href = '{{$backUrl}}';
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

        // cancel ticket
        $('#cancel_ticket_submit').click(function (e) {
            e.preventDefault();
            let button = $(this);
            console.log({{$ticket->id}})
            $('#cancel_ticket_id').val({{$ticket->id}});
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
                        window.location.href = '{{$backUrl}}';
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

        // get ticket performers
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
                    Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.try_again')}}', 'error');
                    modal.find('.user-radio').removeClass('d-none');
                    modal.find('.spinner-border').addClass('d-none');
                }
            });
        });

        // attach users
        $('#attach_user_submit').click(function (e) {
            e.preventDefault();
            let button = $(this);
            let performersSymbols = $('.performers_symbols');
            let form = $('#attach_users_form');
            applyWait($('body'));
            $.ajax({
                url: '{{ route('cabinet.tickets.attach_users') }}',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': token},
                data: form.serialize(),
                success: function (response) {
                    if(response.status === 'success') {
                        window.location.reload();
                        // removeWait($('body'));
                        // $('.ticket_status_label').html(response.ticket_status);
                        // performersSymbols.html(response.html);
                        // $('#attach_users_modal').modal('toggle');
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
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    } else if (response.status === 403) {
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', response.responseJSON.message, 'error');
                    } else {
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    }
                }
            });
        })

        // attach tags
        $('select[name="tags"]').on('select2:close', function(e) {
            e.preventDefault();
            //applyWait($('.select2'));
            applyWait($('body'));
            let selectedValues = $('select[name="tags"]').val();
            let token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '{{ route('cabinet.tickets.attach_tags', $ticket) }}',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': token},
                data: {
                    tags: selectedValues
                },
                success: function (response) {
                    if(response.status === 'success') {
                        removeWait($('body'));
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
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', response.responseJSON.message, 'error');
                    } else {
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    }
                },
                complete: function () {
                    removeWait($('body'));
                }
            });
        });

        @if($ticket->requiresRating())
            // closed
        $('#close_ticket_submit').click(function (e) {
            e.preventDefault();
            let ticket_id = $(this).data('id');
            $('#close_ticket_id').val(ticket_id);
            applyWait($('body'));
            $.ajax({
                url: '{{route('cabinet.tickets.close', ':id')}}'.replace(':id', ticket_id),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: $('#close_ticket_form').serialize(),
                success: function (response) {
                    if(response.success) {
                        removeWait($('body'));
                        window.location.href = '{{$backUrl}}';
                        Swal.fire('{{trans('common.swal.success_title')}}', '{{trans('common.swal.success_text')}}', 'success');
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
        @else
            $(document).on('click', '.closed-ticket-btn', function(e) {
            e.preventDefault();
            Swal.fire({
                html: `{{trans('common.swal.close_ticket')}}`,
                icon: "info",
                buttonsStyling: false,
                showCancelButton: true,
                confirmButtonText: "{{trans('common.swal.yes')}}",
                cancelButtonText: '{{trans('common.swal.no')}}',
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: 'btn fw-bold btn-active-light-primary'
                }
            }).then(async (result) => {
                if (result.value) {
                    try {
                        applyWait($('body'));
                        const url = '{{ route('cabinet.tickets.close', $ticket) }}';
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                            },
                            success: function(response) {
                                if (response.success) {
                                    window.location.href = '{{$backUrl}}';
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
                                } else if(response.status === 403) {
                                    removeWait($('body'));
                                    errorMessage = `<p class="mb-0">${response.responseJSON.message}</p>`;
                                    Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                                }
                                removeWait($('body'));
                                Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                            }
                        });
                    } catch (error) {
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
                    }
                }
            });
        });
        @endif



        // create approve request
        $(document).ready(function() {

            $('#approved_users_form').on('submit', function(e) {
                e.preventDefault();
                let $form = $(this);
                let url = $form.attr('action');
                let data = $form.serialize();
                applyWait($('body'));
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr) {
                        removeWait($('body'));
                        let errorMessage = '{{trans('common.swal.error_text')}}';
                        // проверяем ошибки валидации
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = '';
                            for (const key in errors) {
                                errorMessage += `<p class="mb-0">${errors[key][0]}</p>`;
                            }
                        }
                        // другие ошибки
                        else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            errorMessage = xhr.responseText;
                        }

                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    }
                });
            });

        });



    </script>

    @if(!$ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED)
        && !$ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED))
        @if(auth()->user()->is_approver)
            <script>
                $(document).ready(function() {

                    function handleApproval(url, token, id) {
                        applyWait($('body'));
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                token: token
                            },
                            success: function(response) {
                                // removeWait($('body'));
                                location.reload();
                                // $('.approve[data-id="' + id + '"], .deny[data-id="' + id + '"]').hide();
                                // $('#status-' + id).html(response.badgeHtml);
                            },
                            error: function(response) {
                                removeWait($('body'));
                                let errorMessage = '';
                                if (response.status === 422 && response.responseJSON.errors) {
                                    const errors = response.responseJSON.errors;
                                    for (const key in errors) {
                                        errorMessage += `<p class="mb-0">${errors[key][0]}</p>`;
                                    }
                                } else if (response.responseText) {
                                    errorMessage = response.responseText;
                                } else {
                                    errorMessage = 'Произошла ошибка';
                                }

                                Swal.fire('Произошла ошибка!', errorMessage, 'error');
                            }
                        });
                    }

                    $(document).on('click', '.approve', function() {
                        let id = $(this).data('id');
                        let token = $(this).data('token');
                        handleApproval('{{ route("approval.approve.ajax", ":token") }}'.replace(':token', token), token, id);
                    });

                    $(document).on('click', '.deny', function() {
                        let id = $(this).data('id');
                        let token = $(this).data('token');
                        handleApproval('{{ route("approval.deny.ajax", ":token") }}'.replace(':token', token), token, id);
                    });

                });
            </script>
        @endif
    @endif

    @stack('modal_js')
@endpush
