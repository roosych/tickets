@extends('layouts.app')

@section('title', trans('common.roles.title'))

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">{{trans('common.mainpage')}}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.dept.roles')}}" class="text-muted text-hover-primary">{{trans('common.roles.title')}}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">{{$role->name}}</li>
    </ul>
@endsection

@section('content')

    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-column flex-lg-row-auto w-100 w-lg-200px w-xl-300px mb-10">
            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="mb-0">{{$role->name}}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="accordion accordion-icon-collapse" id="permission_accordion">
                        @forelse($groupedRolePermissions as $group => $permissions)
                            <div class="mb-5">
                                <div class="accordion-header py-3 d-flex collapsed" data-bs-toggle="collapse"
                                     data-bs-target="#kt_accordion_3_item_{{$loop->index}}">
                                    <span class="accordion-icon">
                                        <i class="ki-outline ki-plus-square fs-3 accordion-icon-off"></i>
                                        <i class="ki-outline ki-minus-square fs-3 accordion-icon-on"></i>
                                    </span>
                                    <h3 class="fs-4 fw-semibold mb-0 ms-4">{{$group}}</h3>
                                </div>

                                <div id="kt_accordion_3_item_{{$loop->index}}" class="collapse fs-6 ps-10"
                                     data-bs-parent="#permission_accordion">
                                    @foreach($permissions as $permission)
                                        <div class="d-flex align-items-center py-2">
                                            <span class="bullet bg-primary me-3"></span>
                                            {{$permission->name}}
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @empty
                            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                                <i class="ki-outline ki-information fs-2tx text-warning me-4"></i>
                                <div class="d-flex flex-stack flex-grow-1">
                                    <div class="fw-semibold">
                                        <div class="fs-6 text-gray-700">
                                            {{trans('common.roles.permissions_empty')}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                @can('create', \App\Models\Role::class)
                    <div class="card-footer pt-0">
                        <button type="button" class="btn btn-light btn-active-primary" data-bs-toggle="modal"
                                data-bs-target="#kt_modal_update_role">
                            {{trans('common.roles.buttons.edit')}}
                        </button>
                    </div>
                @endcan
            </div>
            @include('partials.modals.roles.edit_role')
        </div>

        <div class="flex-lg-row-fluid ms-lg-10">
            <div class="card card-flush mb-6 mb-xl-9">
                <div class="card-header pt-5">
                    <div class="card-title">
                        <h2 class="d-flex align-items-center">
                            {{trans('common.roles.role_users')}}
                        </h2>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex align-items-center position-relative my-1"
                             data-kt-view-roles-table-toolbar="base">
                            <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                            <input type="text" data-kt-roles-table-filter="search"
                                   class="form-control form-control-solid w-250px ps-15" placeholder="{{trans('tickets.table.search')}}"/>
                            @can('create', \App\Models\Role::class)
                                <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal"
                                        data-bs-target="#attach_users_modal">
                                    <i class="ki-outline ki-plus fs-2"></i> {{trans('common.roles.buttons.add')}}
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    @if($role->users !== null && !$role->users->isEmpty())
                        <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_roles_view_table">
                            <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-150px">{{trans('common.roles.table_name')}}</th>
                                <th class="min-w-150px">{{trans('common.roles.connected_at')}}</th>
                                <th class="min-w-100px">{{trans('common.roles.last_login')}}</th>
                                <th class="text-end min-w-100px">{{trans('common.roles.actions')}}</th>
                            </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                            @foreach($role->users as $item)
                                <tr class="user_row_{{$item->id}}">
                                    <td class="d-flex align-items-center">
                                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                            <a href="{{route('cabinet.users.show', $item)}}" target="_blank">
                                                <div class="symbol-label">
                                                    <div class="symbol-label">
                                                        @if($item->avatar)
                                                            <img src="{{ $item->avatar }}" alt="{{ $item->name }}" class="w-100" />
                                                        @else
                                                            <div class="symbol-label fs-3 bg-light-dark text-dark">
                                                                {{ get_initials($item->name) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <a href="{{route('cabinet.users.show', $item)}}"
                                               class="text-gray-800 text-hover-primary mb-1" target="_blank">{{$item->name}}</a>
                                            <span>{{$item->email}}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="badge badge-light fw-bold">
                                            {{\Carbon\Carbon::parse($item->pivot->created_at)->isoFormat('D MMM YYYY')}}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="badge badge-light fw-bold">
                                            {{\Carbon\Carbon::parse($item->created_at)->isoFormat('D MMM YYYY, h:mm:ss')}}
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end my-3 ms-9">
                                            <a href="javascript:void(0);" data-id="{{$item->id}}"
                                               class="btn btn-icon btn-active-light-danger w-30px h-30px me-3 detach_user"
                                               data-bs-toggle="tooltip" title="{{trans('common.roles.disconnect')}}">
                                                <i class="ki-outline ki-disconnect fs-3"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 my-5">
                            <i class="ki-outline ki-information fs-2tx text-warning me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1 ">
                                <div class=" fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">
                                        {{trans('common.roles.empty_users')}}
                                    </h4>
                                    <div class="fs-6 text-gray-700 ">
                                        {{trans('common.roles.empty_users_text2')}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @can('create', \App\Models\Role::class)
        @include('partials.modals.roles.add_user')
    @endcan
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('custom_js')
    <script src="{{asset('assets/js/custom/roles/view.js')}}"></script>
    <script>
        @can('create', \App\Models\Role::class)
        // check all checkbox
        $("#select_all_permissions").click(function() {
            $(".form-check-input").prop("checked", $(this).prop("checked"));
        });
        $(".form-check-input").click(function() {
            if (!$(this).prop("checked")) {
                $("#select_all_permissions").prop("checked", false);
            }
        });

        let token = $('meta[name="csrf-token"]').attr('content');

        // attach users to role
        $(document).on('click', '#attach_form_submit_btn', function (e) {
            e.preventDefault();
            applyWait($('body'));
            $.ajax({
                url: "{{route('cabinet.dept.roles.attach_users', $role)}}",
                method: "POST",
                headers: {'X-CSRF-TOKEN': token},
                data: $("#attach_users_role_form").serialize(),
                success: function (response) {
                    if(response.success) {
                        $("#attach_users_role_form").trigger('reset');
                        location.reload();
                    } else {
                        removeWait($('body'));
                        Swal.fire('Произошла ошибка!', errorMessage, 'error');
                    }
                },
                error: function (response) {
                    let errorMessage = '';
                    if (response.status === 422) {
                        const errors = response.responseJSON.errors;
                        for (const key in errors) {
                            errorMessage += `<p>${errors[key][0]}</p>`;
                        }
                    }
                    removeWait($('body'));
                    Swal.fire('Произошла ошибка!', errorMessage, 'error');
                },
            })
        })

        //detach user from role
        $(document).on('click', '.detach_user', function () {
            let user_id = $(this).data('id');
            Swal.fire({
                html: `Открепить сотрудника от роли?`,
                icon: "info",
                buttonsStyling: false,
                showCancelButton: true,
                confirmButtonText: "Да",
                cancelButtonText: 'Нет',
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: 'btn fw-bold btn-active-light-primary'
                }
            }).then(async (result) => {
                if (result.value) {
                    try {
                        applyWait($('body'));
                        const response = await $.ajax({
                            url: "{{route('cabinet.dept.roles.detach_user', $role)}}",
                            method: "POST",
                            headers: {'X-CSRF-TOKEN': token},
                            data: {'user_id': user_id},
                            success: function (response) {
                                removeWait($('body'));
                                if(response.success) {
                                    $('.user_row_' + user_id).fadeOut(200, function() {
                                        $(this).remove();
                                    });
                                } else {
                                    removeWait($('body'));
                                    Swal.fire('Произошла ошибка!', '{{trans('common.swal.error_text')}}', 'error');
                                }
                            },
                            error: function (response) {
                                removeWait($('body'));
                                Swal.fire('Произошла ошибка!', '{{trans('common.swal.error_text')}}', 'error');
                            },
                        });
                        //console.log(response.status)
                    } catch (error) {
                        removeWait($('body'));
                        Swal.fire('Произошла ошибка!', '{{trans('common.swal.error_text')}}', 'error');
                    }
                }
            });
        });

        // updated role function
        let form = $('#kt_modal_update_role_form');
        let modal = $('#kt_modal_update_role');
        let button = $('#kt_modal_update_role_submit_btn');
        let id = $('#role_id').val();

        button.on('click', function (e) {
            e.preventDefault();
            applyWait($('body'));
            $.ajax({
                url: "{{route('cabinet.dept.roles.update', $role)}}",
                method: 'post',
                headers: {'X-CSRF-TOKEN': token},
                data: form.serialize(),
                success: function (response) {
                    if(response.success) {
                        location.reload();
                    } else {
                        removeWait($('body'));
                        Swal.fire('Произошла ошибка!', '{{trans('common.swal.error_text')}}', 'error');
                    }
                },
                error: function (response) {
                    removeWait($('body'));
                    Swal.fire('Произошла ошибка!', '{{trans('common.swal.error_text')}}', 'error');
                },
            });
        });
        @endcan
    </script>
@endpush
