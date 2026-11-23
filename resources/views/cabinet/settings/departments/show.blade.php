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

    <div class="d-flex flex-column flex-lg-row">
        <div class="w-100 flex-lg-row-auto w-lg-350px mb-7 me-7 me-lg-10">
        <form id="department_form" class="form"
              method="POST"
              action="{{route('cabinet.settings.departments.store', $department)}}"
              data-manager-id="{{$department->manager_id}}">
        @csrf
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
                                @foreach($managerOptions as $user)
                                    <option value="{{ $user->id }}" {{ $user->id == old('manager_id', $department->manager_id) ? 'selected' : '' }}>
                                        {{ $user->name }}{{ $user->department_id && $user->department_id != $department->id ? ' · ' . ($user->departmentRelation?->name ?? '') : '' }}
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
                                <div class="text-muted fs-8 mt-1">Новый менеджер получит все права. Если он числится в другом отделе, то останется там, а этот отдел появится у него в переключателе в шапке. Прежний менеджер остаётся сотрудником отдела.</div>
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

                        <button type="submit" class="btn btn-primary w-100">Сохранить</button>
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
        </form>
        </div>

        <div class="d-flex flex-column flex-lg-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Сотрудники департамента <span class="text-muted fs-5 ms-1" id="members_total">({{$members->count()}})</span></h2>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="d-flex flex-column flex-md-row gap-3 mb-2">
                        <div class="flex-grow-1">
                            <select id="add_members_select" class="form-select form-select-solid" multiple
                                    data-placeholder="Найти и добавить сотрудников...">
                                @foreach($candidates as $user)
                                    <option value="{{$user->id}}"
                                            data-name="{{$user->name}}"
                                            data-dept="{{$user->departmentRelation?->name}}"
                                            data-manager-of="{{$otherManagers[$user->id] ?? ''}}">
                                        {{$user->name}}{{$user->departmentRelation ? ' · ' . $user->departmentRelation->name : ''}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary text-nowrap d-inline-flex align-items-center justify-content-center" id="add_members_btn">
                            <span class="indicator-label d-inline-flex align-items-center"><i class="ki-outline ki-plus fs-2 me-1 p-0 lh-1"></i>Добавить</span>
                            <span class="indicator-progress">Добавление...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                    <div class="text-muted fs-8 mb-6">
                        Изменения состава сохраняются сразу. Рядом с именем — отдел, в котором сотрудник сейчас; при добавлении он будет перенесён.
                    </div>

                    <div class="position-relative mb-3">
                        <i class="ki-outline ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" id="members_search" class="form-control form-control-sm form-control-solid ps-12 w-md-250px"
                               placeholder="Поиск по составу...">
                    </div>

                    <table class="table align-middle table-row-dashed fs-6 gy-3 mb-0">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th>Сотрудник</th>
                            <th class="w-150px">Последний вход</th>
                            <th class="text-end w-125px">Создал тикетов</th>
                            <th class="text-end w-100px"></th>
                        </tr>
                        </thead>
                        <tbody id="members_body">
                        @foreach($members as $user)
                            @include('cabinet.settings.departments.partials.member-row')
                        @endforeach
                        <tr id="members_empty" class="{{ $members->count() ? 'd-none' : '' }}">
                            <td colspan="4" class="text-center text-muted py-10">В отделе пока нет сотрудников</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <form id="delete_department_form" method="POST" action="{{route('cabinet.settings.departments.destroy', $department)}}">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('custom_js')
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const $form = $('#department_form');
        const initialManager = String($form.data('manager-id') || '');

        $('#department_manager').on('change', function () {
            const changed = String($(this).val() || '') !== initialManager;
            $('#revoke_wrapper').toggleClass('d-none', !changed || !initialManager);
        });

        function escapeHtml(t) {
            return $('<div>').text(t == null ? '' : t).html();
        }

        function confirmDialog(html, confirmText, btnClass) {
            return Swal.fire({
                html: '<div class="text-start">' + html + '</div>',
                icon: 'warning',
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: confirmText,
                cancelButtonText: 'Отмена',
                customClass: {
                    confirmButton: 'btn fw-bold ' + (btnClass || 'btn-primary'),
                    cancelButton: 'btn fw-bold btn-active-light-primary'
                }
            }).then(r => !!r.value);
        }

        function notify(ok, text) {
            if (window.toastr) {
                toastr[ok ? 'success' : 'error'](escapeHtml(text));
            } else {
                Swal.fire({ text: text, icon: ok ? 'success' : 'error', buttonsStyling: false,
                    confirmButtonText: 'OK', customClass: { confirmButton: 'btn btn-primary' } });
            }
        }

        function ajaxError(xhr, fallback) {
            notify(false, (xhr.responseJSON && xhr.responseJSON.message) || fallback);
        }

        // Смена менеджера — подтверждение
        let confirmed = false;
        $form.on('submit', function (e) {
            if (confirmed) return;
            const newManager = String($('#department_manager').val() || '');
            if (newManager === initialManager) return;

            e.preventDefault();
            const revoke = $('input[name="revoke_old_manager"][type="checkbox"]').prop('checked') && initialManager;
            confirmDialog('<b>Меняется менеджер.</b> Новый получит все права'
                + (revoke ? ', у прежнего права будут сняты.' : '.'), 'Сохранить')
                .then(ok => { if (ok) { confirmed = true; $form.trigger('submit'); } });
        });

        // ===== Состав отдела =====
        const membersAddUrl = '{{ route('cabinet.settings.departments.members.add', $department) }}';
        const membersRemoveUrl = '{{ route('cabinet.settings.departments.members.remove', [$department, ':id']) }}';
        const $addSelect = $('#add_members_select');
        const $addBtn = $('#add_members_btn');
        const $body = $('#members_body');

        $addSelect.select2({
            width: '100%',
            closeOnSelect: false,
            templateResult: function (item) {
                if (!item.element) return item.text;
                const $o = $(item.element);
                const dept = $o.data('dept');
                const managerOf = $o.data('manager-of');
                let extra = '';
                if (dept) extra = '<span class="text-muted fs-8 ms-2">' + escapeHtml(dept) + '</span>';
                if (managerOf) extra += '<span class="text-primary fs-8 ms-2">★ руководит «' + escapeHtml(managerOf) + '»</span>';
                return $('<span>' + escapeHtml($o.data('name')) + extra + '</span>');
            }
        });

        function updateTotal(total) {
            $('#members_total').text('(' + total + ')');
            $('#members_empty').toggleClass('d-none', $body.find('tr[data-member-id]').length > 0);
        }

        $addBtn.on('click', function () {
            const ids = $addSelect.val() || [];
            if (!ids.length) {
                $addSelect.select2('open');
                return;
            }

            const moved = ids.map(id => $addSelect.find('option[value="' + id + '"]'))
                .filter($o => $o.data('dept'))
                .map($o => escapeHtml($o.data('name')) + ' ← ' + escapeHtml($o.data('dept'))
                    + ($o.data('manager-of') ? ' <span class="text-muted">(останется руководителем «' + escapeHtml($o.data('manager-of')) + '»)</span>' : ''));

            const go = moved.length
                ? confirmDialog('<b>Будут перенесены из других отделов:</b><br>' + moved.join('<br>'), 'Перенести')
                : Promise.resolve(true);

            go.then(function (ok) {
                if (!ok) return;
                $addBtn.attr('data-kt-indicator', 'on').prop('disabled', true);
                $.post(membersAddUrl, { user_ids: ids })
                    .done(function (res) {
                        res.rows.forEach(html => $body.find('#members_empty').before(html));
                        res.added.forEach(id => $addSelect.find('option[value="' + id + '"]').remove());
                        $addSelect.val(null).trigger('change');
                        updateTotal(res.total);
                        if (res.added.length) notify(true, 'Добавлено сотрудников: ' + res.added.length);
                        if (res.blocked.length) notify(false, 'Не добавлены: ' + res.blocked.join('; '));
                    })
                    .fail(xhr => ajaxError(xhr, 'Не удалось добавить сотрудников.'))
                    .always(() => $addBtn.removeAttr('data-kt-indicator').prop('disabled', false));
            });
        });

        $body.on('click', '[data-remove-member]', function () {
            const $btn = $(this);
            const $row = $btn.closest('tr');
            const id = $row.data('member-id');
            const name = $row.data('name');
            const open = Number($row.data('open'));

            const text = 'Убрать <b>' + escapeHtml(name) + '</b> из отдела?'
                + (open ? '<br><br>У сотрудника <b>' + open + '</b> открытых тикетов — они останутся за ним.' : '');

            confirmDialog(text, 'Убрать', 'btn-danger').then(function (ok) {
                if (!ok) return;
                $btn.prop('disabled', true);
                $.ajax({ url: membersRemoveUrl.replace(':id', id), type: 'DELETE' })
                    .done(function (res) {
                        $row.remove();
                        // Возвращаем в список для добавления
                        const opt = new Option(name, id, false, false);
                        $(opt).attr('data-name', name);
                        $addSelect.append(opt).trigger('change');
                        updateTotal(res.total);
                        notify(true, name + ' убран(а) из отдела');
                    })
                    .fail(function (xhr) {
                        $btn.prop('disabled', false);
                        ajaxError(xhr, 'Не удалось убрать сотрудника.');
                    });
            });
        });

        $('#members_search').on('input', function () {
            const q = $(this).val().trim().toLowerCase();
            $body.find('tr[data-member-id]').each(function () {
                $(this).toggleClass('d-none', q !== '' && $(this).text().toLowerCase().indexOf(q) === -1);
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
