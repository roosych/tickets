<div class="modal fade" id="kt_modal_add_role" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-850px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Добавить роль</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body mx-lg-5 my-7">
                <form id="kt_modal_add_role_form" class="form" method="POST" action="">
                    @csrf
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_role_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_role_header" data-kt-scroll-wrappers="#kt_modal_add_role_scroll" data-kt-scroll-offset="300px">
                        <div class="row">
                            <label class="fs-5 fw-bold form-label mb-2">
                                <span class="required">Название</span>
                            </label>
                            <div class="fv-row mb-10">
                                <input class="form-control" placeholder="Название" name="name" autocomplete="false"/>
                            </div>
                        </div>
                        <div class="fv-row">
                            <label class="fs-5 fw-bold form-label mb-2">
                                Разрешения
                            </label>
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <tbody class="text-gray-600 fw-semibold">
                                    <tr>
                                        <td class="fw-bold text-gray-800">
                                            Полный доступ
                                            <span class="ms-2" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="Доступ на все функции">
                                                <i class="ki-outline ki-information-5 fs-7"></i>
                                            </span>
                                        </td>
                                        <td>
                                            <label class="form-check form-check-sm form-check-custom form-check-solid me-9">
                                                <input class="form-check-input" type="checkbox" value="" id="kt_roles_select_all" />
                                                <span class="form-check-label">
                                                    Выбрать все
                                                </span>
                                            </label>
                                        </td>
                                    </tr>

                                    @foreach($groupedPermissions as $group => $permissions)
                                        <tr>
                                            <td class="text-gray-800">{{$group}}</td>
                                            <td>
                                                <div class="d-flex">
                                                    @foreach($permissions as $permission)
                                                        <!--begin::Checkbox-->
                                                        <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20">
                                                            <input class="form-check-input" type="checkbox" value="{{$permission->id}}" name="permissions[]" />
                                                            <span class="form-check-label">{{$permission->name}}</span>
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
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">
                            Отменить
                        </button>
                        <button id="kt_modal_add_role_submit_btn" type="submit" class="btn btn-primary" data-kt-roles-modal-action="submit">
                            <span class="indicator-label">Сохранить</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
