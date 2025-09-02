@php
    $visibleUsers = \App\Models\User::excludeFired()->where('visible', true)->get();
@endphp
<div class="modal fade" id="edit_ticket_modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <form id="edit_ticket_modal_form" class="form"
                      action=""
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="temp_folder" id="hidden_temp_folder">
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Редактирование тикета</h1>
                    </div>
                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                            <span class="required">{{trans('tickets.create_modal.description')}}</span>
                            <span class="ms-2" data-bs-toggle="tooltip" title="{{trans('tickets.create_modal.description_hint')}}">
                            <i class="ki-outline ki-information fs-7"></i>
                        </span>
                        </label>
                        <textarea class="form-control form-control-solid"
                                  rows="4"
                                  name="text"
                                  placeholder="{{trans('tickets.create_modal.description_placeholder')}}">{{$ticket->text}}</textarea>
                    </div>

                    <div class="row g-9 mb-8">

                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">{{trans('tickets.create_modal.department')}}</label>
                                <select class="form-select form-select-solid"
                                        data-control="select2"
                                        data-placeholder="{{trans('tickets.create_modal.select_from_list')}}"
                                        data-dropdown-parent="#edit_ticket_modal"
                                        data-department-id="{{ auth()->user()->getDepartmentId() }}"
                                        name="department">
                                    <option value=""></option>
                                    @foreach($departments as $item)
                                        <option value="{{$item->id}}"
                                            @selected(old('department', $ticket->department->id) == $item->id)
                                        >{{$item->name}}</option>
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
                                        <option value="{{$item->id}}"
                                            @selected(old('priority', $ticket->priority->id) == $item->id)
                                        >{{$item->getNameByLocale()}}</option>
                                    @endforeach
                                </select>
                            </div>
                    </div>

                    @if($ticket->media->isNotEmpty())
                        <div class="my-15" id="ticket-files-list">
                            @foreach($ticket->media as $item)
                                <div class="d-flex flex-aligns-center pe-10 pe-lg-20 mb-3 file-entry" data-file-path="{{$ticket->id.'/'.$item->unique_filename.'.'.$item->extension}}">
                                    <img class="w-40px me-3" src="{{asset('assets/media/extensions/'.$item->extension.'.png')}}" alt="">
                                    <div class="ms-1 fw-semibold">
                                        <a href="{{asset('storage/uploads/tickets/'.$ticket->id.'/'.$item->unique_filename.'.'.$item->extension)}}" class="fs-6 text-hover-primary fw-bold" target="_blank">
                                            {{$item->filename}}
                                        </a>
                                        <div class="text-gray-500">
                                            {{bytes_to_mb($item->size)}}
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);" class="ms-1 delete-temp-file-btn">
                                        <i class="ki-outline ki-cross text-danger fs-1"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <input type="hidden" name="files_to_delete" id="files-to-delete" value="[]">
                    @endif

                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2">{{trans('tickets.create_modal.attachments')}}</label>
                        <input type="file" class="edit-ticket-pond" name="media" multiple />
                    </div>

                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{trans('tickets.buttons.close')}}</button>
                        <button type="submit" id="edit_ticket_form_submit" class="btn btn-primary">
                            <span class="indicator-label">{{trans('common.roles.buttons.save')}}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('modal_js')
    <script>
        const editFormUploadManager = FileUploadManager.create({
            pondSelector: '.edit-ticket-pond',
            submitBtnSelector: '#edit_ticket_form_submit',
            formSelector: '#edit_ticket_modal_form',
            modalSelector: '#edit_ticket_modal',

            uploadRoute: '{{ route('cabinet.files.upload') }}',
            deleteRoute: '{{ route('cabinet.files.delete') }}',
            deleteTempFolderRoute: '{{ route('cabinet.files.delete-temp-folder') }}',

            csrfToken: '{{ csrf_token() }}',
            requireFilesForSubmit: false,

            labelFileTypeNotAllowed: '{{ trans('tickets.create_modal.format_error') }}',
            labelMaxFileSizeExceeded: '{{ trans('tickets.create_modal.size_limit') }}',
            labelIdle: '{{ trans('tickets.create_modal.attachments_hint') }}'
        });


        // удаление файлов из DOM
        let filesToDelete = [];

        $(document).on('click', '.delete-temp-file-btn', function () {
            const $fileEntry = $(this).closest('.file-entry');
            const filePath = $fileEntry.data('file-path');

            filesToDelete.push(filePath);
            $('#files-to-delete').val(JSON.stringify(filesToDelete));

            $fileEntry.remove();

            console.log(filesToDelete)
        });

        // Обработка отправки формы
        $('#edit_ticket_form_submit').on('click', function (e) {
            e.preventDefault();

            const $form = $('#edit_ticket_modal_form');
            const formData = new FormData($form[0]);

            $.ajax({
                url: $form.attr('action'), // Убедись, что action установлен динамически
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function () {
                    $('#edit_ticket_modal').modal('hide');
                },
                error: function () {
                    alert('Ошибка при сохранении');
                }
            });
        });

        let originalFilesHtml = '';

        $('#edit_ticket_modal').on('shown.bs.modal', function () {
            if (!originalFilesHtml) {
                originalFilesHtml = $('#ticket-files-list').html();
            }
            // Восстанавливаем исходный список
            $('#ticket-files-list').html(originalFilesHtml);

            // Обнуляем массив удалённых файлов
            filesToDelete = [];
            $('#files-to-delete').val('[]');
        });
    </script>
@endpush
