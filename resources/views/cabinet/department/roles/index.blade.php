@extends('layouts.app')

@section('title', 'Роли')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">Главная</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">Роли</li>
    </ul>
@endsection

@section('content')

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 g-xl-9">
    <div class="col-md-4">
        <div class="card h-md-100">
            <div class="card-body d-flex flex-center">
                <button type="button" class="btn btn-clear d-flex flex-column flex-center" data-bs-toggle="modal" data-bs-target="#kt_modal_add_role">
                    <img src="{{asset('assets/media/misc/6.svg')}}" alt="" class="mw-100 mh-150px mb-7">

                    <div class="fw-bold fs-3 text-gray-600 text-hover-primary">
                        Добавить роль
                    </div>
                </button>
            </div>
        </div>
    </div>
    @foreach($roles as $role)
        <x-roles-list-component :role="$role"></x-roles-list-component>
    @endforeach
</div>

@include('partials.modals.roles.add_role')

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
    <script>
        // check all checkbox
        $("#kt_roles_select_all").click(function() {
            $(".form-check-input").prop("checked", $(this).prop("checked"));
        });
        $(".form-check-input").click(function() {
            if (!$(this).prop("checked")) {
                $("#kt_roles_select_all").prop("checked", false);
            }
        });


        //add role
        let form = $('#kt_modal_add_role_form');
        let modal = $('#kt_modal_add_role');
        let button = $('#kt_modal_add_role_submit_btn');
        let token = $('meta[name="csrf-token"]').attr('content');

        button.on('click', function(e){
            e.preventDefault();
            applyWait($('body'));
            $.ajax({
                url: '{{route('cabinet.dept.roles.store')}}',
                method: 'POST',
                headers: {'X-CSRF-TOKEN': token},
                data: form.serialize(),
                success: function(response)
                {
                    removeWait($('body'));
                    if(response.success) {
                        form.trigger('reset');
                        modal.modal('toggle');
                        location.reload();
                    } else {
                        removeWait($('body'));
                        Swal.fire('Ошибка!', 'Что-то пошло не так', 'error');
                    }
                },
                error: function (response)
                {
                    removeWait($('body'));
                    let errorMessage = '';
                    if (response.status === 422) {
                        const errors = response.responseJSON.errors;
                        for (const key in errors) {
                            errorMessage += `<p>${errors[key][0]}</p>`;
                        }
                    }
                    Swal.fire('Ошибка!', errorMessage, 'error');
                },
            });
        });

        //delete role
        $(".delete_role").on('click', function (){
            let roleId = $(this).data('id');
            let roleName = $(this).data('name');
            let item = $('.role_card_item_' + roleId);
            Swal.fire({
                html: `Роль будет удалена, Вы уверены?`,
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
                if(result.value) {
                    try {
                        applyWait($('body'));
                        const response = await $.ajax({
                            url: '{{ route('cabinet.dept.roles.delete', ':id') }}'.replace(':id', roleId),
                            method: "DELETE",
                            headers: {'X-CSRF-TOKEN': token},
                            success: function(response)
                            {
                                if (response.success) {
                                    removeWait($('body'));
                                    Swal.fire('Всё прошло успешно!', 'Роль <b>"' + roleName + '"</b> удалена.', 'success');
                                    setTimeout(function(){
                                        Swal.close();
                                        item.remove();
                                    }, 1000)
                                } else {
                                    removeWait($('body'));
                                    Swal.fire('Произошла ошибка!', '{{trans('common.swal.error_text')}}', 'error');
                                }
                            },
                            error: function (response)
                            {
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
    </script>
@endpush
