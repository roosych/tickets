@extends('layouts.app')

@section('title', 'Пользователь')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">
                {{trans('common.mainpage')}}
            </a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            Сотрудники
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">{{$user->name}}</li>
    </ul>
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row">
        <div class="flex-lg-row-fluid me-lg-15 order-2 order-lg-1 mb-10 mb-lg-0">
            <div class="card card-flush mb-6">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-5">
                        <i class="ki-outline ki-information-5 fs-2x text-primary me-4"></i>
                        <div>
                            <div class="fs-5 fw-bold text-gray-800">Сотрудник другого отдела</div>
                            <div class="text-gray-600">
                                Тикеты и показатели сотрудников других отделов не отображаются.
                                @if($department?->active)
                                    Вы можете создать тикет на отдел «{{$department->name}}».
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($department?->active)
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_new_ticket" data-department-id="{{$department->id}}" id="create_ticket_for_dept">
                            <i class="ki-outline ki-plus fs-2"></i>Создать тикет на отдел
                        </a>
                    @endif
                </div>
            </div>

            @if(auth()->user()->is_admin)
                <div class="card card-flush mb-6">
                    <div class="card-header pt-5">
                        <div class="card-title"><h2 class="mb-0">Права доступа</h2></div>
                    </div>
                    <div class="card-body pt-2">
                        <div class="fw-bold text-gray-800 mb-3">Роли</div>
                        @forelse($user->roles as $role)
                            <div class="mb-4">
                                <div class="fw-semibold text-gray-800 mb-2">{{$role->name}}</div>
                                @foreach($role->permissions as $permission)
                                    <span class="badge badge-light mb-1">{{$permission->group}} — {{$permission->name}}</span>
                                @endforeach
                            </div>
                        @empty
                            <div class="text-muted mb-4">Ролей нет</div>
                        @endforelse

                        <div class="separator separator-dashed my-5"></div>

                        <div class="fw-bold text-gray-800 mb-3">Прямые разрешения</div>
                        @forelse($user->permissions as $permission)
                            <span class="badge badge-light-primary mb-1">{{$permission->group}} — {{$permission->name}}</span>
                        @empty
                            <div class="text-muted">Прямых разрешений нет</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        <div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xl-300px mb-10 order-1 order-lg-2">
            <div class="card card-flush pt-3 mb-0">
                <div class="card-body pt-0 fs-6">
                    <div class="d-flex flex-center flex-column py-5">
                        <div class="symbol symbol-100px symbol-circle mb-7">
                            @if($user->avatar)
                                <img src="{{$user->avatar}}" alt="{{$user->name}}">
                            @else
                                <div class="symbol-label fs-3 bg-light-dark text-dark">
                                    {{get_initials($user->name)}}
                                </div>
                            @endif
                        </div>
                        <p class="fs-3 text-gray-800 fw-bold mb-3">{{$user->name}}</p>
                        <div class="mb-3">
                            <div class="badge badge-lg badge-light-secondary d-inline">{{$user->position}}</div>
                            @unless($user->active)
                                <div class="badge badge-lg badge-light-danger d-inline ms-1">Неактивен</div>
                            @endunless
                        </div>
                    </div>
                    <div class="separator separator-dashed mb-7"></div>
                    <div class="fs-6">
                        <div class="fw-bold mt-5">Отдел</div>
                        <div class="text-gray-600">{{$department?->name ?? $user->department}}</div>
                        <div class="fw-bold mt-5">Email</div>
                        <div class="text-gray-600">{{$user->email}}</div>
                        @if($user->mobile)
                            <div class="fw-bold mt-5">Номер</div>
                            <div class="text-gray-600">{{$user->mobile}}</div>
                        @endif
                        @if($user->pager)
                            <div class="fw-bold mt-5">3CX</div>
                            <div class="text-gray-600">{{$user->pager}}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/filepond.min.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/plugins/filepond.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.jquery.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-type.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-size.js')}}"></script>
@endpush

@if($department?->active)
    @push('modals')
        @include('partials.modals.tickets.create')
    @endpush

    @push('custom_js')
        <script src="{{asset('assets/js/custom/tickets/create.js')}}"></script>
        <script src="{{asset('assets/js/custom/initFileUpload.js')}}"></script>
        <script>
            FilePond.registerPlugin(FilePondPluginFileValidateType);
            FilePond.registerPlugin(FilePondPluginFileValidateSize);
            FileUploadManager.create({
                pondSelector: '.my-pond',
                submitBtnSelector: '#create_ticket_form_submit',
                formSelector: '#kt_modal_new_ticket_form',
                modalSelector: '#kt_modal_new_ticket',

                uploadRoute: '{{ route('cabinet.files.upload') }}',
                deleteRoute: '{{ route('cabinet.files.delete') }}',
                deleteTempFolderRoute: '{{ route('cabinet.files.delete-temp-folder') }}',

                csrfToken: '{{ csrf_token() }}',
                requireFilesForSubmit: false,

                labelFileTypeNotAllowed: '{{ trans('tickets.create_modal.format_error') }}',
                labelMaxFileSizeExceeded: '{{ trans('tickets.create_modal.size_limit') }}',
                labelIdle: '{{ trans('tickets.create_modal.attachments_hint') }}'
            });

            // отдел предвыбран по кнопке
            $('#kt_modal_new_ticket').on('show.bs.modal', function () {
                $(this).find('select[name="department"]').val($('#create_ticket_for_dept').data('department-id')).trigger('change');
            });

            $('#create_ticket_form_submit').click(function (e) {
                e.preventDefault();
                applyWait($('body'));
                $.ajax({
                    url: "{{route('cabinet.tickets.store')}}",
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')},
                    data: $('#kt_modal_new_ticket_form').serialize(),
                    success: function (response) {
                        removeWait($('body'));
                        if (response.status === 'success') {
                            Swal.fire('{{trans('common.swal.success_title')}}', '{{trans('common.swal.success_text')}}', 'success');
                            window.location.href = '{{route('cabinet.tickets.sent')}}';
                        } else {
                            Swal.fire('{{trans('common.swal.error_title')}}', '{{trans('common.swal.error_text')}}', 'error');
                        }
                    },
                    error: function (response) {
                        let errorMessage = '';
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            for (const key in errors) {
                                errorMessage += `<p class="mb-0">${errors[key][0]}</p>`;
                            }
                        }
                        removeWait($('body'));
                        Swal.fire('{{trans('common.swal.error_title')}}', errorMessage, 'error');
                    },
                });
            });
        </script>
        @stack('js_from_modal')
    @endpush
@endif
