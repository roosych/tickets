<div class="modal fade" id="approved_users_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog mw-650px">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-18 pt-0 pb-15">
                <form id="approved_users_form" method="POST" action="{{ route('cabinet.tickets.approval.store', $ticket) }}">
                    @csrf
                    <div class="text-center mb-13">
                        <h1 class="mb-3">Запрос на одобрение</h1>
                    </div>

                    @if(count($approvers))
                        @php
                            $existingRequests = $ticket->approvalRequests->keyBy('approver_id');
                            $availableApprovers = collect($approvers)->filter(fn($user) => !isset($existingRequests[$user->id]));
                        @endphp

                        <div class="mb-10">
                            <div class="mh-300px scroll-y me-n7 pe-7">
                                @foreach($approvers as $employee)
                                    <div class="d-flex flex-stack py-4 border-bottom border-gray-300 border-bottom-dashed">
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px symbol-circle">
                                                @if($employee->avatar)
                                                    <img src="{{ $employee->avatar }}" alt="{{ $employee->name }}" class="w-100" />
                                                @else
                                                    <div class="symbol-label symbol-35px fs-3 bg-light-primary text-primary">
                                                        {{ mb_substr($employee->name, 0, 1, 'UTF-8') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ms-5">
                                                <p class="fs-5 fw-bold text-gray-900 text-hover-primary mb-0">
                                                    {{ $employee->name }}
                                                </p>
                                                <div class="fw-semibold text-muted">
                                                    {{ $employee->email }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ms-2 text-end">
                                            @if(isset($existingRequests[$employee->id]))
                                                @php
                                                    $status = $existingRequests[$employee->id]->status;
                                                @endphp
                                                <span class="badge badge-light-{{ $status->color() }} fw-bold fs-7">
                                                    {{ $status->label() }}
                                                </span>
                                            @else
                                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                    <input
                                                        class="form-check-input approval-checkbox"
                                                        type="checkbox"
                                                        name="approvers[]"
                                                        value="{{ $employee->id }}"
                                                        id="approver_{{ $employee->id }}"
                                                    />
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($availableApprovers->isNotEmpty())
                                <label class="d-flex align-items-center fs-6 fw-semibold mb-2 mt-8">
                                    <span>Комментарий</span>
                                </label>
                                <textarea class="form-control form-control-solid" name="approval_request_comment" rows="3"></textarea>

                                <div class="text-center mt-5">
                                    <button id="approved_users_form_submit_btn" type="submit" class="btn btn-primary">
                                        <i class="ki-outline ki-send fs-2"></i>
                                        Отправить
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed rounded-3 p-6">
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold mb-0">
                                        Users with approve permission not found.
                                    </h4>
                                </div>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
