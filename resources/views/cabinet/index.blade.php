@extends('layouts.app')
@section('content')
    <h1 class="page-heading d-flex align-items-center text-gray-900 fw-bold fs-3 mb-8">
        <span class="fs-1 me-2">👋</span> {{trans('common.index.greeting')}} {{auth()->user()->name}}!
    </h1>
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-xl-8">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card card-flush mb-5 mb-xl-10">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-row-fluid flex-stack">
                                <div class="d-flex flex-column">
                                    <p class="mb-2 fw-bold text-gray-800 fs-3">
                                        {{trans('common.index.done_tickets')}}
                                    </p>
                                    <span class="fs-6 text-gray-500 fs-semibase">
                                {{trans('common.index.all_time')}}
                            </span>
                                </div>
                                <span class="text-gray-800 fw-bold fs-2x">
                            {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::COMPLETED) }}
                        </span>
                            </div>
                        </div>

                        <div class="card-body d-flex align-items-end flex-row-fluid p-0">
                            <div class="card-rounded-bottom w-100" id="kt_charts_widget_44"
                                 data-kt-chart-color=success style="height: 119px"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card card-flush mb-xl-10">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                            {{ number_format($totalTickets, 0, '.', ',') }}
                        </span>
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">
                            {{trans('common.index.dept_tickets')}}
                        </span>
                            </div>
                        </div>
                        @if(count($topPerformers))
                            <div class="card-body d-flex flex-column justify-content-end pe-0">
                    <span class="fs-6 fw-bolder text-gray-800 d-block mb-2">
                        {{trans('common.index.top_3')}}
                    </span>
                                <div class="symbol-group symbol-hover flex-nowrap">
                                    @foreach($topPerformers as $user)
                                        <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="{{$user->name}}">
                                            <img alt="{{$user->name}}" src="{{$user->avatar}}" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card card-flush mb-5 mb-xl-10">
                        <div class="card-header flex-nowrap pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">
                                    {{trans('common.tickets.title')}}
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pb-4 pt-0">
                            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-5">
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary d-flex align-items-center pb-5 active"
                                       data-bs-toggle="tab" href="#my_opened">
                                        {{trans('tickets.my_open')}} (<span class="text-gray-800">{{count($openedTickets)}}</span>)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary d-flex align-items-center pb-5"
                                       data-bs-toggle="tab" href="#my_done">
                                        {{trans('tickets.sent.done')}} (<span class="text-gray-800">{{count($myDoneTickets)}}</span>)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary d-flex align-items-center pb-5"
                                       data-bs-toggle="tab" href="#my_out">
                                        {{trans('sidebar.tickets.sent.text')}} (<span class="text-gray-800">{{count($sentTickets)}}</span>)
                                    </a>
                                </li>

                                @if(auth()->user()->hasPermissions('close', \App\Models\Ticket::class))
                                    <li class="nav-item">
                                        <a class="nav-link text-active-primary d-flex align-items-center pb-5"
                                           data-bs-toggle="tab" href="#wait_closed">
                                            {{trans('tickets.wait_close')}} (<span class="text-gray-800">{{count($doneTickets)}}</span>)
                                        </a>
                                    </li>
                                @endif

                            </ul>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="my_opened" role="tabpanel">
                                    @if(count($openedTickets))
                                        <table class="table align-middle table-hover table-row-dashed fs-6 gy-5" id="my_open_tickets_table">
                                            <thead>
                                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                                <th class="">{{trans('tickets.table.ticket')}}</th>
                                                <th class="min-w-125px">{{trans('tickets.table.creator')}}</th>
                                                <th class="min-w-125px">{{trans('tickets.table.priority')}}</th>
                                                <th class="min-w-125px">{{trans('tickets.table.created_at')}}</th>
                                                <th>{{trans('tickets.table.main_ticket')}}</th>
                                                <th class="text-end min-w-100px"></th>
                                            </tr>
                                            </thead>
                                            <tbody class="fw-semibold text-gray-600">
                                            @foreach($openedTickets as $ticket)
                                                <tr class="position_row_{{$ticket->id}}">
                                                    <td class="ps-3">
                                                        <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold">#{{$ticket->id}}</a>
                                                        @if($ticket->allChildren()->exists())
                                                            <div class="ms-2" data-bs-toggle="tooltip" aria-label="{{trans('tickets.has_children')}}" data-bs-original-title="{{trans('tickets.has_children')}}">
                                                                <i class="ki-outline ki-note-2 fs-2"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="d-flex align-items-center border-bottom-0">
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
                                                        {{\Carbon\Carbon::parse($ticket->created_at)->isoFormat('D MMM, HH:mm')}}
                                                    </td>
                                                    <td>
                                                        @if($ticket->parent)
                                                            <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold" target="_blank">#{{$ticket->id}}</a>
                                                        @else
                                                            {{trans('tickets.table.no_parent')}}
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-2">
                                                        <div class="my-3 ms-9">
                                                            <a href="{{route('cabinet.tickets.show', $ticket)}}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                                <span data-bs-toggle="tooltip"
                                                                      data-bs-trigger="hover"
                                                                      aria-label="{{trans('tickets.table.more')}}"
                                                                      data-bs-original-title="{{trans('tickets.table.more')}}">
                                                                    <i class="ki-outline ki-black-right fs-2 text-gray-500"></i>
                                                                </span>
                                                            </a>
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
                                                    <h4 class="text-gray-900 fw-bold mb-0">{{trans('tickets.table.empty')}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="tab-pane fade" id="my_done" role="tabpanel">
                                    @if(count($myDoneTickets))
                                        <table class="table align-middle table-hover table-row-dashed fs-6 gy-5" id="my_done_tickets_table">
                                            <thead>
                                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                                <th class="">{{trans('tickets.table.ticket')}}</th>
                                                <th class="min-w-125px">{{trans('tickets.table.creator')}}</th>
                                                <th class="min-w-125px">{{trans('tickets.table.priority')}}</th>
                                                <th class="min-w-125px">{{trans('tickets.table.created_at')}}</th>
                                                <th>{{trans('tickets.table.main_ticket')}}</th>
                                                <th class="text-end min-w-100px"></th>
                                            </tr>
                                            </thead>
                                            <tbody class="fw-semibold text-gray-600">
                                            @foreach($myDoneTickets as $ticket)
                                                <tr class="position_row_{{$ticket->id}}">
                                                    <td class="ps-3">
                                                        <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold">#{{$ticket->id}}</a>
                                                        @if($ticket->allChildren()->exists())
                                                            <div class="ms-2" data-bs-toggle="tooltip" aria-label="{{trans('tickets.has_children')}}" data-bs-original-title="{{trans('tickets.has_children')}}">
                                                                <i class="ki-outline ki-note-2 fs-2"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="d-flex align-items-center border-bottom-0">
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
                                                        {{\Carbon\Carbon::parse($ticket->created_at)->isoFormat('D MMM, HH:mm')}}
                                                    </td>
                                                    <td>
                                                        @if($ticket->parent)
                                                            <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold" target="_blank">#{{$ticket->id}}</a>
                                                        @else
                                                            {{trans('tickets.table.no_parent')}}
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-2">
                                                        <div class="my-3 ms-9">
                                                            <a href="{{route('cabinet.tickets.show', $ticket)}}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                                <span data-bs-toggle="tooltip"
                                                                      data-bs-trigger="hover"
                                                                      aria-label="{{trans('tickets.table.more')}}"
                                                                      data-bs-original-title="{{trans('tickets.table.more')}}">
                                                                    <i class="ki-outline ki-black-right fs-2 text-gray-500"></i>
                                                                </span>
                                                            </a>
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
                                                <div class="fw-semibold">
                                                    <h4 class="text-gray-900 fw-bold mb-0">
                                                        Нет выполненных тикетов
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="tab-pane fade" id="my_out" role="tabpanel">

                                    @if(count($sentTickets))
                                        <table class="table align-middle table-hover table-row-dashed fs-6 gy-5" id="sent_tickets_table">
                                            <thead>
                                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                                <th class="">{{trans('tickets.table.ticket')}}</th>
                                                <th class="min-w-125px">{{trans('tickets.table.performer')}}</th>
                                                <th class="min-w-125px">{{trans('tickets.table.priority')}}</th>
                                                <th class="min-w-125px">{{trans('tickets.table.created_at')}}</th>
                                                <th>{{trans('tickets.table.main_ticket')}}</th>
                                                <th class="text-end min-w-100px"></th>
                                            </tr>
                                            </thead>
                                            <tbody class="fw-semibold text-gray-600">

                                            @foreach($sentTickets as $ticket)
                                                <tr class="position_row_{{$ticket->id}}">
                                                    <td class="ps-3">
                                                        <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold">#{{$ticket->id}}</a>
                                                        @if($ticket->allChildren()->exists())
                                                            <div class="ms-2" data-bs-toggle="tooltip" aria-label="{{trans('tickets.has_children')}}" data-bs-original-title="{{trans('tickets.has_children')}}">
                                                                <i class="ki-outline ki-note-2 fs-2"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="d-flex align-items-center border-bottom-0">
                                                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                            @if($ticket->performer)
                                                                <a href="{{route('cabinet.users.show', $ticket->performer)}}" target="_blank">
                                                                    @if($ticket->performer->avatar)
                                                                        <div class="symbol-label">
                                                                            <img src="{{$ticket->performer->avatar}}" alt="{{$ticket->performer->name}}" class="w-100" />
                                                                        </div>
                                                                    @else
                                                                        <div class="symbol-label fs-3 bg-light-dark text-dark">
                                                                            {{get_initials($ticket->performer->name)}}
                                                                        </div>
                                                                    @endif
                                                                </a>
                                                                @else
                                                                <div class="symbol-label fs-3 bg-light-dark text-gray-800">
                                                                    ?
                                                                </div>
                                                            @endif

                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            @if($ticket->performer)
                                                                <a href="{{route('cabinet.users.show', $ticket->performer)}}" target="_blank" class="text-gray-800 text-hover-primary mb-1">
                                                                    {{$ticket->performer->name}}
                                                                </a>
                                                                <span>{{$ticket->performer->department}}</span>
                                                            @else
                                                                {{trans('tickets.sent.performer_empty')}}
                                                            @endif

                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-light-{{$ticket->priority->class}} fw-bold fs-7">
                                                            {{$ticket->priority->getNameByLocale()}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{\Carbon\Carbon::parse($ticket->created_at)->isoFormat('D MMM, HH:mm')}}
                                                    </td>
                                                    <td>
                                                        @if($ticket->parent)
                                                            <a href="{{route('cabinet.tickets.show', $ticket->parent)}}" class="text-gray-800 text-hover-primary fw-bold" target="_blank">#{{$ticket->parent->id}}</a>
                                                        @else
                                                            {{trans('tickets.table.no_parent')}}
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-2">
                                                        <div class="my-3 ms-9">
                                                            <a href="{{route('cabinet.tickets.show', $ticket)}}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                                <span data-bs-toggle="tooltip"
                                                                      data-bs-trigger="hover"
                                                                      aria-label="{{trans('tickets.table.more')}}"
                                                                      data-bs-original-title="{{trans('tickets.table.more')}}">
                                                                    <i class="ki-outline ki-black-right fs-2 text-gray-500"></i>
                                                                </span>
                                                            </a>
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
                                                    <h4 class="text-gray-900 fw-bold mb-0">
                                                        {{trans('tickets.empty_out')}}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if(auth()->user()->hasPermissions('close', \App\Models\Ticket::class))
                                    <div class="tab-pane fade" id="wait_closed" role="tabpanel">
                                        @if(count($doneTickets))
                                            <table class="table align-middle table-hover table-row-dashed fs-6 gy-5" id="done_tickets_table">
                                                <thead>
                                                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                                    <th class="">{{trans('tickets.table.ticket')}}</th>
                                                    <th class="min-w-125px">{{trans('tickets.table.performer')}}</th>
                                                    <th class="min-w-125px">{{trans('tickets.table.priority')}}</th>
                                                    <th class="min-w-125px">{{trans('tickets.table.created_at')}}</th>
                                                    <th>{{trans('tickets.table.main_ticket')}}</th>
                                                    <th class="text-end min-w-100px"></th>
                                                </tr>
                                                </thead>
                                                <tbody class="fw-semibold text-gray-600">
                                                @php
                                                    // Создаем массив ID тикетов, у которых есть выполненные подтикеты
                                                    $ticketsWithDoneChildren = [];
                                                    foreach($doneTickets as $ticket) {
                                                        if($ticket->parent) {
                                                            $ticketsWithDoneChildren[] = $ticket->parent->id;
                                                        }
                                                    }
                                                @endphp

                                                @foreach($doneTickets as $ticket)
                                                    @if(!in_array($ticket->id, $ticketsWithDoneChildren))
                                                        <tr class="position_row_{{$ticket->id}}">
                                                            <td class="ps-3">
                                                                <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold">#{{$ticket->id}}</a>
                                                                @if($ticket->allChildren()->exists())
                                                                    <div class="ms-2" data-bs-toggle="tooltip" aria-label="{{trans('tickets.has_children')}}" data-bs-original-title="{{trans('tickets.has_children')}}">
                                                                        <i class="ki-outline ki-note-2 fs-2"></i>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="d-flex align-items-center border-bottom-0">
                                                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                                    <a href="{{route('cabinet.users.show', $ticket->performer)}}" target="_blank">
                                                                        @if($ticket->performer->avatar)
                                                                            <div class="symbol-label">
                                                                                <img src="{{$ticket->performer->avatar}}" alt="{{$ticket->performer->name}}" class="w-100" />
                                                                            </div>
                                                                        @else
                                                                            <div class="symbol-label fs-3 bg-light-dark text-dark">
                                                                                {{get_initials($ticket->performer->name)}}
                                                                            </div>
                                                                        @endif
                                                                    </a>
                                                                </div>
                                                                <div class="d-flex flex-column">
                                                                    <a href="{{route('cabinet.users.show', $ticket->performer)}}" target="_blank" class="text-gray-800 text-hover-primary mb-1">
                                                                        {{$ticket->performer->name}}
                                                                    </a>
                                                                    <span>{{$ticket->performer->department}}</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                        <span class="badge badge-light-{{$ticket->priority->class}} fw-bold fs-7">
                                                            {{$ticket->priority->getNameByLocale()}}
                                                        </span>
                                                            </td>
                                                            <td>
                                                                {{\Carbon\Carbon::parse($ticket->created_at)->isoFormat('D MMM, HH:mm')}}
                                                            </td>
                                                            <td>
                                                                @if($ticket->parent)
                                                                    <a href="{{route('cabinet.tickets.show', $ticket->parent)}}" class="text-gray-800 text-hover-primary fw-bold" target="_blank">#{{$ticket->parent->id}}</a>
                                                                @else
                                                                    {{trans('tickets.table.no_parent')}}
                                                                @endif
                                                            </td>
                                                            <td class="text-end pe-2">
                                                                <div class="my-3 ms-9">
                                                                    <a href="{{route('cabinet.tickets.show', $ticket)}}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                                <span data-bs-toggle="tooltip"
                                                                      data-bs-trigger="hover"
                                                                      aria-label="{{trans('tickets.table.more')}}"
                                                                      data-bs-original-title="{{trans('tickets.table.more')}}">
                                                                    <i class="ki-outline ki-black-right fs-2 text-gray-500"></i>
                                                                </span>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 my-5">
                                                <i class="ki-outline ki-information fs-2tx text-warning me-4"></i>
                                                <div class="d-flex flex-stack flex-grow-1 ">
                                                    <div class=" fw-semibold">
                                                        <h4 class="text-gray-900 fw-bold mb-0">
                                                            {{trans('tickets.empty_wait_close')}}
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="col-xl-4">
            <div class="col-12">
                <div class="card card-flush h-xl-100">
                    <div class="card-header rounded bgi-no-repeat bgi-size-cover bgi-position-y-top bgi-position-x-center align-items-start h-250px" style="background: linear-gradient(112.14deg, #3A7BD5 0%, #00D2FF 100%);" data-bs-theme="light">
                        <h3 class="card-title align-items-start flex-column text-white pt-15">
                            <span class="fw-bold fs-2x mb-3">
                                {{trans('common.index.my_tickets')}}
                            </span>
                            <div class="fs-4 text-white">
                            <span class="position-relative d-inline-block">
                                <a href="{{route('cabinet.tickets.inbox')}}" class="link-white opacity-75-hover text-gray-800 fw-bold d-block mb-1">
                                    {{trans('common.index.opened_tickets')}} {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::OPENED) }}
                                </a>
                                <span class="position-absolute opacity-50 bottom-0 start-0 border-2 border-body border-bottom w-100"></span>
                            </span>
                            </div>
                        </h3>

                        <div class="card-toolbar pt-5">
                            <button class="btn btn-icon bg-white btn-circle"
                                    data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_new_ticket">
                                <i class="ki-outline ki-plus fs-1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body mt-n20">
                        <div class="mt-n20 position-relative">
                            <div class="row g-3 g-lg-6">
                                <div class="col-6">
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                    <span class="symbol-label">
                                        <i class="ki-outline ki-timer fs-1 text-primary"></i>
                                    </span>
                                        </div>

                                        <div class="m-0">
                                            <a href="{{ route('cabinet.tickets.inbox', ["filter[status]" => \App\Enums\TicketStatusEnum::IN_PROGRESS]) }}" class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">
                                                {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::IN_PROGRESS) }}
                                            </a>
                                            <a href="{{ route('cabinet.tickets.inbox', ["filter[status]" => \App\Enums\TicketStatusEnum::IN_PROGRESS]) }}" class="text-gray-500 fw-semibold fs-6">
                                                {{trans('tickets.statuses.in_progress')}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                        <span class="symbol-label">
                                            <i class="ki-outline ki-check-circle fs-1 text-primary"></i>
                                        </span>
                                        </div>
                                        <div class="m-0">
                                            <a href="{{ route('cabinet.tickets.inbox', ["filter[status]" => \App\Enums\TicketStatusEnum::DONE]) }}" class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">
                                                {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::DONE) }}
                                            </a>
                                            <a href="{{ route('cabinet.tickets.inbox', ["filter[status]" => \App\Enums\TicketStatusEnum::DONE]) }}" class="text-gray-500 fw-semibold fs-6">
                                                {{trans('tickets.statuses.done')}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                <span class="symbol-label">
                                    <i class="ki-outline ki-double-check-circle fs-1 text-primary"></i>
                                </span>
                                        </div>
                                        <div class="m-0">
                                            <a href="{{ route('cabinet.tickets.inbox', ["filter[status]" => \App\Enums\TicketStatusEnum::COMPLETED]) }}" class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">
                                                {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::COMPLETED) }}
                                            </a>
                                            <a href="{{ route('cabinet.tickets.inbox', ["filter[status]" => \App\Enums\TicketStatusEnum::COMPLETED]) }}" class="text-gray-500 fw-semibold fs-6">
                                                {{trans('tickets.statuses.completed')}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                <span class="symbol-label">
                                    <i class="ki-outline ki-cross-circle fs-1 text-primary"></i>
                                </span>
                                        </div>
                                        <div class="m-0">
                                            <a href="{{ route('cabinet.tickets.inbox', ["filter[status]" => \App\Enums\TicketStatusEnum::CANCELED]) }}" class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">
                                                {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::CANCELED) }}
                                            </a>
                                            <a href="{{ route('cabinet.tickets.inbox', ["filter[status]" => \App\Enums\TicketStatusEnum::CANCELED]) }}" class="text-gray-500 fw-semibold fs-6">
                                                {{trans('tickets.statuses.canceled')}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/plugins/filepond.min.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/widgets.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.jquery.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-type.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-size.js')}}"></script>
@endpush

@push('modals')
    @include('partials.modals.tickets.create')
@endpush

@push('custom_js')
    <script src="{{asset('assets/js/custom/tickets/create.js')}}"></script>
    <script src="{{asset('assets/js/custom/index/opened_tickets_table.js')}}"></script>
    <script src="{{asset('assets/js/custom/index/done_tickets_table.js')}}"></script>
    <script src="{{asset('assets/js/custom/index/my_done_tickets_table.js')}}"></script>
    <script src="{{asset('assets/js/custom/index/sent_tickets_table.js')}}"></script>
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
            labelFileTypeNotAllowed: '{{trans('tickets.create_modal.format_error')}}',
            maxFileSize: '5MB',
            labelMaxFileSizeExceeded: '{{trans('tickets.create_modal.size_limit')}}',
            labelIdle: '{{trans('tickets.create_modal.attachments_hint')}}'
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
                        Swal.fire('{{trans('common.swal.error_title')}}', '', 'error');
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
    </script>

    <script>
        $(function() {
            var KTChartsWidget44 = {
                self: null,
                rendered: false,

                init: function() {
                    this.fetchData();
                },

                fetchData: function() {
                    let self = this;
                    $.ajax({
                        url: '{{route('cabinet.get_tickets_chart')}}',
                        method: 'GET',
                        success: function(response) {
                            self.render(response.data);
                        },
                        error: function(xhr, status, error) {
                            console.error("Ошибка при получении данных:", error);
                        }
                    });
                },

                render: function(chartData) {
                    let $chart = $('#kt_charts_widget_44');
                    if ($chart.length === 0) return;

                    let chartColor = $chart.data('kt-chart-color');
                    let height = $chart.height();
                    let grayColor = KTUtil.getCssVariableValue('--bs-gray-800');
                    let borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
                    let baseColor = KTUtil.getCssVariableValue('--bs-' + chartColor);
                    let lightColor = KTUtil.getCssVariableValue('--bs-' + chartColor + '-light');

                    let options = {
                        series: [{
                            name: "{{trans('common.index.chart_tickets')}}",
                            data: chartData
                        }],
                        chart: {
                            fontFamily: "inherit",
                            type: "area",
                            height: height,
                            toolbar: { show: false },
                            zoom: { enabled: false },
                            sparkline: { enabled: true }
                        },
                        plotOptions: {},
                        legend: { show: false },
                        dataLabels: { enabled: false },
                        fill: { type: "solid", opacity: 1 },
                        stroke: { curve: "smooth", show: true, width: 3, colors: [baseColor] },
                        xaxis: {
                            categories: [
                                "{{trans('common.month.jan')}}",
                                "{{trans('common.month.feb')}}",
                                "{{trans('common.month.mar')}}",
                                "{{trans('common.month.apr')}}",
                                "{{trans('common.month.may')}}",
                                "{{trans('common.month.jun')}}",
                                "{{trans('common.month.jul')}}",
                                "{{trans('common.month.aug')}}",
                                "{{trans('common.month.sep')}}",
                                "{{trans('common.month.okt')}}",
                                "{{trans('common.month.nov')}}",
                                "{{trans('common.month.dec')}}"
                            ],
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: { show: false, style: { colors: grayColor, fontSize: "12px" } },
                            crosshairs: { show: false, position: "front", stroke: { color: borderColor, width: 1, dashArray: 3 } },
                            tooltip: { enabled: true, formatter: undefined, offsetY: 0, style: { fontSize: "12px" } }
                        },
                        yaxis: {
                            min: 0,
                            max: Math.max(...chartData) * 1.2, // максимум на 20% выше наибольшего значения
                            labels: { show: false, style: { colors: grayColor, fontSize: "12px" } }
                        },
                        states: {
                            normal: { filter: { type: "none", value: 0 } },
                            hover: { filter: { type: "none", value: 0 } },
                            active: { allowMultipleDataPointsSelection: false, filter: { type: "none", value: 0 } }
                        },
                        tooltip: {
                            style: { fontSize: "12px" },
                            y: {
                                formatter: function(val) {
                                    return val;
                                }
                            }
                        },
                        colors: [lightColor],
                        markers: { colors: lightColor, strokeColor: baseColor, strokeWidth: 3 }
                    };

                    this.self = new ApexCharts($chart[0], options);

                    this.self.render();
                    this.rendered = true;

                    this.bindThemeModeChange();
                },

                bindThemeModeChange: function() {
                    var self = this;
                    $('body').on('kt.thememode.change', function() {
                        if (self.rendered) {
                            self.self.destroy();
                        }
                        self.fetchData();
                    });
                }
            };

            KTChartsWidget44.init();
        });
    </script>
    @stack('js_from_modal')
@endpush
