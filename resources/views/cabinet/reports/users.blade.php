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
        <li class="breadcrumb-item text-muted">Отчет по сотрудникам</li>
    </ul>
@endsection

@section('content')
    {{--<div class="card card-flush">
        <!--begin::Card header-->
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                    <input type="text" user-report-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Поиск..." />
                </div>
                <!--end::Search-->
                <!--begin::Export buttons-->
                <div id="kt_ecommerce_report_returns_export" class="d-none"></div>
                <!--end::Export buttons-->
            </div>
            <!--end::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                <!--begin::Daterangepicker-->
                <input class="form-control form-control-solid w-100 mw-250px" placeholder="Pick date range" id="kt_ecommerce_report_returns_daterangepicker" />
                <!--end::Daterangepicker-->
                <!--begin::Export dropdown-->
                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-outline ki-exit-up fs-2"></i>
                    Экспорт
                </button>
                <!--begin::Menu-->
                <div id="users_export_menu" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" data-kt-ecommerce-export="excel">
                            Excel
                        </a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" data-kt-ecommerce-export="pdf">PDF</a>
                    </div>
                </div>
                <!--end::Menu-->
                <!--end::Export dropdown-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Table-->
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="user_report_table">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-100px">Сотрудник</th>
                    <th class="text-end min-w-75px">Открытых тикетов</th>
                    <th class="text-end min-w-75px">Тикеты в процессе</th>
                    <th class="text-end min-w-75px">Выполненных тикетов</th>
                    <th class="text-end min-w-100px">Завершенных тикетов</th>
                    <th class="text-end min-w-100px">Отмененных тикетов</th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                @forelse($deptUsers as $user)
                    <tr>
                        <td class="d-flex align-items-center">
                            <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                <a href="{{route('cabinet.users.show', $user)}}" target="_blank">
                                    <div class="symbol-label">
                                        <img src="{{$user->avatar}}" alt="{{$user->name}}" class="w-100" />
                                    </div>
                                </a>
                            </div>
                            <div class="d-flex flex-column">
                                {{$user->name}}
                            </div>
                        </td>
                        <td class="text-end pe-0">
                            {{$user->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::OPENED)}}
                        </td>
                        <td class="text-end pe-0">
                            {{$user->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::IN_PROGRESS)}}
                        </td>
                        <td class="text-end pe-0">
                            {{$user->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::DONE)}}
                        </td>
                        <td class="text-end pe-0">
                            {{$user->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::COMPLETED)}}
                        </td>
                        <td class="text-end">
                            {{$user->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::CANCELED)}}
                        </td>
                    </tr>
                @empty
                    нет сотрудников в отделе
                @endforelse
                </tbody>
            </table>
        </div>
    </div>--}}
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
    <script src="{{asset('assets/js/custom/reports/users.js')}}"></script>
@endpush
