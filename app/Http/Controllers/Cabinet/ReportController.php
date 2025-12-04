<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\FilterGroupingEnum;
use App\Exports\TicketsReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\FilterRequest;
use App\Models\Department;
use App\Models\Priorities;
use App\Services\TicketService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService
    ) {}

    public function tickets(FilterRequest $request)
    {
        $this->authorize('users', 'report');
        $user = auth()->user();
        $deptUsers = $user->deptAllUsers();
        $priorities = Priorities::getCachedPriorities();
        $departments = Department::all();

        $data = $request->validated();

        $groupedTickets = $this->ticketService->getFilteredAndGroupedTickets($data);;

        return view('cabinet.reports.users', compact('deptUsers', 'priorities', 'groupedTickets', 'departments'));
    }

    public function depts()
    {
        $this->authorize('depts', 'report');
        return view('cabinet.reports.depts');
    }

    public function tags()
    {
        $this->authorize('tags', 'report');
        return view('cabinet.reports.tags');
    }

    // Вспомогательные функции
    public static function groupBy(Collection $tickets, ?string $grouping): Collection
    {
        return match (FilterGroupingEnum::tryFrom($grouping)) {
            FilterGroupingEnum::TAG => $tickets->groupBy(fn($ticket) => $ticket->tags->pluck('id')),
            FilterGroupingEnum::PRIORITY => $tickets->groupBy('priority_id'),
            default => $tickets->groupBy('executor_id'),
        };
    }

    public function exportTickets(Request $request)
    {
        $this->authorize('users', 'report');

        // Получаем сгруппированные тикеты с фильтрами
        $tickets = $this->ticketService->getFilteredAndGroupedTickets($request->all());

        // Разворачиваем группы в плоскую коллекцию
        $flatTickets = collect();

        foreach ($tickets as $group) {
            if ($group instanceof Collection) {
                foreach ($group as $item) {
                    // если элемент массива ['ticket' => ..., 'tag' => ...]
                    if (is_array($item) && isset($item['ticket']) && $item['ticket'] instanceof \App\Models\Ticket) {
                        $flatTickets->push($item['ticket']->loadMissing(['creator', 'performer', 'priority']));
                    }
                    // если элемент — сразу Ticket
                    elseif ($item instanceof \App\Models\Ticket) {
                        $flatTickets->push($item->loadMissing(['creator', 'performer', 'priority']));
                    }
                }
            }
        }

        // Сортировка по дате создания
        $flatTickets = $flatTickets->sortBy('created_at')->values();

        return Excel::download(new TicketsReportExport($flatTickets), 'tickets_report.xlsx');
    }
}
