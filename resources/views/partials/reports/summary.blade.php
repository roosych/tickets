@php
    $query = request()->query();
    $chipUrl = function (string $key) use ($query) {
        $q = $query;
        \Illuminate\Support\Arr::forget($q, $key);
        if ($key === 'date_range') {
            \Illuminate\Support\Arr::forget($q, 'date_mode');
        }

        return route('cabinet.reports.tickets', $q);
    };
    $chips = [];
    if ($id = request('filter.executor_id')) {
        $chips[] = [trans('common.reports.employee') . ': ' . ($deptUsers->firstWhere('id', $id)?->name ?? $inactiveUsers->firstWhere('id', $id)?->name ?? $id), 'filter.executor_id'];
    }
    if ($id = request('filter.department_id')) {
        $chips[] = [trans('common.reports.department') . ': ' . ($departments->firstWhere('id', $id)?->name ?? $id), 'filter.department_id'];
    }
    if ($st = \App\Enums\TicketStatusEnum::tryFrom((string) request('filter.status'))) {
        $chips[] = [trans('common.reports.status') . ': ' . $st->label(), 'filter.status'];
    }
    if ($range = request('date_range')) {
        $chips[] = [(request('date_mode') === 'closed' ? trans('common.reports.by_closed') : trans('common.reports.by_created')) . ': ' . $range, 'date_range'];
    }
    if (request('hide_inactive')) {
        $chips[] = [trans('common.reports.hide_inactive'), 'hide_inactive'];
    }
    foreach ((array) request('filter.priorities_id', []) as $pid) {
        $chips[] = [trans('tickets.table.priority') . ': ' . ($priorities->firstWhere('id', (int) $pid)?->getNameByLocale() ?? $pid), 'filter.priorities_id'];
    }
    $kpis = [
        [trans('common.reports.kpi_total'), $summary['total'], 'gray-800'],
        [trans('common.reports.kpi_done'), $summary['done'], 'success'],
        [trans('common.reports.kpi_overdue'), $summary['overdue'], $summary['overdue'] ? 'danger' : 'gray-800'],
        [trans('common.reports.kpi_on_time'), $summary['on_time_percent'] === null ? '—' : $summary['on_time_percent'] . '%', 'primary'],
        [trans('common.reports.kpi_avg'), formatMinutes($summary['avg_minutes']), 'gray-800'],
    ];
@endphp

<div class="row g-5 mb-6">
    @foreach($kpis as [$label, $value, $color])
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

@if(count($chips))
    <div class="d-flex flex-wrap gap-2 mb-6">
        @foreach($chips as [$label, $key])
            <a href="{{ $chipUrl($key) }}" class="badge badge-light-primary fs-7 py-2 px-3 text-hover-danger">
                {{$label}} <i class="ki-outline ki-cross fs-6 ms-1"></i>
            </a>
        @endforeach
    </div>
@endif
