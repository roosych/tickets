<div class="modal fade" id="update_deadline_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">
                    {{trans('tickets.deadline_modal_title')}}
                </h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary"  data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="update_deadline_form" class="form" action="{{route('cabinet.tickets.update_deadline', $ticket)}}" method="POST">
                    @csrf
                    <input id="update_deadline_id" type="hidden" value="{{$ticket->id}}" name="ticket_id">
                    <div class="fv-row mb-8">
                        <label class="required fs-6 fw-semibold mb-2">{{trans('tickets.create_modal.deadline_label')}}</label>
                        <input class="form-control form-control-solid flatpickr"
                               name="due_date"
                               placeholder="{{trans('tickets.create_modal.pick_date')}}"/>
                    </div>
                    <div class="fv-row">
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span class="required">
                                {{trans('tickets.cancel_modal.comment_label')}}
                            </span>
                        </label>
                        <textarea class="form-control form-control-solid mb-4" rows="4" placeholder="{{trans('tickets.cancel_modal.comment_placeholder')}}" name="deadline_comment"></textarea>
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">
                            {{trans('common.roles.buttons.cancel')}}
                        </button>
                        <button type="button" class="btn btn-primary" id="update_deadline_submit">
                            {{trans('common.roles.buttons.save')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('custom_js')
    <script>
        $(document).ready(function() {
            $('#update_deadline_modal').modal({
                focus: false
            });

            $(".flatpickr").flatpickr({
                enableTime: true,
                time_24hr: true,
                dateFormat: "d-m-Y H:i",
                minDate: "today",
                defaultHour: 17,
                defaultMinute: 59,
                locale: { firstDayOfWeek: 1 },
                onChange: function(selectedDates, dateStr, instance) {
                    instance.close();
                }
            });

            $('#update_deadline_submit').click(function (e) {
                e.preventDefault();
                applyWait($('body'));
                $.ajax({
                    url: "{{route('cabinet.tickets.update_deadline', $ticket)}}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
                    },
                    data: $('#update_deadline_form').serialize(),
                    success: function (response) {
                        $('#update_deadline_modal').modal('hide');
                        if(response.success) {
                            Swal.fire('{{trans('common.swal.success_title')}}', '{{trans('common.swal.success_text')}}', 'success')
                                .then(() => {
                                    window.location.reload();
                                });
                        } else {
                            Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
                        }
                    },
                    error: function (response) {
                        $('.flatpickr-input').blur();
                        const message = getAjaxErrorMessage(response);
                        Swal.fire('{{trans('common.swal.error_title')}}', message, 'error');
                    },
                    complete: function () {
                        removeWait($('body'));
                    }
                });
            });
        });

    </script>
@endpush
