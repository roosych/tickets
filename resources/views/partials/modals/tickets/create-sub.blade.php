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
                    <input type="hidden" name="temp_folder" id="hidden_temp_folder">
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">{{trans('tickets.create_modal.title')}}</h1>
                    </div>
                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span class="required">{{trans('tickets.create_modal.description')}}</span>
                            <span class="ms-2" data-bs-toggle="tooltip" title="{{trans('tickets.create_modal.description_hint')}}">
                            <i class="ki-outline ki-information fs-7"></i>
                        </span>
                        </label>
                        <textarea class="form-control form-control-solid" rows="4" name="text" placeholder="{{trans('tickets.create_modal.description_placeholder')}}"></textarea>
                    </div>

                    <div class="row g-9 mb-8">
                        <input type="hidden" name="department" value="{{$ticket->department->id}}">
                        <input type="hidden" name="parent_id" value="{{$ticket->id}}">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('tickets.create_modal.performer')}}</label>
                            <select class="form-select form-select-solid"
                                    data-control="select2"
                                    data-hide-search="true"
                                    data-placeholder="{{trans('tickets.create_modal.select_from_list')}}"
                                    name="user">
                                <option value=""></option>
                                @foreach(auth()->user()->deptAllUsers() as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">{{trans('tickets.create_modal.priority')}}</label>
                            <select class="form-select form-select-solid"
                                    data-control="select2"
                                    data-hide-search="true"
                                    data-placeholder="{{trans('tickets.create_modal.select_from_list')}}" name="priority">
                                <option value=""></option>
                                @foreach($priorities as $item)
                                    <option value="{{$item->id}}">{{$item->getNameByLocale()}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @php
                        $deptTags = auth()->user()->getDepartment()->tags()->get();
                    @endphp

                    @if(count($deptTags) > 0)
                        <div class="my-10" id="tagsSelect" style="display: none;">
                            <label class="fs-6 fw-semibold mb-2">{{trans('tickets.create_modal.tags')}}</label>
                            <select class="form-select form-select-solid"
                                    name="tags[]"
                                    data-control="select2"
                                    data-close-on-select="false"
                                    data-placeholder="{{trans('tickets.create_modal.select_from_list')}}"
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
                        <label class="fs-6 fw-semibold mb-2">{{trans('tickets.create_modal.attachments')}}</label>
                        <input type="file" class="my-pond" name="media" multiple />
                    </div>

                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{trans('tickets.buttons.close')}}</button>
                        <button type="submit" id="create_ticket_form_submit" class="btn btn-primary">
                            <span class="indicator-label">{{trans('tickets.buttons.create')}}</span>
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
