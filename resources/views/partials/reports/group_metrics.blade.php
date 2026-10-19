@php
    $m = app(\App\Services\TicketReportMetrics::class)->summarize($tickets);
@endphp
<div class="d-flex flex-wrap gap-2 ms-5">
    @if($m['overdue'] > 0)
        <span class="badge badge-light-danger">{{$m['overdue']}} {{trans('common.reports.overdue_short')}}</span>
    @endif
    @if($m['on_time_percent'] !== null)
        <span class="badge badge-light-{{ $m['on_time_percent'] >= 80 ? 'success' : ($m['on_time_percent'] >= 50 ? 'warning' : 'danger') }}">{{$m['on_time_percent']}}% {{trans('common.reports.on_time_short')}}</span>
    @endif
    @if($m['avg_minutes'] !== null)
        <span class="badge badge-light-secondary">{{formatMinutes($m['avg_minutes'])}}</span>
    @endif
</div>
