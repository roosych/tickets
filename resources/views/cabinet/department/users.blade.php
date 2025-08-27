@extends('layouts.app')

@section('title', trans('common.users.title'))

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">{{trans('common.mainpage')}}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">{{trans('common.users.title')}}</li>
    </ul>
@endsection

@section('content')

    <div class="card">
        <div class="card-body py-4">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-5">
                <li class="nav-item">
                    <a class="nav-link text-active-primary d-flex align-items-center pb-5 active" data-bs-toggle="tab" href="#dept_users_info">
                    <i class="ki-outline ki-people fs-2 me-2"></i>
                        {{trans('users.users_table')}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#dept_users_tickets">
                        <i class="ki-outline ki-chart-simple fs-2 me-2"></i>
                        {{trans('users.ticket_table')}}
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="dept_users_info" role="tabpanel">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-125px">{{trans('users.name')}}</th>
                            <th class="min-w-125px">{{trans('users.position')}}</th>
                            <th class="min-w-125px">{{trans('users.roles')}}</th>
                            <th class="min-w-125px">{{trans('users.last_login')}}</th>
                        </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                        @foreach($users as $user)
                            <tr>
                                <td class="d-flex align-items-center border-bottom-0">
                                    <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                        <a href="{{route('cabinet.users.show', $user)}}" target="_blank">
                                            <div class="symbol-label">
                                                @if($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-100" />
                                                @else
                                                    <div class="symbol-label fs-3 bg-light-dark text-dark">
                                                        {{ get_initials($user->name) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="{{route('cabinet.users.show', $user)}}"
                                           class="text-gray-800 text-hover-primary mb-1"
                                           target="_blank"
                                        >
                                            {{$user->name}}
                                        </a>
                                        <span>{{$user->email}}</span>
                                    </div>
                                </td>
                                <td>{{$user->position}}</td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="badge badge-light-primary fs-7 m-1">
                                        {{$role->name}}
                                    </span>
                                    @empty
                                        {{trans('users.roles_empty')}}
                                    @endforelse
                                </td>
                                <td>
                                    @if($user->last_login)
                                        {{\Carbon\Carbon::parse($user->last_login)->isoFormat('D MMMM, HH:mm')}}
                                    @else
                                        {{trans('users.not_logined')}}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="dept_users_tickets" role="tabpanel">
                    <div class="d-flex align-items-center position-relative my-2 pt-5">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" user-report-filter="search" class="form-control form-control-solid w-250px ps-12"
                               placeholder="{{trans('tickets.table.search')}}" />
                    </div>
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="user_report_table">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px">{{trans('users.name')}}</th>
                            <th class="text-end min-w-75px">{{trans('users.tickets_statuses.opened')}}</th>
                            <th class="text-end min-w-75px">{{trans('users.tickets_statuses.in_progress')}}</th>
                            <th class="text-end min-w-75px">{{trans('users.tickets_statuses.done')}}</th>
                            <th class="text-end min-w-100px">{{trans('users.tickets_statuses.completed')}}</th>
                            <th class="text-end min-w-100px">{{trans('users.tickets_statuses.cancelled')}}</th>
                            @can('users', 'report')
                                <th></th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @forelse($users as $user)
                            <tr>
                                <td class="d-flex align-items-center border-bottom-0">
                                    <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                        <a href="{{route('cabinet.users.show', $user)}}" target="_blank">
                                            <div class="symbol-label">
                                                @if($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-100" />
                                                @else
                                                    <div class="symbol-label fs-3 bg-light-dark text-dark">
                                                        {{ get_initials($user->name) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="{{route('cabinet.users.show', $user)}}"
                                           class="text-gray-800 text-hover-primary mb-1"
                                           target="_blank"
                                        >
                                            {{$user->name}}
                                        </a>
                                        <span>{{$user->email}}</span>
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
                                @can('users', 'report')
                                    <td class="text-end">
                                        <a href="{{ route('cabinet.reports.tickets', ['filter' => ['executor_id' => $user->id]]) }}"
                                           class="btn btn-icon btn-active-light-primary"
                                           target="_blank"
                                           data-bs-toggle="tooltip"
                                           data-kt-menu-placement="bottom-end"
                                           data-bs-original-title="{{trans('sidebar.dept.reports.text')}}">
                                            <i class="ki-outline ki-chart-simple fs-3"></i>
                                        </a>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                        {{trans('users.users_empty')}}
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

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
    <script src="{{asset('assets/js/custom/department/users.js')}}"></script>
@endpush
