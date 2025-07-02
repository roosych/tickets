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
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                <div class="w-100 mw-250px">
                    <select class="form-select form-select-solid"
                            data-control="select2"
                            data-hide-search="true" data-placeholder="{{trans('tickets.table.priority')}}" data-kt-ecommerce-priority-filter="priority">
                        <option></option>
                        <option value="all">{{trans('tickets.table.all_priorities')}}</option>
                        @foreach($priorities as $item)
                            <option value="{{$item->getNameByLocale()}}">{{$item->getNameByLocale()}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-100 mw-150px">
                    <select id="statusFilter" class="form-select form-select-solid"
                            data-control="select2"
                            data-hide-search="true"
                            data-placeholder="{{ trans('tickets.table.all_statuses') }}">
                        <option></option>
                        <option value="all">{{ trans('tickets.table.all_statuses') }}</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}"
                                {{ request('filter.status') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            @if(count($tickets))
                <table class="table align-middle table-hover table-row-dashed fs-6 gy-5" id="dept_tickets_table">
                    <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="">{{trans('tickets.table.ticket')}}</th>
                        <th class="min-w-125px">{{trans('tickets.table.creator')}}</th>
                        <th>Исполнитель</th>
                        <th class="min-w-125px">{{trans('tickets.table.priority')}}</th>
                        <th class="min-w-125px">{{trans('tickets.table.created_at')}}</th>
                        <th>{{trans('tickets.table.status')}}</th>
                        <th>{{trans('tickets.create_modal.description')}}</th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    @foreach($tickets as $ticket)
                        <tr class="position_row_{{$ticket->id}} clickable-row cursor-pointer"
                            data-href="{{ route('cabinet.tickets.show', $ticket) }}">
                            <td class="ps-3">
                                <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold">#{{$ticket->id}}</a>
                                @if($ticket->allChildren()->exists())
                                    <div class="ms-2" data-bs-toggle="tooltip" aria-label="{{trans('tickets.has_children')}}" data-bs-original-title="{{trans('tickets.has_children')}}">
                                        <i class="ki-outline ki-note-2 fs-2"></i>
                                    </div>
                                @endif
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
                                    <a href="{{route('cabinet.users.show', $ticket->creator)}}" target="_blank" class="text-gray-800 text-hover-primary mb-1" style="width: fit-content;">
                                        {{$ticket->creator->name}}
                                    </a>
                                    <span>{{$ticket->creator->email}}</span>
                                </div>
                            </td>
                            <td>
                                {{$ticket->department->name}}
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
                                <span class="cursor-help"
                                      data-bs-toggle="tooltip"
                                      data-bs-html="true"
                                      data-bs-original-title="<p class='fs-6 fw-bold mb-0'>{{$ticket->text}}</p>"
                                >{{Str::limit($ticket->text, 20)}}</span>
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
    <script src="{{asset('assets/js/custom/tickets/dept_sent_tickets.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('#statusFilter, #creatorFilter').on('change', function () {
                const status = $('#statusFilter').val();
                const creator = $('#creatorFilter').val();
                const url = new URL(window.location.href);
                if (status === 'all') {
                    url.searchParams.delete('filter[status]');
                } else {
                    url.searchParams.set('filter[status]', status);
                }
                if (creator === 'all') {
                    url.searchParams.delete('filter[creator]');
                } else {
                    url.searchParams.set('filter[creator]', creator);
                }
                window.location.href = url.toString();
            });
        });

        $(document).ready(function () {
            $('.clickable-row').on('click', function (e) {
                // Не реагировать на клик по интерактивным элементам
                if ($(e.target).closest('a, button, .btn, .dropdown, [data-bs-toggle]').length) return;
                window.location.href = $(this).data('href');
            });
        });
    </script>
@endpush
