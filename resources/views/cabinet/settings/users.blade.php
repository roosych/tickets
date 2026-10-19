@extends('layouts.app')

@section('title', 'Список пользователей')

@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route('cabinet.index')}}" class="text-muted text-hover-primary">{{trans('common.mainpage')}}</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-500 w-5px h-2px"></span>
        </li>
        <li class="breadcrumb-item text-muted">Пользователи</li>
    </ul>
@endsection

@section('content')

    <div class="row g-5 mb-6">
        @foreach([
            ['Всего', $stats['total'], 'gray-800', 'all'],
            ['Активные', $stats['active'], 'success', 'active'],
            ['Неактивные', $stats['inactive'], $stats['inactive'] ? 'danger' : 'gray-800', 'inactive'],
            ['Скрытые', $stats['hidden'], 'gray-800', 'hidden'],
            ['Согласующие', $stats['approvers'], 'primary', 'approver'],
        ] as [$label, $count, $color, $key])
            <div class="col-6 col-xl">
                <a href="#" class="card card-flush h-100 hover-elevate-up" data-quick-filter="{{$key}}">
                    <div class="card-body py-5">
                        <div class="fs-2 fw-bold text-{{$color}} lh-1 mb-2">{{$count}}</div>
                        <div class="text-gray-600 fw-semibold fs-7">{{$label}}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    @if($stats['inactive_with_tickets'] > 0)
        <div class="alert alert-warning d-flex align-items-center mb-5">
            <i class="ki-outline ki-information-5 fs-2 text-warning me-3"></i>
            <div>
                У <strong>{{$stats['inactive_with_tickets']}}</strong> неактивных сотрудников остались открытые тикеты —
                их нужно переназначить.
                <a href="#" class="fw-bold ms-2" data-quick-filter="inactive_tickets">Показать</a>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                    <input type="text" user-report-filter="search" class="form-control form-control-solid w-250px ps-12"
                           placeholder="{{trans('tickets.table.search')}}" />
                </div>
            </div>
            <div class="card-toolbar flex-row flex-nowrap align-items-center gap-3">
                <div class="w-250px"><select class="form-select form-select-solid" id="filterDept" data-control="select2" data-placeholder="Все департаменты">
                    <option value="">Все департаменты</option>
                    @foreach($departments as $dept)
                        <option value="{{$dept->id}}">{{$dept->name}}</option>
                    @endforeach
                </select></div>
                <div class="w-200px"><select class="form-select form-select-solid" id="filterStatus" data-control="select2" data-hide-search="true">
                    <option value="all">Все</option>
                    <option value="active">Активные</option>
                    <option value="inactive">Неактивные</option>
                    <option value="hidden">Скрытые</option>
                    <option value="approver">Согласующие</option>
                    <option value="inactive_tickets">Неактивные с тикетами</option>
                </select></div>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="user_report_table">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-100px">Сотрудник</th>
                    <th class="min-w-75px">Департамент</th>
                    <th class="min-w-75px">Позиция</th>
                    <th class="min-w-100px">Учетка</th>
                    <th class="text-end">Открытых тикетов</th>
                    <th class="min-w-100px">Последний вход</th>
                    <th class="text-end">Видимый</th>
                    <th class="text-end">Активный</th>
                    <th class="text-end">Согласующий</th>
                    <th class="text-end">Email</th>
                    <th class="text-end">Telegram</th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                @foreach($users as $user)
                    <tr data-dept="{{$user->department_id}}"
                        data-active="{{(int) $user->active}}"
                        data-hidden="{{(int) !$user->visible}}"
                        data-approver="{{(int) $user->is_approver}}"
                        data-open="{{$user->open_tickets_count}}">
                        <td class="d-flex align-items-center border-bottom-0">
                            <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                @if($user->avatar)
                                    <div class="symbol-label">
                                        <img src="{{$user->avatar}}" alt="{{$user->name}}" class="w-100" />
                                    </div>
                                @else
                                    <div class="symbol-label fs-3 bg-light-dark text-dark">
                                        {{get_initials($user->name)}}
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex flex-column">
                                <p class="text-gray-800 mb-1">
                                    {{$user->name}}
                                    @unless($user->active)
                                        <span class="badge badge-light-danger ms-2 badge-inactive">Неактивен</span>
                                    @endunless
                                </p>
                                <span>{{$user->email}}</span>
                            </div>
                        </td>
                        <td>{{$user->departmentRelation?->name}}</td>
                        <td>{{$user->position}}</td>
                        <td>{{$user->username}}</td>
                        <td class="text-end" data-order="{{$user->open_tickets_count}}">
                            @if($user->open_tickets_count > 0)
                                <a href="{{route('cabinet.reports.tickets', ['filter' => ['executor_id' => $user->id]])}}"
                                   class="badge badge-light-{{$user->active ? 'primary' : 'danger'}}">{{$user->open_tickets_count}}</a>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td data-order="{{$user->last_login ? \Illuminate\Support\Carbon::parse($user->last_login)->timestamp : 0}}">
                            @if($user->last_login)
                                {{\Illuminate\Support\Carbon::parse($user->last_login)->format('d.m.Y H:i')}}
                            @else
                                <span class="text-muted">не входил(а)</span>
                            @endif
                        </td>
                        @foreach(['visible' => 'visible', 'active' => 'active', 'is_approver' => 'is_approver', 'email_notify' => 'email_notify', 'tg_notify' => 'tg_notify'] as $setting => $attr)
                            <td data-order="{{(int) $user->$attr}}">
                                <div class="d-flex justify-content-end">
                                    <div class="form-check form-check-solid form-check-custom form-switch">
                                        <input class="form-check-input w-35px h-20px setting-switch"
                                               type="checkbox"
                                               data-setting="{{$setting}}"
                                               data-user-id="{{$user->id}}"
                                               {{$user->$attr ? 'checked' : ''}}>
                                    </div>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <!-- Toast контейнер -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <!-- Уведомление об успехе -->
        <div id="toastSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto" id="toastTitleSuccess"></strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body fs-6 text-gray-800" id="toastMessageSuccess"></div>
        </div>

        <!-- Уведомление об ошибке -->
        <div id="toastError" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <strong class="me-auto" id="toastTitleError"></strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body fs-6 text-gray-800" id="toastMessageError"></div>
        </div>
    </div>
@endsection



@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/plugins/datatables.bundle.js')}}"></script>
@endpush

@push('modals')

@endpush

@push('custom_js')
    <script src="{{asset('assets/js/custom/settings/users.js')}}"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).on('change', '.setting-switch', function() {
                const $input = $(this);
                const setting = $input.data('setting');
                const isChecked = $input.prop('checked');
                const userId = $input.data('user-id');
                const $row = $input.closest('tr');

                $.ajax({
                    url: '{{ route('cabinet.settings.users.toggleUserSetting', ['id' => ':id', 'setting' => ':setting']) }}'
                        .replace(':id', userId)
                        .replace(':setting', setting),
                    type: 'POST',
                    data: { value: isChecked ? 1 : 0 },
                    success: function(response) {
                        const map = { active: 'active', visible: 'hidden', is_approver: 'approver' };
                        if (map[setting]) {
                            const val = setting === 'visible' ? !response.value : response.value;
                            $row.attr('data-' + map[setting], val ? 1 : 0);
                        }
                        if (setting === 'active') {
                            $row.find('.badge-inactive').remove();
                            if (!response.value) {
                                $row.find('p.text-gray-800').append('<span class="badge badge-light-danger ms-2 badge-inactive">Неактивен</span>');
                            }
                        }
                        $('#toastTitleSuccess').text('Success');
                        $('#toastMessageSuccess').text('Изменения сохранены!');
                        new bootstrap.Toast($('#toastSuccess')[0], { delay: 3000 }).show();
                    },
                    error: function() {
                        $input.prop('checked', !isChecked);
                        $('#toastTitleError').text('Error');
                        $('#toastMessageError').text('Произошла ошибка при изменении настройки.');
                        new bootstrap.Toast($('#toastError')[0], { delay: 3000 }).show();
                    }
                });
            });
        });
    </script>
@endpush
