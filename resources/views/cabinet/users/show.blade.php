@extends('layouts.app')

@section('title', 'Сотрудник')

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

    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-column flex-lg-row-auto w-lg-250px w-xl-350px mb-10">
            <div class="card mb-5 mb-xl-8">
                <div class="card-body">
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
                            <div class="badge badge-lg badge-light-dark d-inline">
                                {{$user->position}}
                            </div>
                        </div>

                    </div>
                    <div class="d-flex flex-stack fs-4 py-3">
                        <div class="fw-bold rotate collapsible collapsed" data-bs-toggle="collapse" href="#kt_user_view_details" role="button" aria-expanded="false" aria-controls="kt_user_view_details">
                            Информация
                            <span class="ms-2 rotate-180">
                                <i class="ki-outline ki-down fs-3"></i>
                            </span>
                        </div>
                    </div>
                    <div id="kt_user_view_details" class="collapse" style="">
                        <div class="separator"></div>
                        <div class="pb-5 fs-6">
                            <div class="fw-bold mt-5">Отдел</div>
                            <div class="text-gray-600">{{$user->head ? $user->head->department : $user->department}}</div>
                            <div class="fw-bold mt-5">Email</div>
                            <div class="text-gray-600">{{$user->email}}</div>
                            <div class="fw-bold mt-5">3CX</div>
                            <div class="text-gray-600">{{$user->pager}}</div>
                            <div class="fw-bold mt-5">Последний вход</div>
                            <div class="text-gray-600">
                                @if($user->last_login)
                                    {{\Carbon\Carbon::parse($user->last_login)->isoFormat('D MMMM, HH:mm')}}
                                @else
                                    <em>не входил(а)</em>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="flex-lg-row-fluid ms-lg-15">
            <form id="attach_user_permissions" method="POST">
                @csrf
                <div class="card card-flush pt-4 mb-6 mb-xl-9">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="mb-0">Разрешения</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="fv-row">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <tbody class="text-gray-600 fw-semibold">
                                    <tr>
                                        <td class="fs-5 fw-bold text-gray-800">Полный доступ
                                            <span class="ms-2" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="Доступ на все имеющиеся функции">
                                                    <i class="ki-outline ki-information-5 fs-7"></i>
                                            </span></td>
                                        <td>
                                            <label class="form-check form-check-custom form-check-sm form-check-solid me-9">
                                                <input class="form-check-input" type="checkbox" value="" id="select_all_permissions">
                                                <span class="form-check-label">Выбрать все</span>
                                            </label>
                                        </td>
                                    </tr>
                                    @foreach($groupedPermissions as $permission => $items)
                                        <tr>
                                            <td class="text-gray-800">{{$permission}}</td>
                                            <td>
                                                <div class="d-flex">
                                                    @foreach($items as $item)
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                            <input class="form-check-input permission_checkbox" type="checkbox" value="{{$item->id}}" name="permissions[]"
                                                                {{ is_array($user->permissions->pluck('id')->toArray())
                                                                 &&
                                                                 in_array($item->id, $user->permissions->pluck('id')->toArray())
                                                                  ? 'checked' : '' }}
                                                            />
                                                            <span class="form-check-label">{{$item->name}}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <button id="attach_user_permissions_submit_btn" type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('custom_js')
    <script>
        // check all checkbox
        $("#select_all_permissions").click(function() {
            $(".form-check-input").prop("checked", $(this).prop("checked"));
        });
        $(".form-check-input").click(function() {
            if (!$(this).prop("checked")) {
                $("#select_all_permissions").prop("checked", false);
            }
        });
    </script>
@endpush
