@extends('layouts.app')

@section('title', 'Отчеты')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">Главная</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">Отчеты</li>
    </ul>
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xxl-325px mb-8 mb-lg-0 me-lg-9 me-5">
            <form action="{{ route('cabinet.reports.tickets') }}" method="GET">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-5">
                            <label class="fs-6 form-label fw-bold text-gray-900">
                                Сотрудник
                            </label>
                            <select class="form-select form-select-solid @error('filter.executor_id') is-invalid @enderror"
                                    name="filter[executor_id]"
                                    data-control="select2"
                                    data-placeholder="Все сотрудники"
                                    data-allow-clear="true"
                                    data-hide-search="false">
                                <option value=""></option>
                                @foreach($deptUsers as $user)
                                    <option value="{{ $user->id }}"{{ request('filter.executor_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                       {{-- <div class="mb-5">
                            <label class="fs-6 form-label fw-bold text-gray-900">
                                Отделы
                            </label>
                            <select class="form-select form-select-solid @error('filter.department_id') is-invalid @enderror"
                                    name="filter[department_id]"
                                    data-control="select2"
                                    data-placeholder="Все отделы"
                                    data-hide-search="false">
                                <option value=""></option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}"{{ request('filter.department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>--}}

                        <div class="mb-5">
                            <label class="fs-6 form-label fw-bold text-gray-900">Период</label>
                            <input class="form-control form-control-solid w-100"
                                   name="date_range"
                                   placeholder="За всё время"
                                   id="users_report_period"
                                   value="{{ request('date_range') }}"
                            />
                        </div>

                        <div class="mb-5">
                            <label class="fs-6 form-label fw-bold text-gray-900">
                                Группировка
                            </label>
                            <div class="nav-group nav-group-fluid">
                                <label>
                                    <input type="radio"
                                           class="btn-check"
                                           name="grouping"
                                           value="{{\App\Enums\FilterGroupingEnum::USER}}"
                                        {{ request('grouping', \App\Enums\FilterGroupingEnum::USER->value) === \App\Enums\FilterGroupingEnum::USER->value ? 'checked' : '' }} />
                                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">
                            Сотрудник
                        </span>
                                </label>
                                <label>
                                    <input type="radio"
                                           class="btn-check"
                                           name="grouping"
                                           value="{{\App\Enums\FilterGroupingEnum::TAG}}"
                                        {{ \App\Enums\FilterGroupingEnum::isSelected(request('grouping'), \App\Enums\FilterGroupingEnum::TAG) ? 'checked' : '' }}
                                    />
                                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">
                            Тег
                        </span>
                                </label>
                                <label>
                                    <input type="radio"
                                           class="btn-check"
                                           name="grouping"
                                           value="{{\App\Enums\FilterGroupingEnum::PRIORITY}}"
                                            {{ \App\Enums\FilterGroupingEnum::isSelected(request('grouping'), \App\Enums\FilterGroupingEnum::PRIORITY) ? 'checked' : '' }}
                                            />
                                    <span class="btn btn-sm btn-color-muted btn-active btn-active-primary fw-bold px-4">
                            Приоритет
                        </span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-10">
                            <label class="fs-6 form-label fw-bold text-gray-900 mb-5">
                                Приоритет
                            </label>

                            @foreach($priorities as $priority)
                                <div class="form-check form-check-custom form-check-solid mb-5">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="filter[priorities_id][]"
                                           id="priority_{{$priority->id}}"
                                           value="{{$priority->id}}"
                                        {{ in_array($priority->id, request('filter.priorities_id', [])) ? 'checked' : '' }}
                                    />
                                    <label class="form-check-label flex-grow-1 fw-semibold text-gray-700 fs-6"
                                           for="priority_{{$priority->id}}">
                                        {{ $priority->getNameByLocale()}}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex align-items-center justify-content-end">
                            <a href="{{route('cabinet.reports.tickets')}}" class="btn btn-active-light-primary btn-color-gray-500 me-3">Сбросить</a>
                            <button type="submit" class="btn btn-primary">Применить</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--end::Aside-->
        <!--begin::Layout-->
        <div class="flex-lg-row-fluid">
            <div class="card pt-4 mb-6 mb-xl-9">

                <!--end::Card header-->
                <!--begin::Card body-->
                @if(\App\Enums\FilterGroupingEnum::isSelected(request('grouping'), \App\Enums\FilterGroupingEnum::PRIORITY))
                    @include('partials.reports.priority_accordion')
                @elseif(\App\Enums\FilterGroupingEnum::isSelected(request('grouping'), \App\Enums\FilterGroupingEnum::TAG))
                    @include('partials.reports.tag_accordion')
                @else
                    @include('partials.reports.user_accordion')
                @endif
                <!--end::Card body-->
            </div>



            {{--<div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                    <h3 class="fw-bold me-5 my-1">
                        Результатов: {{count($groupedTickets)}} ({{count($deptUsers)}})
                    </h3>
                    <!--begin::Card title-->
                    <div class="card-title">
                        <div id="kt_ecommerce_report_customer_orders_export" class="d-none"></div>
                    </div>

                    <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                        <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-outline ki-exit-up fs-2"></i>Export</button>
                        <div id="kt_ecommerce_report_customer_orders_export_menu" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3" data-kt-ecommerce-export="excel">
                                    Excel
                                </a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3" data-kt-ecommerce-export="pdf">
                                    PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card-body pt-0">
                    <div class="accordion" id="userTicketsAccordion">
                        @foreach($groupedTickets as $executorId => $tickets)
                            @php
                                $executor = $tickets->first()->performer;
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-{{ $executorId }}">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $executorId }}" aria-expanded="true"
                                            aria-controls="collapse-{{ $executorId }}">
                                        Исполнитель: {{ $executor->name}} ({{ count($tickets) }} тикетов)
                                    </button>
                                </h2>
                                <div id="collapse-{{ $executorId }}" class="accordion-collapse collapse"
                                     aria-labelledby="heading-{{ $executorId }}" data-bs-parent="#userTicketsAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-group">
                                            @foreach($tickets as $ticket)
                                                <li class="list-group-item">
                                                    Тикет ID: {{ $ticket->id }}<br>
                                                    Последний тикет: {{ $ticket->latest_ticket }}<br>
                                                    @if($ticket->tags->isNotEmpty())
                                                        Теги:
                                                        <ul>
                                                            @foreach($ticket->tags as $tag)
                                                                <li>{{ $tag->text }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                    @if($ticket->comments->isNotEmpty())
                                                        Комментарии:
                                                        <ul>
                                                            @foreach($ticket->comments as $comment)
                                                                <li>{{ $comment->body }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                    @if($ticket->histories->isNotEmpty())
                                                        История:
                                                        <ul>
                                                            @foreach($ticket->histories as $history)
                                                                <li>{{ $history->action }} от пользователя ID {{ $history->user_id }} в {{ $history->created_at }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
--}}{{--                    @foreach($result as $item)
                        <pre>{{ json_encode($item, JSON_PRETTY_PRINT) }}</pre>
                    @endforeach--}}{{--
                </div>
            </div>--}}
        </div>
    </div>
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
    @php
        $today = \Carbon\Carbon::today()->format('d.m.Y');
        $yesterday = \Carbon\Carbon::yesterday()->format('d.m.Y');
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth()->format('d.m.Y');
        $endOfMonth = \Carbon\Carbon::now()->endOfMonth()->format('d.m.Y');
        $sevenDaysAgo = \Carbon\Carbon::now()->subDays(6)->format('d.m.Y');
        $thirtyDaysAgo = \Carbon\Carbon::now()->subDays(29)->format('d.m.Y');
    @endphp
    <script>
        $("#users_report_period").daterangepicker({
                ranges: {
                    "Сегодня": ["{{ $today }}", "{{ $today }}"],
                    "Вчера": ["{{ $yesterday }}", "{{ $yesterday }}"],
                    "За 7 дней": ["{{ $sevenDaysAgo }}", "{{ $today }}"],
                    "За 30 дней": ["{{ $thirtyDaysAgo }}", "{{ $today }}"],
                    "Этот месяц": ["{{ $startOfMonth }}", "{{ $endOfMonth }}"],
                },
                locale: {
                    customRangeLabel: "Период",
                    format: "DD.MM.YYYY",
                },
                autoUpdateInput: false,
            },
            function(start, end) {
                $('#users_report_period').val(start.format('DD.MM.YYYY') + ' - ' + end.format('DD.MM.YYYY'));
            }
        );
    </script>
@endpush

@push('modals')

@endpush

@push('custom_js')
    <script src="{{asset('assets/js/custom/reports/users.js')}}"></script>
@endpush
