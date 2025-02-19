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
                        <img src="{{$user->avatar}}" alt="image" class="border border-white border-4">
                    </div>
                </div>

                <!--begin::Info-->
                <div class="d-flex flex-stack flex-wrap align-items-end">
                    <!--begin::User-->
                    <div class="d-flex flex-column">
                        <!--begin::Name-->
                        <div class="d-flex align-items-center">
                            <p class="text-gray-800 fs-2 fw-bolder me-1 mb-0">
                                {{$user->name}}
                            </p>
                        </div>

                        {{--<span class="fw-bold text-gray-600 fs-6 mb-2 d-block">
                            text
                        </span>--}}

                        <!--begin::Info-->
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
                        <!--end::Info-->
                    </div>
                    <!--end::User-->

                </div>
                <!--end::Info-->
            </div>
            <!--end::Details-->
        </div>
    </div>
    {{--<div class="d-flex flex-column flex-lg-row">
        <div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xl-300px mb-10 order-1 order-lg-2">
            <div class="card card-flush pt-3 mb-0" data-kt-sticky="true" data-kt-sticky-name="subscription-summary" data-kt-sticky-offset="{default: false, lg: '200px'}" data-kt-sticky-width="{lg: '250px', xl: '300px'}" data-kt-sticky-left="auto" data-kt-sticky-top="150px" data-kt-sticky-animation="false" data-kt-sticky-zindex="95">
                <div class="card-body pt-0 fs-6">
                    <div class="">
                        <div class="d-flex flex-center flex-column py-5">
                            <div class="symbol symbol-100px symbol-circle mb-7">
                                @if($user->avatar)
                                    <img src="{{$user->avatar}}" alt="{{$user->name}}">
                                @else
                                    <div class="symbol-label fs-3 bg-light-dark text-dark">
                                        {{get_initials($user->name)}}
                                    </div>
                                @endif
                            </div>
                            <p class="fs-3 text-gray-800 fw-bold mb-3">
                                {{$user->name}}
                            </p>
                            <div class="mb-3">
                                <div class="badge badge-lg badge-light-secondary d-inline">
                                    {{$user->position}}
                                </div>
                            </div>

                        </div>
                        <div class="separator separator-dashed mb-7"></div>
                        <div class="fs-6">
                            <div class="fw-bold mt-5">Отдел</div>
                            <div class="text-gray-600">{{$user->head ? $user->head->department : $user->department}}</div>
                            <div class="fw-bold mt-5">Email</div>
                            <div class="text-gray-600">{{$user->email}}</div>
                            @if($user->mobile)
                                <div class="fw-bold mt-5">Номер</div>
                                <div class="text-gray-600">{{$user->mobile}}</div>
                            @endif
                            @if($user->pager)
                                <div class="fw-bold mt-5">3CX</div>
                                <div class="text-gray-600">{{$user->pager}}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>--}}
@endsection

@push('vendor_css')

@endpush

@push('vendor_js')

@endpush

@push('custom_js')

@endpush
