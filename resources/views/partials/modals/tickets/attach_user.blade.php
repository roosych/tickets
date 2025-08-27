<div class="modal fade" id="attach_users_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-650px">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-18 pt-0 pb-15">
                <form id="attach_users_form" method="POST">
                    @csrf
                    <input type="hidden" name="ticket_id" id="ticket-id">
                    <div class="text-center mb-13">
                        <h1 class="mb-3">
                            {{trans('tickets.table.dept_users')}}
                        </h1>
                    </div>

                    <div id="modal_content">
                        <div class="mb-10">
                            <div class="mh-300px scroll-y me-n7 pe-7">
                                @foreach(auth()->user()->deptAllUsers() as $user)
                                    <div class="d-flex flex-stack py-4 border-bottom border-gray-300 border-bottom-dashed">
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px symbol-circle">
                                                @if($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-100" />
                                                @else
                                                    <div class="symbol-label fs-3 bg-light-dark text-dark">
                                                        {{ get_initials($user->name) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ms-5">
                                                <p class="fs-5 fw-bold text-gray-900 mb-0">{{ $user->name }}</p>
                                                <span class="badge badge-light">{{ $user->position }}</span>
                                                {{--<div class="fw-semibold text-muted">{{ $user->email }}</div>--}}
                                            </div>
                                        </div>
                                        <div class="ms-2 text-end">
                                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                <span class="spinner-border spinner-border-sm d-inline-block"></span>
                                                <input class="form-check-input user-radio d-none"
                                                       type="radio" name="performer_id"
                                                       value="{{ $user->id }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
{{--
                        @if(! $ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED))
--}}
                            <div class="text-center">
                                <button id="attach_user_submit" type="submit" class="btn btn-primary">
                                    {{trans('common.roles.buttons.save')}}
                                </button>
                            </div>
{{--
                        @endif
--}}

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
