<div class="modal fade" id="cancel_ticket_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Отменить тикет</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary"  data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="cancel_ticket_form" class="form">
                    @csrf
                    <input id="cancel_ticket_id" type="hidden" value="" name="ticket_id">
                    <div class="fv-row">
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span class="required">Комментарий</span>
                        </label>
                        <textarea class="form-control form-control-solid mb-4" rows="4" placeholder="Введите текст" name="cancelled_comment"></textarea>
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary" id="cancel_ticket_submit">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
