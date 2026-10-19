                <div class="card-header align-items-center flex-wrap py-5 gap-3">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <a href="{{$backUrl}}" class="btn btn-sm btn-icon btn-clear btn-active-light-primary"
                           data-bs-toggle="tooltip" data-bs-placement="top" title="{{trans('common.back')}}">
                            <i class="ki-outline ki-arrow-left fs-1 m-0"></i>
                        </a>
                        <h3 class="fw-bold mb-0">#{{$ticket->id}}</h3>
                        <div class="ticket_status_label">
                            <x-ticket-status-badge :status="$ticket->status->label()" :color="$ticket->status->color()"></x-ticket-status-badge>
                        </div>
                        <span class="badge badge-light-{{$ticket->priority->class}} fw-bold fs-7" data-bs-toggle="tooltip" title="{{trans('tickets.table.priority')}}">
                            {{$ticket->priority->getNameByLocale()}}
                        </span>
                    </div>

                    <div class="d-flex align-items-center ms-auto flex-wrap gap-2">
                        <a href="#kt_chat_messenger" class="btn btn-sm btn-light d-xxl-none" data-bs-toggle="tooltip" title="{{trans('tickets.activity')}}">
                            <i class="ki-outline ki-message-text-2 fs-2"></i>
                        </a>
                        @if($canClose)
                            <button class="btn btn-sm btn-light-success btn-active-success closed-ticket-btn"
                                    data-ticket_id="{{$ticket->id}}"
                                    @if(auth()->id() === $ticket->creator->id)
                                        data-bs-toggle="modal"
                                        data-bs-target="#close_ticket_modal"
                                    @else
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="{{trans('tickets.buttons.close_ticket')}}"
                                    @endif
                            >
                                <i class="ki-outline ki-check-square fs-2"></i>
                                {{trans('tickets.buttons.close_ticket')}}
                            </button>
                        @endif

                        @if($canStart)
                            <button class="btn btn-sm btn-light-warning btn-active-warning start-task-btn"
                                    data-ticket_id="{{$ticket->id}}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ trans('tickets.buttons.start_ticket') }}">
                                <i class="ki-outline ki-timer fs-2"></i>
                                {{ trans('tickets.buttons.start_ticket') }}
                            </button>
                        @endif

                        @if($canComplete)
                            <button class="btn btn-sm btn-light-primary btn-active-primary"
                                    data-ticket_id="{{$ticket->id}}"
                                    data-id="{{$ticket->id}}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#complete_ticket_modal">
                                <i class="ki-outline ki-timer fs-2"></i>
                                {{trans('tickets.buttons.done_ticket')}}
                            </button>
                        @endif

                        @if($hasMenu)
                            <button type="button" class="btn btn-sm btn-icon btn-light" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="ki-outline ki-dots-horizontal fs-2"></i>
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-700 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                                @if($canReopen)
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0);" class="menu-link px-3 reject_ticket" data-ticket-id="{{$ticket->id}}">
                                            <i class="ki-outline ki-arrow-circle-left fs-3 me-2"></i>{{trans('tickets.buttons.reopen')}}
                                        </a>
                                    </div>
                                @endif
                                @if($canCancel)
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0);" class="menu-link px-3 text-danger cancel-ticket-btn"
                                           data-ticket_id="{{$ticket->id}}"
                                           data-id="{{$ticket->id}}"
                                           data-bs-toggle="modal"
                                           data-bs-target="#cancel_ticket_modal">
                                            <i class="ki-outline ki-cross-square fs-3 me-2 text-danger"></i>{{trans('tickets.buttons.cancel_ticket')}}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
