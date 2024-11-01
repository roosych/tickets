@extends('layouts.app')

@section('title', 'Список с Active Directory')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">{{trans('common.mainpage')}}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">Пользователи</li>
    </ul>
@endsection

@section('content')

    <div class="card">
        <div class="card-body py-4">
            <div class="d-flex align-items-center position-relative my-2 pt-5">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" user-report-filter="search" class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{trans('tickets.table.search')}}" />
            </div>
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="user_report_table">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-100px">Сотрудник</th>
                    <th class="text-end min-w-75px">Департамент</th>
                    <th class="text-end min-w-75px">Позиция</th>
                    <th class="text-end min-w-75px">HEAD</th>
                    <th class="text-end min-w-100px">3CX</th>
                    <th class="text-end min-w-100px">Учетка</th>
                    <th class="text-end">Видимый</th>
                    <th class="text-end">Активный</th>
                    <th class="text-end">Email notify</th>
                    <th class="text-end">TG notify</th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                @forelse($users as $user)
                    <tr>
                        <td class="d-flex align-items-center border-bottom-0">
                            <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                @if($user->avatar)
                                    <div class="symbol-label">
                                        <img src="{{$user->avatar}}" alt="{{$user->name}}" class="w-100" />
                                    </div>
                                @else
                                    <div class="symbol-label fs-3 bg-light-dark text-dark">
                                        {{get_initials($user->name)}}
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex flex-column">
                                <p class="text-gray-800 mb-1">
                                    {{$user->name}}
                                    @if($user->is_manager)
                                        <i class="ki-solid ki-star fs-3 text-warning"></i>
                                    @endif
                                </p>
                                <span>{{$user->email}}</span>
                            </div>
                        </td>
                        <td class="text-end pe-0">
                            {{$user->department}}
                        </td>
                        <td class="text-end pe-0">
                            {{$user->position}}
                        </td>
                        <td class="text-end pe-0">
                            <p class="mb-0 fw-bold {{$user->head->name ?? 'text-danger'}}">
                                {{$user->head->name ?? 'не указан'}}
                            </p>
                        </td>
                        <td class="text-end pe-0">
                            {{$user->pager}}
                        </td>
                        <td class="text-end">
                            {{$user->username}}
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="form-check form-check-solid form-check-custom form-switch">
                                    <input class="form-check-input w-35px h-20px"
                                           type="checkbox"
                                           id="visibleSwitch"
                                        {{$user->visible ? 'checked' : ''}}>
                                    <label class="form-check-label" for="visibleSwitch"></label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="form-check form-check-solid form-check-custom form-switch">
                                    <input class="form-check-input w-35px h-20px"
                                           type="checkbox"
                                           id="activeSwitch"
                                        {{$user->active ? 'checked' : ''}}>
                                    <label class="form-check-label" for="activeSwitch"></label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="form-check form-check-solid form-check-custom form-switch">
                                    <input class="form-check-input w-35px h-20px"
                                           type="checkbox"
                                           id="emailSwitch"
                                        {{$user->email_notify ? 'checked' : ''}}>
                                    <label class="form-check-label" for="emailSwitch"></label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div class="form-check form-check-solid form-check-custom form-switch">
                                    <input class="form-check-input w-35px h-20px"
                                           type="checkbox"
                                           id="tgSwitch"
                                        {{$user->tg_notify ? 'checked' : ''}}>
                                    <label class="form-check-label" for="tgSwitch"></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    {{trans('users.users_empty')}}
                @endforelse
                </tbody>
            </table>
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
    <script src="{{asset('assets/js/custom/settings/users.js')}}"></script>
@endpush
