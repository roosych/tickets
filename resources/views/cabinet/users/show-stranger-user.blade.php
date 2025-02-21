@extends('layouts.app')

@section('title', 'Пользователь')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">
                {{trans('common.mainpage')}}
            </a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            Сотрудники
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">{{$user->name}}</li>
    </ul>
@endsection

@section('content')
    <div class="card card-flush mb-9" id="kt_user_profile_panel">
        <div class="card-header rounded-top bgi-size-cover h-200px"
             style="background-position: 100% 50%; background-image:url('{{asset('assets/media/misc/profile-head-bg.jpg')}}')">
        </div>

        <div class="card-body mt-n19">
            <div class="m-0">
                    <div class="d-flex flex-stack align-items-end pb-4 mt-n19">
                        <div class="symbol symbol-125px symbol-lg-150px symbol-fixed position-relative mt-n3">

                            @if($user->avatar)
                                <span class="symbol-label" style="background-image: url('{{ $user->avatar }}'); background-size: cover; background-position: center;"></span>
                            @else
                                <span class="symbol-label fs-3 bg-light-dark text-dark d-flex align-items-center justify-content-center">
                                    {{ get_initials($user->name) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex flex-stack flex-wrap align-items-end">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <p class="text-gray-800 fs-2 fw-bolder me-1 mb-0">
                                    {{$user->name}}
                                </p>
                            </div>

                            {{--<span class="fw-bold text-gray-600 fs-6 mb-2 d-block">
                                text
                            </span>--}}

                            <div class="d-flex flex-column fw-semibold fs-7 pe-2">
                                <p class="d-flex align-items-center text-gray-500 fs-5 me-3 mb-0">
                                    {{$user->department}} - {{$user->position}}
                                </p>
                                <div>
                                    <p class="d-flex align-items-center text-gray-500 fs-5 me-3 mb-0">
                                        {{$user->email}}
                                    </p>

                                    @if($user->pager)
                                        <div>
                                            <p class="d-flex align-items-center text-gray-500 fs-5 me-3">
                                                {{$user->pager}}
                                            </p>
                                        </div>
                                    @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @if(auth()->user()->is_admin)
        <div class="row">
            <div class="col-lg-6">
                <h4>Прямые разрешения:</h4>
                @foreach($user->permissions as $permission)
                    <p>{{$permission->group}} - {{$permission->name}}</p>
                @endforeach
            </div>
            <div class="col-lg-6">
                <h4>Роли:</h4>
                @foreach($user->roles as $role)
                    <h5>{{$role->name}}</h5>
                    <ul>
                        @foreach($role->permissions as $permission)
                            <p>{{$permission->group}} - {{$permission->name}}</p>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>

    @endif
@endsection

@push('vendor_css')

@endpush

@push('vendor_js')

@endpush

@push('custom_js')

@endpush
