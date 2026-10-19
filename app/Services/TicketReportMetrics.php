<?php

namespace App\Services;

use App\Enums\TicketStatusEnum;
use Illuminate\Support\Collection;

class TicketReportMetrics
{
    /**
     * Сводка по набору тикетов (история статусов должна быть загружена).
     *
     * @return array{total:int, done:int, active:int, overdue:int, on_time_percent:?int, avg_minutes:?int}
     */
    public function summarize(Collection $tickets): array
    {
        $tickets = $tickets->unique('id');

        $done = $tickets->filter(fn ($t) => in_array($t->status, [TicketStatusEnum::DONE, TicketStatusEnum::COMPLETED]));
        $active = $tickets->filter(fn ($t) => in_array($t->status, [TicketStatusEnum::OPENED, TicketStatusEnum::IN_PROGRESS]));
        $overdue = $active->filter(fn ($t) => $t->due_date && $t->due_date->isPast());

        $withDeadline = $done->filter(fn ($t) => $t->due_date && $t->doneAt());
        $onTime = $withDeadline->filter(fn ($t) => $t->doneAt()->lte($t->due_date));

        $durations = $done
            ->map(fn ($t) => $t->doneAt() ? $t->created_at->diffInMinutes($t->doneAt()) : null)
            ->filter(fn ($minutes) => $minutes !== null);

        return [
            'total' => $tickets->count(),
            'done' => $done->count(),
            'active' => $active->count(),
            'overdue' => $overdue->count(),
            'on_time_percent' => $withDeadline->isEmpty() ? null : (int) round($onTime->count() / $withDeadline->count() * 100),
            'avg_minutes' => $durations->isEmpty() ? null : (int) round($durations->avg()),
        ];
    }
}
