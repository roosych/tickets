@extends('layouts.app')

@section('title', 'Исходящие тикеты отдела')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">{{trans('common.mainpage')}}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">Исходящие тикеты отдела</li>
    </ul>
@endsection

@section('content')
    @php
        $activeSt = [\App\Enums\TicketStatusEnum::OPENED, \App\Enums\TicketStatusEnum::IN_PROGRESS];
        $isOverdue = fn ($t) => in_array($t->status, $activeSt) && $t->due_date && $t->due_date->isPast();
        $chips = [
            'all' => [trans('tickets.table.chip_all'), $tickets->count()],
            'new' => [trans('tickets.table.chip_new'), $tickets->where('status', \App\Enums\TicketStatusEnum::OPENED)->count()],
            'in_progress' => [trans('tickets.table.chip_in_progress'), $tickets->where('status', \App\Enums\TicketStatusEnum::IN_PROGRESS)->count()],
            'overdue' => [trans('tickets.table.chip_overdue'), $tickets->filter($isOverdue)->count()],
            'unassigned' => [trans('tickets.table.chip_unassigned'), $tickets->filter(fn ($t) => !$t->executor_id && in_array($t->status, $activeSt))->count()],
            'done' => [trans('tickets.table.chip_await_close'), $tickets->where('status', \App\Enums\TicketStatusEnum::DONE)->count()],
        ];
        $creators = $tickets->pluck('creator')->filter()->unique('id')->sortBy('name');
        $performers = $tickets->pluck('performer')->filter()->unique('id')->sortBy('name');
        $targetDepts = $tickets->pluck('department')->filter()->unique('id')->sortBy('name');
    @endphp
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                    <input type="text"
                           data-dept-tickets-filter="search"
                           class="form-control form-control-solid w-250px ps-12" placeholder="{{trans('tickets.table.search')}}" />
                </div>
            </div>
            <div class="card-toolbar flex-row-fluid justify-content-end flex-wrap gap-3">
                @if(count($tickets))
                    @foreach([
                        ['creator', 'all_creators', $creators, 'mw-200px'],
                        ['dept', 'all_depts', $targetDepts, 'mw-200px'],
                        ['performer', 'all_performers', $performers, 'mw-200px'],
                    ] as [$key, $placeholder, $list, $width])
                        <div class="w-100 {{$width}}">
                            <select class="form-select form-select-solid" data-control="select2"
                                    data-placeholder="{{trans('tickets.table.'.$placeholder)}}" data-dept-tickets-filter="{{$key}}">
                                <option></option>
                                <option value="all">{{trans('tickets.table.'.$placeholder)}}</option>
                                @foreach($list as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <div class="position-relative">
                        <i class="ki-outline ki-calendar fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" id="dept_tickets_period" class="form-control form-control-solid ps-12 w-200px"
                               placeholder="{{trans('tickets.table.period')}}" autocomplete="off" readonly>
                    </div>
                    <label class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" data-dept-tickets-filter="mine" data-user-id="{{auth()->id()}}">
                        <span class="form-check-label fw-semibold text-gray-700">{{trans('tickets.table.only_mine')}}</span>
                    </label>
                    <div class="w-100 mw-200px">
                        <select class="form-select form-select-solid" data-control="select2" data-hide-search="true"
                                data-placeholder="{{trans('tickets.table.priority')}}" data-kt-ecommerce-priority-filter="priority">
                            <option></option>
                            <option value="all">{{trans('tickets.table.all_priorities')}}</option>
                            @foreach($priorities as $item)
                                <option value="{{$item->getNameByLocale()}}">{{$item->getNameByLocale()}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-100 mw-150px">
                        <select class="form-select form-select-solid" data-control="select2" data-hide-search="true"
                                data-placeholder="{{ trans('tickets.table.all_statuses') }}" data-dept-tickets-filter="status"
                                data-initial="{{ $statusLabels[request('filter.status')] ?? '' }}">
                            <option></option>
                            <option value="all">{{ trans('tickets.table.all_statuses') }}</option>
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $label }}" {{ request('filter.status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body pt-0">
            @if(count($tickets))
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-5" id="dept_tickets_chips">
                    @foreach($chips as $key => [$label, $count])
                        <li class="nav-item">
                            <a href="#" data-chip="{{$key}}" class="nav-link text-active-primary d-flex align-items-center pb-5 {{$loop->first ? 'active' : ''}}">
                                {{$label}} (<span class="{{ $key === 'overdue' && $count > 0 ? 'text-danger' : 'text-gray-800' }}">{{$count}}</span>)
                            </a>
                        </li>
                    @endforeach
                </ul>
                <table class="table align-middle table-hover table-row-dashed fs-6 gy-5" id="dept_tickets_table">
                    <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="">{{trans('tickets.table.ticket')}}</th>
                        <th class="min-w-125px">{{trans('tickets.table.creator')}}</th>
                        <th>{{trans('tickets.table.performer')}}</th>
                        <th class="min-w-125px">{{trans('tickets.table.priority')}}</th>
                        <th class="min-w-125px">{{trans('tickets.table.created_at')}}</th>
                        <th class="min-w-150px">{{trans('tickets.table.deadline')}}</th>
                        <th>{{trans('tickets.table.status')}}</th>
                        <th class="min-w-250px">{{trans('tickets.create_modal.description')}}</th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    @foreach($tickets as $ticket)
                        @php
                            $overdue = $isOverdue($ticket);
                            $isActive = in_array($ticket->status, $activeSt);
                        @endphp
                        <tr class="position_row_{{$ticket->id}} clickable-row cursor-pointer"
                            data-href="{{ route('cabinet.tickets.show', $ticket) }}"
                            data-status="{{$ticket->status->value}}"
                            data-overdue="{{$overdue ? 1 : 0}}"
                            data-unassigned="{{ !$ticket->executor_id && $isActive ? 1 : 0 }}"
                            data-executor="{{$ticket->executor_id}}"
                            data-creator="{{$ticket->user_id}}"
                            data-dept="{{$ticket->department_id}}"
                            data-created="{{$ticket->created_at->timestamp}}">
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold">#{{$ticket->id}}</a>
                                    @if($ticket->children_count > 0)
                                        <div class="ms-2" data-bs-toggle="tooltip" aria-label="{{trans('tickets.has_children')}}" data-bs-original-title="{{trans('tickets.has_children')}}">
                                            <i class="ki-outline ki-note-2 fs-2"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="d-flex align-items-center border-bottom-1">
                                <div class="symbol symbol-circle symbol-35px overflow-hidden me-3">
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
                                    <a href="{{route('cabinet.users.show', $ticket->creator)}}" target="_blank" class="text-gray-800 text-hover-primary" style="width: fit-content;">
                                        {{$ticket->creator->name}}
                                    </a>
                                    <span class="fs-8 text-muted">{{$ticket->creator->email}}</span>
                                </div>
                            </td>
                            <td data-order="{{ $ticket->performer?->name ?? $ticket->department?->name }}">
                                @if($ticket->performer)
                                    <div class="text-gray-800">{{$ticket->performer->name}}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                                <div class="fs-8 text-muted">{{$ticket->department?->name}}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{$ticket->priority->class}} fw-bold fs-7">
                                    {{$ticket->priority->getNameByLocale()}}
                                </span>
                            </td>
                            <td data-order="{{$ticket->created_at->timestamp}}">
                                {{$ticket->created_at->isoFormat('D MMMM YYYY, HH:mm')}}
                            </td>
                            <td data-order="{{ $isActive && $ticket->due_date ? $ticket->due_date->timestamp : PHP_INT_MAX }}">
                                @if($ticket->due_date)
                                    {{$ticket->due_date->isoFormat('D MMM YYYY, HH:mm')}}
                                    @if($isActive)
                                        <div class="fs-7 {{ $overdue ? 'text-danger fw-bold' : 'text-muted' }}">{{$ticket->timeUntilDueFull()}}</div>
                                    @endif
                                @else
                                    {{trans('tickets.table.no_deadline')}}
                                @endif
                            </td>
                            <td id="ticket_status_{{$ticket->id}}">
                                <x-ticket-status-badge :status="$ticket->status->label()" :color="$ticket->status->color()"></x-ticket-status-badge>
                            </td>
                            <td>
                                <span class="cursor-help"
                                      data-bs-toggle="tooltip"
                                      data-bs-html="true"
                                      data-bs-original-title="<p class='fs-6 fw-bold mb-0'>{{$ticket->text}}</p>"
                                >{{Str::limit(strip_tags($ticket->text), 70)}}</span>
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
                            <h4 class="text-gray-900 fw-bold mb-0">{{trans('tickets.table.empty')}}</h4>
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

@endpush

@push('custom_js')
    <script>
        window.deptTicketsI18n = {
            today: "{{trans('common.reports.period.today')}}",
            yesterday: "{{trans('common.reports.period.yesterday')}}",
            days7: "{{trans('common.reports.period.7_days')}}",
            days30: "{{trans('common.reports.period.30_days')}}",
            month: "{{trans('common.reports.period.this_month')}}",
            custom: "{{trans('common.reports.period.custom_date')}}",
            apply: "{{trans('common.reports.apply_btn')}}",
            reset: "{{trans('common.roles.buttons.cancel')}}",
        };
    </script>
    <script src="{{asset('assets/js/custom/tickets/dept_sent_tickets.js?v=2')}}"></script>
    <script>
        $(document).ready(function () {
            $('#dept_tickets_table').on('click', '.clickable-row', function (e) {
                // Не реагировать на клик по интерактивным элементам
                if ($(e.target).closest('a, button, .btn, .dropdown, [data-bs-toggle]').length) return;
                window.location.href = $(this).data('href');
            });
        });
    </script>
@endpush
