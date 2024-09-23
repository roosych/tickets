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
                            <td class="d-flex align-items-center">
                                <!--begin:: Avatar -->
                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
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
                                <!--begin::User details-->
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
            <!--end::Table-->
        </div>
        <!--end::Card body-->
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

@endpush
