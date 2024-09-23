<div class="modal fade" id="modal_add_tag" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Добавить тег</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body mx-lg-5 my-7">
                <form id="add_tag_form" class="form" method="POST">
                    @csrf
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-offset="300px">
                        <div class="row">
                            <label class="fs-5 fw-bold form-label mb-2">
                                <span class="required">Название</span>
                            </label>
                            <div class="fv-row mb-10">
                                <input class="form-control" placeholder="Название" name="text" autocomplete="false"/>
                            </div>
                        </div>
                        <div class="row">
                            <label class="fs-5 fw-bold form-label mb-2">
                                <span>Цвет</span>
                            </label>
                            <div class="fv-row mb-10">
                                <input class="form-control form-control-lg rounded-2" type="color" name="color" value="#000000" autocomplete="false"/>
                            </div>
                        </div>
                    </div>
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">
                            Отменить
                        </button>
                        <button id="add_tag_submit_btn" type="submit" class="btn btn-primary" data-kt-roles-modal-action="submit">
                            <span class="indicator-label">Сохранить</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
