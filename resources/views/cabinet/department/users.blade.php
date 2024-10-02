@extends('layouts.app')

@section('title', 'Сотрудники отдела')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">Главная</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">Сотрудники отдела</li>
    </ul>
@endsection

@section('content')

    <div class="card">
        <div class="card-body py-4">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-5">
                <li class="nav-item">
                    <a class="nav-link text-active-primary d-flex align-items-center pb-5 active" data-bs-toggle="tab" href="#dept_users_info">
                    <i class="ki-outline ki-people fs-2 me-2"></i>
                        Сотрудники
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary d-flex align-items-center pb-5" data-bs-toggle="tab" href="#dept_users_tickets">
                        <i class="ki-outline ki-chart-simple fs-2 me-2"></i>
                        Таблица тикетов
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="dept_users_info" role="tabpanel">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-125px">Имя</th>
                            <th class="min-w-125px">Должность</th>
                            <th class="min-w-125px">Роли</th>
                            <th class="min-w-125px">Последний вход</th>
                        </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                        @foreach($users as $user)
                            <tr>
                                <td class="d-flex align-items-center border-bottom-0">
                                    <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                        <a href="{{route('cabinet.users.show', $user)}}" target="_blank">
                                            <div class="symbol-label">
                                                <img src="{{$user->avatar}}" alt="{{$user->name}}" class="w-100" />
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
                                        нет ролей
                                    @endforelse
                                </td>
                                <td>
                                    @if($user->last_login)
                                        {{\Carbon\Carbon::parse($user->last_login)->isoFormat('D MMMM, HH:mm')}}
                                    @else
                                        не входил(а)
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
                        <input type="text" user-report-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Поиск..." />
                    </div>
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
                        @forelse($users as $user)
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
