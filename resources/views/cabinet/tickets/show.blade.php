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
    <div class="row g-xxl-9">
        <div class="col-xxl-8">
            <div class="card">
                <div class="card-header align-items-center py-5 gap-5">
                    <div class="d-flex">
                        <a href="{{$backUrl}}" class="btn btn-sm btn-icon btn-clear btn-active-light-primary me-3"
                           data-bs-toggle="tooltip" data-bs-placement="top" title="{{trans('common.back')}}">
                            <i class="ki-outline ki-arrow-left fs-1 m-0"></i>
                        </a>
                    </div>
                <div>

                    @if(!$ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED) && !$ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED))
                        @can('cancel', $ticket)
                            @if(!$ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED)
                                && (!$ticket->parent || !$ticket->parent->status->is(\App\Enums\TicketStatusEnum::CANCELED)))
                                <button class="btn btn-sm btn-light-danger btn-active-danger me-2 cancel-ticket-btn"
                                        data-ticket_id="{{$ticket->id}}"
                                        data-id="{{$ticket->id}}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cancel_ticket_modal">
                                    <i class="ki-outline ki-cross-square fs-2"></i>
                                    {{trans('tickets.buttons.cancel_ticket')}}
                                </button>
                            @endif
                        @endcan

                            @if($ticket->status->is(\App\Enums\TicketStatusEnum::DONE))
                                @can('close', $ticket)
                                    <button class="btn btn-sm btn-light-success btn-active-success me-2 closed-ticket-btn"
                                            data-ticket_id="{{$ticket->id}}"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="{{trans('tickets.buttons.close_ticket')}}">
                                        <i class="ki-outline ki-check-square fs-2"></i>
                                        {{trans('tickets.buttons.close_ticket')}}
                                    </button>
                                @endcan
                            @endif

                            @if($ticket->status->is(\App\Enums\TicketStatusEnum::OPENED))
                                @if((!$ticket->performer || $ticket->performer->id === auth()->id()) && auth()->user()->getDepartmentId() === $ticket->department->id)
                                    <button class="btn btn-sm btn-light-warning btn-active-warning me-2 start-task-btn"
                                            data-ticket_id="{{$ticket->id}}"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="{{ trans('tickets.buttons.start_ticket') }}">
                                        <i class="ki-outline ki-timer fs-2"></i>
                                        {{ trans('tickets.buttons.start_ticket') }}
                                    </button>
                                @endif
                            @endif
                        @if($ticket->status->is(\App\Enums\TicketStatusEnum::IN_PROGRESS)
                            && $ticket->performer
                            && $ticket->performer->id === auth()->id())
                            <button class="btn btn-sm btn-light-primary btn-active-primary me-2"
                                    data-ticket_id="{{$ticket->id}}"
                                    data-id="{{$ticket->id}}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#complete_ticket_modal">
                                <i class="ki-outline ki-timer fs-2"></i>
                                {{trans('tickets.buttons.done_ticket')}}
                            </button>
                        @endif
                    @endif
                </div>

                </div>
                <div class="card-body">
                    <div class="mb-5">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="ki-outline ki-calendar fs-2 me-2"></i>
                                <span class="fw-semibold text-muted text-end me-3">
                                {{ \Carbon\Carbon::parse($ticket->created_at)->isoFormat('D MMMM, HH:mm') }}
                            </span>
                            </div>

                            <div class="d-flex">
                                <span class="text-gray-800 fw-bold fs-12 me-2">
                                    {{trans('tickets.table.status')}}:
                                </span>
                                <div class="ticket_status_label">
                                    <x-ticket-status-badge :status="$ticket->status->label()" :color="$ticket->status->color()"></x-ticket-status-badge>
                                </div>

                                    @if($ticket->status->is(\App\Enums\TicketStatusEnum::DONE))
                                        @can('close', $ticket)
                                            <a href="javascript:void(0);" class="reject_ticket" data-ticket-id="{{$ticket->id}}">
                                                <i class="ki-outline ki-arrow-circle-left text-gray-800 fs-2 ms-2"></i>
                                            </a>
                                        @endcan
                                    @endif
                            </div>
                        </div>
                    </div>

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

                        <div class="badge badge-lg badge-light-dark mb-4">
                            <div class="d-flex align-items-center flex-wrap">
                                @if($ticket->parent)
                                    Тикет: <a href="{{route('cabinet.tickets.show', $ticket->parent->id)}}" class="text-dark text-hover-primary ms-1" target="_blank">
                                        #{{$ticket->parent_id}}
                                    </a>
                                @else
                                    {{$ticket->creator->department}}
                                    <i class="ki-outline ki-right fs-2 text-dark mx-1"></i>
                                    {{$ticket->department->name}}
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 flex-stack">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
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

                                <div class="pe-5">
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        <a href="{{route('cabinet.users.show', $ticket->creator)}}" class="fw-bold text-gray-900 text-hover-primary" target="_blank">
                                            {{$ticket->creator->name}}
                                        </a>
                                    </div>
                                    <div>
                                        <span class="text-muted fw-semibold">
                                            {{$ticket->creator->email}}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <div class="d-flex align-items-center">
                                    <div class="text-gray-800 fw-bold fs-12">
                                        {{trans('tickets.table.priority')}}:
                                    </div>
                                    <span class="badge badge-light-{{$ticket->priority->class}} ms-2 my-1 fw-bold fs-7">
                                        {{$ticket->priority->getNameByLocale()}}
                                    </span>
                                </div>
                            </div>

                            <div class="my-5">
                                <div class="performers_symbols symbol-group symbol-hover flex-nowrap">
                                    <div class="text-gray-800 fw-bold fs-12 me-5">
                                        {{trans('tickets.responsible')}}:
                                    </div>
                                    <x-ticket-performer :user="$ticket->performer" :ticket="$ticket"></x-ticket-performer>
                                </div>
                            </div>

                        </div>

                        <div class="py-5 mt-2">
                            <p>{{$ticket->text}}</p>
                        </div>

                        @if($ticket->media->isNotEmpty())
                            <div class="my-5 pb-5">
                                @foreach($ticket->media as $item)
                                    <div class="d-flex flex-aligns-center pe-10 pe-lg-20 mb-3">
                                        <img alt="" class="w-40px me-3" src="{{asset('assets/media/extensions/'.$item->extension.'.png')}}">
                                        <div class="ms-1 fw-semibold">
                                            <a href="{{asset('storage/uploads/tickets/'.$ticket->id.'/'.$item->filename)}}" class="fs-6 text-hover-primary fw-bold" target="_blank">
                                                {{$item->filename}}
                                            </a>
                                            <div class="text-gray-500">
                                                {{bytes_to_mb($item->size)}}
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
                </div>
            </div>
            @if($ticket->department->id === auth()->user()->getDepartmentId())
                @if(!$ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED)
                    && !$ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED))
                    <div class="d-flex flex-wrap flex-stack my-6">
                        <h3 class="fw-bold my-2">
                            {{$ticket->allChildren->isNotEmpty() ? trans('tickets.children_title') : trans('tickets.children_title_empty')}}
                        </h3>

                        <div class="d-flex align-items-center my-2">
                            <button class="btn btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_new_ticket">
                                <i class="ki-outline ki-plus-square fs-2"></i>
                                {{trans('tickets.buttons.create')}}
                            </button>
                        </div>
                    </div>
                @endif
                <div class="row my-6">
                    @foreach($ticket->allChildren as $item)
                        <div class="col-lg-6">
                            <div class="card mb-6 mb-xl-9">
                                <div class="card-body">
                                    <div class="d-flex flex-stack mb-3">
                                        <div class="badge badge-light-{{$item->status->color()}}">
                                            {{$item->status->label()}}
                                        </div>
                                        <div>
                                        <span class="text-gray-800 fw-bold fs-12">
                                            {{trans('tickets.table.priority')}}:
                                        </span>
                                            <span class="badge badge-light-{{$item->priority->class}} ms-2 my-1 fw-bold fs-7">
                                            {{$item->priority->getNameByLocale()}}
                                    </span>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <a href="{{route('cabinet.tickets.show', $item->id)}}" class="fs-4 fw-bold mb-1 text-gray-900 text-hover-primary" target="_blank">
                                            #{{$item->parent->id}}<i class="ki-duotone ki-right fs-6"></i>{{$item->id}}
                                        </a>
                                    </div>

                                    <div class="fs-6 fw-semibold text-gray-800 mb-5">
                                        {{$item->text}}
                                    </div>

                                    <div class="d-flex flex-stack flex-wrap">
                                        @if($item->performer)
                                            <div class="symbol-group symbol-hover flex-nowrap">
                                                <div class="symbol symbol-35px symbol-circle"
                                                     data-bs-toggle="tooltip"
                                                     aria-label="{{$item->performer->name}}"
                                                     data-bs-original-title="{{$item->performer->name}}">
                                                    <img alt="avatar" src="{{$item->performer->avatar}}">
                                                </div>
                                            </div>
                                        @else
                                            <div>
                                                <a href="#" class="symbol symbol-35px symbol-circle border border-gray-300 border-dashed"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#kt_modal_view_users">
                                                    <span class="symbol-label bg-light text-gray-400 fs-8 fw-bold" style="width: 34px;height: 34px">
                                                        +
                                                    </span>
                                                </a>
                                            </div>

                                        @endif

                                        <a href="{{route('cabinet.tickets.show', $item->id)}}" class="d-flex align-items-center text-primary opacity-75-hover fs-6 fw-semibold" target="_blank">
                                            {{strtolower(trans('tickets.table.more'))}}
                                            <i class="ki-outline ki-exit-right-corner fs-4 ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-xxl-4">
            <div class="card" id="kt_chat_messenger">
                <div class="card-header" id="kt_chat_messenger_header">
                    <div class="card-title">
                        {{trans('tickets.activity')}}
                    </div>
                </div>

                <div class="card-body" id="kt_chat_messenger_body">
                    <div class="scroll-y me-n5 pe-5 h-300px h-lg-auto" id="chat-messages" data-kt-element="messages" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_header, #kt_app_header, #kt_app_toolbar, #kt_toolbar, #kt_footer, #kt_app_footer, #kt_chat_messenger_header, #kt_chat_messenger_footer" data-kt-scroll-wrappers="#kt_content, #kt_app_content, #kt_chat_messenger_body" data-kt-scroll-offset="5px">
                        @forelse($activities as $activity)
                            @if($activity instanceof \App\Models\Comment)
                                <x-ticket-comment :comment="$activity"></x-ticket-comment>
                            @elseif($activity instanceof \App\Models\TicketHistory)
                                <div class="d-flex align-items-center justify-content-start mt-1 fs-6 mb-5">
                                    @if($activity->action->is(\App\Enums\TicketActionEnum::ASSIGN_USER))
                                        <div class="text-muted me-2 fs-7">{{ $activity->action->label() }}
                                            <strong class="text-gray-800 me-1">
                                                {{$activity->assignUser->name}}
                                            </strong>
                                            {{ $activity->created_at->isoFormat('D MMM, HH:mm') }}
                                        </div>
                                    @else
                                        <div class="text-muted me-2 fs-7">{{ $activity->action->label() }}
                                            <span class="badge badge-light-{{$activity->status->color()}}">{{ $activity->status->label() }}</span>
                                            {{ $activity->created_at->isoFormat('D MMM, HH:mm') }}
                                        </div>
                                    @endif


                                    <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip" data-bs-boundary="window" data-bs-placement="top" aria-label="{{ $activity->user->name }}" data-bs-original-title="{{ $activity->user->name }}">
                                        <img src="{{ $activity->user->avatar }}" alt="img">
                                    </div>
                                </div>

                                @if($activity->status === \App\Enums\TicketStatusEnum::DONE)
                                    <div class="p-5 mb-10 rounded bg-light-primary text-gray-900 fw-semibold mw-lg-400px text-start">
                                        {{$ticket->getDoneTicketComment()}}
                                    </div>
                                @endif

                                @if($activity->status === \App\Enums\TicketStatusEnum::CANCELED)
                                    <div class="p-5 mb-10 rounded bg-light-danger text-gray-900 fw-semibold mw-lg-400px text-start">
                                        {{$ticket->getCanceledTicketComment()}}
                                    </div>
                                @endif
                            @endif
                        @empty
                            <div class="text-center empty_activity">
                                <img src="{{asset('assets/media/misc/13.png')}}" class="w-200px" alt="">
                            </div>
                        @endforelse
                    </div>
                </div>

                @if(!$ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED)
                    && !$ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED))
                    <form method="POST" id="send_comment_form">
                        @csrf
                        <div class="card-footer pt-4" id="kt_chat_messenger_footer">
                            <textarea id="tribute" class="form-control form-control-flush mb-3 bg-light rounded" rows="3" name="text" placeholder="{{trans('tickets.send_comment')}}" style="resize: none; width: 100%;"></textarea>
                            <div class="fs-7 text-muted">
                                 {{trans('tickets.mention_hint')}}
                            </div>

                            <div id="comment_filepond">
                                <input type="file" class="comment_attach" name="media" multiple />
                            </div>

                            <div class="d-flex flex-stack">
                                <div class="d-flex align-items-center me-2">
                                    <button class="btn btn-sm btn-icon btn-active-light-primary me-1 show-filepond" type="button">
                                        <i class="ki-outline ki-paper-clip fs-3"></i>
                                    </button>
                                </div>
                                <button id="send_comment_btn"
                                        class="btn btn-primary"
                                        type="submit">
                                    <i class="ki-outline ki-send fs-2"></i>
                                    {{trans('tickets.buttons.send')}}
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
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
    <script src="{{asset('assets/js/plugins/filepond.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.jquery.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-type.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-size.js')}}"></script>
    <script src="{{asset('assets/js/plugins/tribute.js')}}"></script>
@endpush

@push('custom_js')
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
        //filepond
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        FilePond.registerPlugin(FilePondPluginFileValidateSize);


        let pond;
        pond = FilePond.create(document.querySelector('.comment_attach'), {
            server: {
                process: {
                    url: '{{ route('cabinet.files.upload') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    onload: (response) => {
                        let responseJson = JSON.parse(response);
                        console.log(response)
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
                        // Обработка ошибки
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
            labelFileTypeNotAllowed: '{{trans('tickets.create_modal.format_error')}}',
            maxFileSize: '3MB',
            labelMaxFileSizeExceeded: '{{trans('tickets.create_modal.size_limit')}}',
        });

        $('.show-filepond').on('click', function(e) {
            e.preventDefault();
            pond.browse();
        });


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
                        // Обработка ошибки
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
            labelFileTypeNotAllowed: '{{trans('tickets.create_modal.format_error')}}',
            maxFileSize: '5MB',
            labelMaxFileSizeExceeded: '{{trans('tickets.create_modal.size_limit')}}',
            labelIdle: '{{trans('tickets.create_modal.attachments_hint')}}'
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
                    mentions: selectedUsers.map(user => user.id)
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
                    console.log(response.html)
                    if (response.success) {
                        $('.ticket_status_label').html(response.html);
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
                                    window.location.reload()
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
                        window.location.href = '{{route('cabinet.tickets.index')}}';
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

        // closed
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
                                    window.location.reload();
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
    </script>
@endpush
