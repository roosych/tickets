@extends('layouts.app')

@section('title', $department->name)

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">Главная</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.settings.departments')}}" class="text-muted text-hover-primary">Департаменты</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">{{$department->name}}</li>
    </ul>
@endsection

@section('content')
    <form id="kt_ecommerce_edit_order_form" class="form d-flex flex-column flex-lg-row"
          method="POST"
          action="{{route('cabinet.settings.departments.show', $department)}}">
        @csrf
        <div class="w-100 flex-lg-row-auto w-lg-300px mb-7 me-7 me-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Сотрудников:</h2>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-6">
                        <div class="fv-row">
                            <div class="fs-4x fw-semibold lh-sm">
                                {{count($department->users)}}
                            </div>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label">
                                Название
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name"
                                   value="{{ old('name', $department->name) }}"
                            >
                            @error('name')
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="fv-row">
                            <label class="form-label">
                                Менеджер
                            </label>
                            <select class="form-select @error('manager_id') is-invalid @enderror" data-control="select2"
                                    data-placeholder="Выбрать из списка"
                                    name="manager_id"
                                    id="kt_ecommerce_edit_order_payment">
                                <option></option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ ($department->manager && $user->id == $department->manager->id) ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('manager_id')
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="fv-row">
                            <label class="form-label">Тикетный?</label>
                            <select class="form-select mb-2"
                                    data-control="select2"
                                    data-hide-search="true"
                                    name="active">
                                <option></option>
                                <option value="1" {{ $department->active ? 'selected' : '' }}>Да</option>
                                <option value="0" {{ !$department->active ? 'selected' : '' }}>Нет</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-lg-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header d-flex align-items-center justify-content-between mb-3">
                    <div class="card-title">
                        <h2>Сотрудники департамента</h2>
                    </div>
                    <button type="submit" for="kt_ecommerce_edit_order_form" class="btn btn-primary ms-auto">
                        Сохранить
                    </button>
                </div>


                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-10">
                        <div>
                            <div class="row row-cols-1 row-cols-xl-3 row-cols-md-2 border border-dashed rounded pt-3 pb-1 px-2 mb-5 mh-300px overflow-scroll"
                                 id="kt_ecommerce_edit_order_selected_products">

                                @if($department->manager)
                                    <div class="col my-2"
                                         data-kt-ecommerce-edit-order-filter="product"
                                         data-kt-ecommerce-edit-order-id="product_{{$department->manager->id}}">
                                        <div class="d-flex align-items-center border border-dashed p-3 rounded bg-white">
                                            <a href="{{route('cabinet.users.show', $department->manager)}}" class="symbol symbol-50px" target="_blank">
                                                @if($department->manager->avatar)
                                                    <span class="symbol-label" style="background-image: url('{{ $department->manager->avatar }}'); background-size: cover; background-position: center;"></span>
                                                @else
                                                    <span class="symbol-label fs-3 bg-light-dark text-dark d-flex align-items-center justify-content-center">
                                                        {{ get_initials($department->manager->name) }}
                                                    </span>
                                                @endif
                                            </a>
                                            <div class="ms-5">
                                                <a href="{{route('cabinet.users.show', $department->manager)}}" class="text-gray-800 text-hover-primary fs-5 fw-bold" target="_blank">
                                                    {{$department->manager->name}}
                                                </a>
                                                <div class="text-muted fs-7">
                                                    {{$department->manager->email}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if(count($department->users))
                                    @foreach($department->users as $user)
                                            @if($user->id == $department->manager?->id)
                                                @continue
                                            @endif
                                        <div class="col my-2"
                                             data-kt-ecommerce-edit-order-filter="product"
                                             data-kt-ecommerce-edit-order-id="product_{{$user->id}}">
                                            <div class="d-flex align-items-center border border-dashed p-3 rounded">
                                                <a href="{{route('cabinet.users.show', $user)}}" class="symbol symbol-50px" target="_blank">
                                                    @if($user->avatar)
                                                        <span class="symbol-label" style="background-image: url('{{ $user->avatar }}'); background-size: cover; background-position: center;"></span>
                                                    @else
                                                        <span class="symbol-label fs-3 bg-light-dark text-dark d-flex align-items-center justify-content-center">
                                                            {{ get_initials($user->name) }}
                                                        </span>
                                                    @endif
                                                </a>
                                                <div class="ms-5">
                                                    <a href="{{route('cabinet.users.show', $user)}}" class="text-gray-800 text-hover-primary fs-5 fw-bold" target="_blank">
                                                        {{$user->name}}
                                                    </a>
                                                    <div class="text-muted fs-7">
                                                        {{$user->email}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="w-100 text-muted mb-2">
                                        Чтобы добавить сотрудника, выберите его из списка
                                    </span>
                                @endif

                            </div>
                        </div>

                        <div class="separator"></div>

                        <div class="d-flex align-items-center position-relative mb-n7">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                            <input type="text"
                                   data-kt-ecommerce-edit-order-filter="search"
                                   class="form-control form-control-solid w-100 w-lg-50 ps-12"
                                   placeholder="Поиск..." />
                        </div>

                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_ecommerce_edit_order_product_table">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-25px pe-2"></th>
                                <th class="min-w-200px">Сотрудник</th>
                            </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                @foreach($users as $user)
                                    @if($user->id == $department->manager?->id)
                                        @continue
                                    @endif
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       name="users[]"
                                                       value="{{$user->id}}"
                                                    {{$user->department_id == $department->id ? 'checked' : ''}}
                                                />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center"
                                                 data-kt-ecommerce-edit-order-filter="product"
                                                 data-kt-ecommerce-edit-order-id="product_{{$user->id}}">

                                                <a href="{{route('cabinet.users.show', $user)}}" class="symbol symbol-50px" target="_blank">
                                                    @if($user->avatar)
                                                        <span class="symbol-label" style="background-image: url('{{ $user->avatar }}'); background-size: cover; background-position: center;"></span>
                                                    @else
                                                        <span class="symbol-label fs-3 bg-light-dark text-dark d-flex align-items-center justify-content-center">
                                                            {{ get_initials($user->name) }}
                                                        </span>
                                                    @endif
                                                </a>

                                                <div class="ms-5">
                                                    <a href="{{route('cabinet.users.show', $user)}}" class="text-gray-800 text-hover-primary fs-5 fw-bold" target="_blank">
                                                        {{$user->name}}
                                                    </a>

                                                    <div class="text-muted fs-7">
                                                        {{$user->email}}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
    <script src="{{asset('assets/js/custom/department/users-show.js')}}"></script>
@endpush
