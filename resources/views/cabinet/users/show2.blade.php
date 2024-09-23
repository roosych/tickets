@extends('layouts.app')

@section('title', 'Пользователь')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">Главная</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.dept.users.index')}}" class="text-muted text-hover-primary">Сотрудники отдела</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">{{$user->name}}</li>
    </ul>
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid me-lg-15 order-2 order-lg-1 mb-10 mb-lg-0">
            <div class="card pt-4 mb-6 mb-xl-9">
                <div class="card-header border-0">
                    <div class="card-title">
                        <h2>Последняя активность</h2>
                    </div>

                    <div class="card-toolbar">
                        <!--begin::Button-->
                        <button type="button" class="btn btn-sm btn-light-primary">
{{--                            <i class="ki-outline ki-cloud-download fs-3"></i>--}}
                            Посмотреть все
                        </button>
                    </div>
                </div>

                <div class="card-body py-0">
                    <table class="table align-middle table-row-dashed fs-6 text-gray-600 fw-semibold gy-5">
                        <tbody>
                            @forelse($user->ticketHistories->take(10) as $item)
                                <tr>
                                    <td class="min-w-400px">
                                        {{$item->action}} тикета <a href="{{route('cabinet.tickets.show', $item->ticket->id)}}" class="fw-bold text-gray-900 text-hover-primary me-1" target="_blank">
                                            #{{$item->ticket->id}}
                                        </a>
                                        на <span class="badge badge-light-{{$item->status->color()}}">{{$item->status}}</span>
                                    </td>
                                    <td class="pe-0 text-gray-600 text-end min-w-200px">
                                        {{\Carbon\Carbon::parse($item->created_at)->isoFormat('D MMMM Y, HH:mm')}}
                                    </td>
                                </tr>
                            @empty
                                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 mb-4">
                                    <div class="d-flex flex-stack flex-grow-1 ">
                                        <div class=" fw-semibold">
                                            <div class="fs-6 text-gray-700">
                                                Нет активности
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

Проверка на редактирование юзера <br>

            @if(auth()->user()->getDepartmentId() === $user->getDepartmentId())
                <div class="card card-flush pt-4 mb-6 mb-xl-9">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="mb-0">Разрешения</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-4 fw-semibold mb-5">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary d-flex align-items-center pb-5 active"
                                   data-bs-toggle="tab" href="#user_roles">
                                    Роли
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary d-flex align-items-center pb-5"
                                   data-bs-toggle="tab" href="#user_permissions">
                                    Прямые полномочия
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="user_roles" role="tabpanel">
                                <div class="pt-5">
                                    @forelse(auth()->user()->getDepartment()->roles as $item)
                                        <div class="mb-5">
                                            <label class="form-check form-check-custom form-check-solid align-items-start">
                                                <input class="form-check-input me-3 mt-1" type="checkbox" name="roles" value="{{$item->id}}"
                                                    {{ is_array($user->roles->pluck('id')->toArray())
                                                     &&
                                                     in_array($item->id, $user->roles->pluck('id')->toArray())
                                                      ? 'checked' : '' }}
                                                >
                                                <span class="form-check-label d-flex flex-column align-items-start">
                                                <a href="{{route('cabinet.dept.roles.show', $item)}}" class="fw-bold text-gray-800 text-hover-primary fs-5 mb-0" target="_blank">
                                                    {{$item->name}}
                                                </a>
                                            <span class="text-muted fs-6">
                                                Разрешений: {{count($item->permissions)}}
                                            </span>
                                        </span>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed  p-6">
                                            <div class="d-flex flex-stack flex-grow-1 ">
                                                <div class=" fw-semibold">
                                                    <div class="fs-6 text-gray-700">
                                                        Вы можете присваивать сотруднику роль, но у Вашего отдела еще нет созданных ролей.
                                                        Для создания перейдите по
                                                        <a href="{{route('cabinet.dept.roles')}}" class="fw-bold" target="_blank">ссылке</a>.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="tab-pane fade" id="user_permissions" role="tabpanel">
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
                                            <form id="attach_user_permissions" method="POST">
                                                @csrf
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
                                            </form>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="card-footer pt-0">
                        <button id="attach_user_permissions_submit_btn" type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </div>
            @endif

        </div>
        <!--end::Content-->
        <!--begin::Sidebar-->
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
                                <div class="badge badge-lg badge-light-dark d-inline">
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
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Sidebar-->
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
