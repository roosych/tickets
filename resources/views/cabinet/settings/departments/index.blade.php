@extends('layouts.app')

@section('title', 'Департаменты')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">
                Главная
            </a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            Департаменты
        </li>
    </ul>
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid">
            <div class="card pt-4 mb-6 mb-xl-9">

                <div class="card-header border-0">
                    <div class="card-title">
                        <h3 class="fw-bold mb-0">
                            Департаменты
                        </h3>
                    </div>
                </div>

                <div id="departments_accordion" class="card-body pt-0">
                    @foreach($departments as $department)
                        <div class="py-0">
                            <div class="py-3 d-flex flex-stack flex-wrap">
                                <div class="d-flex align-items-center collapsible collapsed rotate"
                                     data-bs-toggle="collapse"
                                     href="#departments_accordion_{{$department->id}}"
                                     role="button"
                                     aria-expanded="false"
                                     aria-controls="departments_accordion_{{$department->id}}">
                                    <div class="me-3 rotate-90">
                                        <i class="ki-outline ki-right fs-3"></i>
                                    </div>
                                    <div class="me-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fw-semibold ms-5">
                                                <a href="javascript:void(0);" class="fs-5 fw-bold text-gray-900">
                                                    {{$department->name}}
                                                </a>
                                                <div class="badge badge-light-primary ms-3">
                                                    {{ count($department->users) }}
                                                </div>
                                                <div class="fs-6 text-muted">
                                                    @if($department->manager)
                                                        <a href="{{route('cabinet.users.show', $department->manager)}}">
                                                            {{$department->manager->name}}
                                                        </a>
                                                    @else
                                                        <span class="text-muted fs-7">
                                                            <em>менеджер не указан</em>
                                                        </span>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex my-3 ms-9">
                                    <a href="{{route('cabinet.settings.departments.show', $department)}}" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3">
                                        <span data-bs-toggle="tooltip" data-bs-trigger="hover" title="Редактировать">
                                            <i class="ki-outline ki-setting-2 fs-3"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div id="departments_accordion_{{$department->id}}" class="collapse fs-6 ps-10"
                                 data-bs-parent="#departments_accordion"
                            >
                                @if($department->users->isNotEmpty())
                                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                                        <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-125px">{{trans('users.name')}}</th>
                                            <th class="min-w-125px">{{trans('users.position')}}</th>
                                            <th class="min-w-125px">{{trans('users.last_login')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                        @foreach($department->users as $user)
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
                                @else
                                    <div class="border border-dashed rounded p-3 mb-5">
                                        <span class="text-muted mb-2">
                                            Никого нет :(
                                        </span>
                                    </div>
                                @endif

                            </div>
                        </div>

                        @if (!$loop->last)
                            <div class="separator separator-dashed"></div>
                        @endif

                    @endforeach
                </div>


            </div>
        </div>
    </div>
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('modals')

@endpush

@push('custom_js')

@endpush
