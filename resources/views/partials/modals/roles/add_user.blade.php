<div class="modal fade" id="attach_users_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog mw-650px">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-18 pt-0 pb-15">
                <form id="attach_users_role_form" method="POST">
                    @csrf
                    <div class="text-center mb-13">
                        <h1 class="mb-3">Добавить сотрудников</h1>
                    </div>
                    @if(count($employees))
                        <div class="mb-10">
                            <div class="fs-6 fw-semibold mb-2">Сотрудники моего отдела</div>
                            <div class="mh-300px scroll-y me-n7 pe-7">
                                @foreach($employees as $employee)
                                    <div class="d-flex flex-stack py-4 border-bottom border-gray-300 border-bottom-dashed">
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px symbol-circle">
                                                @if($employee->avatar)
                                                    <img src="{{$employee->avatar}}" alt="{{$employee->name}}" class="w-100" />
                                                @else
                                                    <div class="symbol-label symbol-35px fs-3 bg-light-primary text-primary">
                                                        {{mb_substr($employee->name, 0, 1, 'UTF-8')}}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ms-5">
                                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary mb-2">{{$employee->name}}</a>
                                                <div class="fw-semibold text-muted">{{$employee->email}}</div>
                                            </div>
                                        </div>
                                        <div class="ms-2 text-end">
                                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" name="users[]" value="{{$employee->id}}"
                                                    {{ is_array($role->users->pluck('id')->toArray())
                                                     &&
                                                     in_array($employee->id, $role->users->pluck('id')->toArray())
                                                      ? 'checked' : '' }}
                                                >
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="text-center">
                            <button id="attach_form_submit_btn" type="submit" class="btn btn-primary">
                                Добавить
                            </button>
                        </div>
                    @else
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed rounded-3 p-6">
                            <div class="d-flex flex-stack flex-grow-1 ">
                                <div class=" fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">
                                        В Вашем отделе нет сотрудников
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
