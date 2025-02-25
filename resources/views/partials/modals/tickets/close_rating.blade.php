<div class="modal fade" id="close_ticket_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">
                    {{trans('tickets.buttons.close')}}
                </h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary"  data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 mb-7">
                <div class="fs-6 fw-semibold text-muted text-center">
                    {{trans('tickets.raiting_title')}}
                </div>
                <form id="close_ticket_form" class="form">
                    @csrf
                    <input id="close_ticket_id" type="hidden" value="" name="ticket_id">
                    <div class="rating-stars d-flex justify-content-center my-5">
                        <input class="rating-input" name="rating" value="0" checked type="radio" id="kt_rating_input_0"/>

                        <label class="rating-label" for="kt_rating_input_1">
                            <i class="ki-duotone ki-star fs-3hx"></i>
                        </label>
                        <input class="rating-input" name="rating" value="1" type="radio" id="kt_rating_input_1"/>

                        <label class="rating-label" for="kt_rating_input_2">
                            <i class="ki-duotone ki-star fs-3hx"></i>
                        </label>
                        <input class="rating-input" name="rating" value="2" type="radio" id="kt_rating_input_2"/>

                        <label class="rating-label" for="kt_rating_input_3">
                            <i class="ki-duotone ki-star fs-3hx"></i>
                        </label>
                        <input class="rating-input" name="rating" value="3" type="radio" id="kt_rating_input_3"/>

                        <label class="rating-label" for="kt_rating_input_4">
                            <i class="ki-duotone ki-star fs-3hx"></i>
                        </label>
                        <input class="rating-input" name="rating" value="4" type="radio" id="kt_rating_input_4"/>

                        <label class="rating-label" for="kt_rating_input_5">
                            <i class="ki-duotone ki-star fs-3hx"></i>
                        </label>
                        <input class="rating-input" name="rating" value="5" type="radio" id="kt_rating_input_5"/>
                    </div>

                    <div class="fv-row">
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span class="required">
                                {{trans('tickets.cancel_modal.comment_label')}}
                            </span>
                        </label>
                        <textarea class="form-control form-control-solid mb-4"
                              rows="4"
                              placeholder="{{trans('tickets.cancel_modal.comment_placeholder')}}"
                              name="comment"></textarea>
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">
                            {{trans('common.roles.buttons.cancel')}}
                        </button>
                        <button type="button" class="btn btn-primary" id="close_ticket_submit" data-id="{{ optional($ticket)->id }}">
                            {{trans('common.roles.buttons.save')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
