@extends('layouts.app')

@section('title', $department->name)

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">Главная</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.settings.departments')}}" class="text-muted text-hover-primary">Департаменты</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">{{$department->name}}</li>
    </ul>
@endsection

@section('content')
    @foreach(['success' => 'success', 'error' => 'danger'] as $key => $color)
        @if(session($key))
            <div class="alert alert-{{$color}} d-flex align-items-center mb-6">
                <i class="ki-outline ki-{{$key === 'success' ? 'check-circle' : 'information-5'}} fs-2 text-{{$color}} me-3"></i>
                <div>{{session($key)}}</div>
            </div>
        @endif
    @endforeach

    <div class="row g-5 mb-6">
        @foreach([
            ['Сотрудников', $stats['members_active'] . ($stats['members_total'] != $stats['members_active'] ? ' / ' . $stats['members_total'] : ''), 'gray-800'],
            ['Открытых тикетов', $stats['open_tickets'], $stats['open_tickets'] ? 'primary' : 'gray-800'],
            ['Тегов', $stats['tags'], 'gray-800'],
            ['Ролей', $stats['roles'], 'gray-800'],
            ['Исполняет тикеты', $department->active ? 'Да' : 'Нет', $department->active ? 'success' : 'gray-800'],
        ] as [$label, $value, $color])
            <div class="col-6 col-xl">
                <div class="card card-flush h-100">
                    <div class="card-body py-5">
                        <div class="fs-2 fw-bold text-{{$color}} lh-1 mb-2">{{$value}}</div>
                        <div class="text-gray-600 fw-semibold fs-7">{{$label}}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <form id="department_form" class="form d-flex flex-column flex-lg-row"
          method="POST"
          action="{{route('cabinet.settings.departments.store', $department)}}"
          data-manager-id="{{$department->manager_id}}">
        @csrf
        <div class="w-100 flex-lg-row-auto w-lg-350px mb-7 me-7 me-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title"><h2>Основное</h2></div>
                </div>

                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-6">
                        <div class="fv-row">
                            <label class="required form-label">Название</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $department->name) }}">
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="fv-row">
                            <label class="form-label">Менеджер</label>
                            <select class="form-select @error('manager_id') is-invalid @enderror" data-control="select2"
                                    data-placeholder="Выбрать из списка" data-allow-clear="true"
                                    name="manager_id" id="department_manager">
                                <option></option>
                                @foreach($users->filter(fn ($u) => $u->active || $u->id == $department->manager_id) as $user)
                                    <option value="{{ $user->id }}" {{ $user->id == old('manager_id', $department->manager_id) ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('manager_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="revoke_wrapper" class="d-none mt-3">
                                <input type="hidden" name="revoke_old_manager" value="0">
                                <label class="form-check form-check-custom form-check-solid form-check-sm">
                                    <input class="form-check-input" type="checkbox" name="revoke_old_manager" value="1" checked>
                                    <span class="form-check-label text-gray-700">Снять права у прежнего менеджера</span>
                                </label>
                                <div class="text-muted fs-8 mt-1">Новый менеджер получит все права.</div>
                            </div>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="form-label mb-0">Исполняет тикеты</label>
                                    <div class="text-muted fs-8">Отмечается звёздочкой, на отдел можно создавать тикеты</div>
                                </div>
                                <input type="hidden" name="active" value="0">
                                <div class="form-check form-check-solid form-check-custom form-switch">
                                    <input class="form-check-input w-45px h-30px" type="checkbox" name="active" value="1"
                                           {{ old('active', $department->active) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <div class="separator separator-dashed"></div>

                        <div class="fv-row">
                            <div class="fw-bold text-gray-800 mb-3"><i class="ki-outline ki-send fs-3 me-1"></i>Telegram-уведомления</div>
                            <label class="form-label">ID чата</label>
                            <input type="text" class="form-control mb-4 @error('tg_chat_id') is-invalid @enderror"
                                   name="tg_chat_id" value="{{ old('tg_chat_id', $department->tg_chat_id) }}" placeholder="-100123456789">
                            <label class="form-label">ID темы</label>
                            <input type="text" class="form-control @error('tg_topic_id') is-invalid @enderror"
                                   name="tg_topic_id" value="{{ old('tg_topic_id', $department->tg_topic_id) }}" placeholder="необязательно">
                            @if(! $department->tg_chat_id)
                                <div class="text-muted fs-8 mt-2">Чат не задан — уведомления о новых тикетах отдела не отправляются.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush mt-7">
                <div class="card-header"><div class="card-title"><h2 class="text-danger">Удаление</h2></div></div>
                <div class="card-body pt-0">
                    @if($deleteBlockers)
                        <div class="text-muted fs-7 mb-4">Удалить нельзя, пока есть: {{implode(', ', $deleteBlockers)}}.</div>
                    @endif
                    <button type="button" id="delete_department_btn" class="btn btn-light-danger" {{ $deleteBlockers ? 'disabled' : '' }}>
                        <i class="ki-outline ki-trash fs-3"></i>Удалить департамент
                    </button>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-lg-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header d-flex align-items-center justify-content-between mb-3">
                    <div class="card-title"><h2>Сотрудники департамента</h2></div>
                    <button type="submit" class="btn btn-primary ms-auto">Сохранить</button>
                </div>

                <div class="card-body pt-0">
                    <div class="text-muted fs-7 mb-4">
                        В списке слева — доступные сотрудники, справа — состав отдела. Неактивные и скрытые сотрудники отдела тоже показаны, чтобы не потерять их при сохранении.
                    </div>
                    <select multiple="multiple" size="10" name="users[]" title="users[]">
                        @foreach($users as $user)
                            @if($user->id == $department->manager_id)
                                @continue
                            @endif
                            @php
                                $isMember = $user->department_id == $department->id;
                                $label = $user->name
                                    . (! $user->active ? ' · неактивен' : '')
                                    . (! $user->visible ? ' · скрыт' : '')
                                    . (! $isMember && $user->departmentRelation ? ' · ' . $user->departmentRelation->name : '')
                                    . ($user->open_tickets_count ? ' · тикетов: ' . $user->open_tickets_count : '');
                            @endphp
                            <option value="{{$user->id}}"
                                    data-name="{{$user->name}}"
                                    data-open="{{$user->open_tickets_count}}"
                                    data-member="{{(int) $isMember}}"
                                    data-other-dept="{{ ! $isMember && $user->departmentRelation ? $user->departmentRelation->name : '' }}"
                                    {{$isMember ? 'selected' : ''}}>
                                {{$label}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </form>

    <form id="delete_department_form" method="POST" action="{{route('cabinet.settings.departments.destroy', $department)}}">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .btn-group {
            display: none !important;
        }
    </style>
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('modals')

@endpush

@push('custom_js')
    <script src="{{asset('assets/js/plugins/jquery.bootstrap-duallistbox.js')}}"></script>
    <script>
        $('select[name="users[]"]').bootstrapDualListbox({
            nonSelectedListLabel: 'Доступные сотрудники',
            selectedListLabel: 'Состав отдела',
            preserveSelectionOnMove: 'moved',
            infoText: false,
            filterPlaceHolder: 'Поиск...',
            filterTextClear: false,
        });

        const $form = $('#department_form');
        const initialManager = String($form.data('manager-id') || '');

        $('#department_manager').on('change', function () {
            const changed = String($(this).val() || '') !== initialManager;
            $('#revoke_wrapper').toggleClass('d-none', !changed || !initialManager);
        });

        function escapeHtml(t) {
            return $('<div>').text(t).html();
        }

        let confirmed = false;
        $form.on('submit', function (e) {
            if (confirmed) return;

            const warnings = [];
            const removedWithTickets = [];
            const moved = [];

            $('select[name="users[]"] option').each(function () {
                const $o = $(this);
                const isMember = $o.data('member') === 1;
                if (isMember && !this.selected && Number($o.data('open')) > 0) {
                    removedWithTickets.push(escapeHtml($o.data('name')) + ' (' + $o.data('open') + ')');
                }
                if (!isMember && this.selected && $o.data('other-dept')) {
                    moved.push(escapeHtml($o.data('name')) + ' ← ' + escapeHtml($o.data('other-dept')));
                }
            });

            if (removedWithTickets.length) {
                warnings.push('<b>Уходят из отдела с открытыми тикетами:</b><br>' + removedWithTickets.join('<br>'));
            }
            if (moved.length) {
                warnings.push('<b>Переносятся из других отделов:</b><br>' + moved.join('<br>'));
            }
            const newManager = String($('#department_manager').val() || '');
            if (newManager !== initialManager) {
                const revoke = $('input[name="revoke_old_manager"][type="checkbox"]').prop('checked') && initialManager;
                warnings.push('<b>Меняется менеджер.</b> Новый получит все права' + (revoke ? ', у прежнего права будут сняты.' : '.'));
            }

            if (!warnings.length) return;

            e.preventDefault();
            Swal.fire({
                html: '<div class="text-start">' + warnings.join('<br><br>') + '</div>',
                icon: 'warning',
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: 'Сохранить',
                cancelButtonText: 'Отмена',
                customClass: {
                    confirmButton: 'btn fw-bold btn-primary',
                    cancelButton: 'btn fw-bold btn-active-light-primary'
                }
            }).then(function (result) {
                if (result.value) {
                    confirmed = true;
                    $form.trigger('submit');
                }
            });
        });

        $('#delete_department_btn').on('click', function () {
            Swal.fire({
                html: 'Удалить департамент «{{ e($department->name) }}»? Действие необратимо.',
                icon: 'warning',
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: 'Удалить',
                cancelButtonText: 'Отмена',
                customClass: {
                    confirmButton: 'btn fw-bold btn-danger',
                    cancelButton: 'btn fw-bold btn-active-light-primary'
                }
            }).then(function (result) {
                if (result.value) {
                    $('#delete_department_form').trigger('submit');
                }
            });
        });
    </script>
@endpush
