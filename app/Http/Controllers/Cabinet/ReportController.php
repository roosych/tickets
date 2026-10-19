<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\FilterGroupingEnum;
use App\Exports\TicketsReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\FilterRequest;
use App\Models\Department;
use App\Models\Priorities;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketReportMetrics;
use App\Services\TicketService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly TicketReportMetrics $metrics
    ) {}

    public function tickets(FilterRequest $request)
    {
        $this->authorize('users', 'report');
        $user = auth()->user();
        $deptUsers = $user->deptAllUsers();
        $priorities = Priorities::getCachedPriorities();
        $departments = Department::all();

        $data = $request->validated();

        $groupedTickets = $this->ticketService->getFilteredAndGroupedTickets($data);

        $summary = $this->metrics->summarize($this->flattenTickets($groupedTickets));

        // Бывшие сотрудники отдела, у которых остались тикеты: нужны в фильтре, чтобы их можно было выбрать
        $inactiveUsers = User::where('active', false)
            ->whereIn('id', Ticket::where('department_id', $user->getDepartmentId())
                ->whereNotNull('executor_id')
                ->select('executor_id'))
            ->orderBy('name')
            ->get();

        return view('cabinet.reports.users', compact(
            'deptUsers', 'priorities', 'groupedTickets', 'departments', 'summary', 'inactiveUsers'
        ));
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

        $flatTickets = $this->flattenTickets($tickets);

        // Сортировка по дате создания
        $flatTickets = $flatTickets->sortBy('created_at')->values();

        return Excel::download(new TicketsReportExport($flatTickets), 'tickets_report.xlsx');
    }

    /**
     * Разворачивает сгруппированные тикеты в плоскую коллекцию (с учётом формата группировки по тегам).
     */
    private function flattenTickets($grouped): \Illuminate\Support\Collection
    {
        $flat = collect();

        foreach ($grouped as $group) {
            // группировка по тегам: ['tag' => ..., 'tickets' => Collection]
            if (is_array($group) && isset($group['tickets'])) {
                $group = $group['tickets'];
            }
            if (! $group instanceof \Illuminate\Support\Collection) {
                continue;
            }
            foreach ($group as $item) {
                if (is_array($item) && ($item['ticket'] ?? null) instanceof Ticket) {
                    $flat->push($item['ticket']->loadMissing(['creator', 'performer', 'priority']));
                } elseif ($item instanceof Ticket) {
                    $flat->push($item->loadMissing(['creator', 'performer', 'priority']));
                }
            }
        }

        return $flat->unique('id')->values();
    }
}
