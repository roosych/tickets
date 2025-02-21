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


                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <select multiple="multiple" size="10" name="users[]" title="users[]">
                                    @foreach($users as $user)
                                        @if($user->id == $department->manager?->id)
                                            @continue
                                        @endif
                                        <option value="{{$user->id}}" {{$user->department_id == $department->id ? 'selected' : ''}}>
                                            {{$user->name}}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .btn-group {
            display: none !important;
        }
    </style>
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('modals')

@endpush

@push('custom_js')
    <script src="{{asset('assets/js/plugins/jquery.bootstrap-duallistbox.js')}}"></script>
    <script>
        let demo1 = $('select[name="users[]"]').bootstrapDualListbox({
            nonSelectedListLabel: 'Выберите сотрудников',
            selectedListLabel: 'Выбранные',
            preserveSelectionOnMove: 'moved',
            infoText: false,
            filterPlaceHolder: 'Поиск...',
            filterTextClear: false,
        });
    </script>
@endpush
