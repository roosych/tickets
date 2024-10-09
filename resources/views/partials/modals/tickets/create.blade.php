<div class="modal fade" id="kt_modal_new_ticket" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <form id="kt_modal_new_ticket_form" class="form"
                      action="{{route('cabinet.tickets.store')}}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Новый тикет</h1>
                    </div>
                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span class="required">Описание</span>
                            <span class="ms-2" data-bs-toggle="tooltip" title="Чем более подробно описан икет, тем быстрее он решается :)">
                            <i class="ki-outline ki-information fs-7"></i>
                        </span>
                        </label>
                        <textarea class="form-control form-control-solid" rows="4" name="text" placeholder="Опишите ситуацию, задачу или проблему"></textarea>
                    </div>

                    <div class="row g-9 mb-8">


                        @if(\Illuminate\Support\Facades\Route::is('cabinet.tickets.show'))
                            <input type="hidden" name="department" value="{{$ticket->department->id}}">
                            <input type="hidden" name="parent_id" value="{{$ticket->id}}">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Сотрудник</label>
                                <select class="form-select form-select-solid"
                                        data-control="select2"
                                        data-hide-search="true"
                                        data-placeholder="Выберите из списка..." name="user">
                                    <option value=""></option>
                                    @foreach(auth()->user()->deptAllUsers() as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Отдел</label>
                                <select class="form-select form-select-solid"
                                        data-control="select2"
                                        data-placeholder="Выберите из списка..."
                                        data-dropdown-parent="#kt_modal_new_ticket"
                                        data-department-id="{{ auth()->user()->getDepartmentId() }}"
                                        name="department">
                                    <option value=""></option>
                                    @foreach($departments as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Приоритет</label>
                                <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Выберите из списка..." name="priority">
                                    <option value=""></option>
                                    @foreach($priorities as $item)
                                        <option value="{{$item->id}}">{{$item->getNameByLocale()}}</option>
                                    @endforeach
                                </select>
                            </div>
                    </div>

                    @can('assign', \App\Models\Ticket::class)
                        <div id="dept_users_list" style="display: none;">
                            <div class="col-md-12 fv-row">
                                <label class="fs-6 fw-semibold mb-2">Сотрудник</label>
                                <select class="form-select form-select-solid"
                                        data-control="select2"
                                        data-placeholder="Выберите из списка..."
                                        data-dropdown-parent="#kt_modal_new_ticket"
                                        name="user">
                                    <option value=""></option>
                                    @foreach(auth()->user()->deptAllUsers() as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endcan

                    @php
                        $deptTags = auth()->user()->getDepartment()->tags()->get();
                    @endphp

                    @if(count($deptTags) > 0)
                        <div class="my-10" id="tagsSelect" style="display: none;">
                            <label class="fs-6 fw-semibold mb-2">Теги</label>
                            <select class="form-select form-select-solid"
                                    name="tags[]"
                                    data-control="select2"
                                    data-close-on-select="false"
                                    data-placeholder="Выбрать теги"
                                    data-allow-clear="true"
                                    multiple="multiple">
                                <option></option>
                                @foreach($deptTags as $tag)
                                    <option value="{{$tag->id}}">
                                        {{$tag->text}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2">Вложения</label>
                        <input type="file" class="my-pond" name="media" multiple />
                    </div>

                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" id="create_ticket_form_submit" class="btn btn-primary">
                            <span class="indicator-label">Создать</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js_from_modal')
    <script>
        $(document).ready(function() {
            let userDepartmentId = $('select[name="department"]').data('department-id');

            $('select[name="department"]').change(function() {
                let selectedDepartmentId = $(this).val();

                if (selectedDepartmentId === userDepartmentId?.toString()) {
                    $('#tagsSelect').show(300);
                    $('#dept_users_list').show(300);
                } else {
                    $('#tagsSelect').hide(300);
                    $('#dept_users_list').hide(300);
                    $('select[name="tags[]"]').val([]).trigger('change');
                    $('select[name="user"]').val([]).trigger('change');
                }
            });
        });

    </script>
@endpush
