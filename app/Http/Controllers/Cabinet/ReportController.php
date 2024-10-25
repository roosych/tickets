<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\FilterGroupingEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\FilterRequest;
use App\Models\Department;
use App\Models\Priorities;
use App\Services\TicketService;
use Illuminate\Database\Eloquent\Collection;

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
}
