<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\TicketActionEnum;
use App\Enums\TicketStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Priorities;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index()
    {
        //dd(Auth::user()->department_id);
        //auth()->loginUsingId(96);
        $user = Auth::user();
        $tickets = Ticket::with('performer', 'department', 'creator', 'priority', 'parent')
            ->where(function ($query) {
                $query->whereColumn('user_id', '!=', 'executor_id')
                    ->orWhereNull('executor_id'); // Добавляем условие для null
            })
            ->visibleToUser()
            ->get();

        $openedTickets = $tickets
            ->where('executor_id', $user->id)
            ->where('status', TicketStatusEnum::OPENED);

        $doneTickets = $tickets
            ->where('department_id', Auth::user()->getDepartmentId())
            ->where('status', TicketStatusEnum::DONE)
            ->where('executor_id', '!=', auth()->id());

        $myDoneTickets = $tickets
            ->where('department_id', Auth::user()->getDepartmentId())
            ->where('status', TicketStatusEnum::DONE)
            ->where('executor_id', auth()->id());

        $sentTickets = $tickets
            ->where('user_id', auth()->id());

        $period = in_array(request('period'), ['week', 'month']) ? request('period') : 'all';
        $periodStart = match ($period) {
            'week' => now()->subDays(6)->startOfDay(),
            'month' => now()->startOfMonth(),
            default => null,
        };
        $myCounts = $user->ticketsByExecutor()
            ->whereColumn('user_id', '!=', 'executor_id')
            ->when($periodStart, fn ($query) => $query->where('created_at', '>=', $periodStart))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeStatuses = [TicketStatusEnum::OPENED, TicketStatusEnum::IN_PROGRESS];
        $myActive = $tickets
            ->where('executor_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->whereNotNull('due_date');
        $overdueTickets = $myActive->filter(fn ($ticket) => $ticket->isDue())->sortBy('due_date')->values();
        $dueSoonTickets = $myActive
            ->filter(fn ($ticket) => ! $ticket->isDue() && $ticket->due_date->lte(now()->addHours(48)))
            ->sortBy('due_date')
            ->values();
        $awaitingCloseTickets = $sentTickets->where('status', TicketStatusEnum::DONE)->values();
        $unassignedTickets = $user->isManager()
            ? $tickets
                ->where('department_id', $user->getDepartmentId())
                ->whereNull('executor_id')
                ->where('status', TicketStatusEnum::OPENED)
                ->values()
            : collect();

        $deptSummary = null;
        if ($user->isManager()) {
            $deptActive = $tickets
                ->where('department_id', $user->getDepartmentId())
                ->whereIn('status', $activeStatuses)
                ->whereNotNull('executor_id');
            $deptSummary = [
                'opened' => $deptActive->where('status', TicketStatusEnum::OPENED)->count(),
                'in_progress' => $deptActive->where('status', TicketStatusEnum::IN_PROGRESS)->count(),
                'overdue' => $deptActive->filter(fn ($ticket) => $ticket->isDue())->count(),
                'busiest' => $deptActive->groupBy('executor_id')
                    ->map(fn ($group) => ['user' => $group->first()->performer, 'count' => $group->count()])
                    ->sortByDesc('count')
                    ->take(3)
                    ->values(),
            ];
        }

        $feed = TicketHistory::with(['ticket', 'user'])
            ->where('created_at', '>=', now()->subDays(14))
            ->whereHas('ticket')
            ->where(function ($query) use ($user) {
                $query->where(fn ($q) => $q
                    ->where('action', TicketActionEnum::ASSIGN_USER)
                    ->where('assign_user', $user->id)
                    ->where('user_id', '!=', $user->id))
                    ->orWhere(fn ($q) => $q
                        ->where('action', TicketActionEnum::UPDATE_STATUS)
                        ->where('user_id', '!=', $user->id)
                        ->whereHas('ticket', fn ($t) => $t->where('user_id', $user->id)));
            })
            ->latest()
            ->limit(8)
            ->get();

        $statusLabels = [];
        foreach (TicketStatusEnum::cases() as $status) {
            $statusLabels[$status->value] = $status->label();
        }
        $priorities = Priorities::getCachedPriorities();
        $departments = Department::where('active', true)->get();

        $data = $this->getTopPerformers();
        $topPerformers = $data['topPerformers'];
        $totalTickets = $data['totalDepartmentTickets'];

        $department = Auth::user()->getDepartment();

        if ($department) {
            return $department->active
                ? view('cabinet.index', compact('priorities','openedTickets', 'departments', 'topPerformers', 'totalTickets', 'tickets', 'doneTickets', 'sentTickets', 'myDoneTickets', 'period', 'myCounts', 'overdueTickets', 'dueSoonTickets', 'awaitingCloseTickets', 'unassignedTickets', 'deptSummary', 'feed'))
                : view('cabinet.deactive', compact('priorities', 'departments'));
        } else {
            // Пользователь не привязан к департаменту
            return view('cabinet.no_department');
        }
    }

    private function getTopPerformers()
    {
        $departmentId = Auth::user()->getDepartmentId();

        $totalTickets = Ticket::where('department_id', $departmentId)
            ->whereColumn('user_id', '!=', 'executor_id')
            ->count();

        $topPerformers = User::select('users.*')
            ->selectRaw('COUNT(DISTINCT tickets.id) as completed_tickets_count')
            ->join('tickets', 'users.id', '=', 'tickets.executor_id')
            ->where('tickets.department_id', $departmentId)
            ->whereIn('tickets.status', [
                TicketStatusEnum::DONE->value,
                TicketStatusEnum::COMPLETED->value
            ])
            ->whereColumn('tickets.user_id', '!=', 'tickets.executor_id')
            ->groupBy('users.id')
            ->orderBy('completed_tickets_count', 'desc')
            ->limit(3)
            ->get();

        return [
            'topPerformers' => $topPerformers,
            'totalDepartmentTickets' => $totalTickets
        ];
    }

    public function getChartData()
    {
        $user = Auth::user();

        $data = Ticket::where('status', TicketStatusEnum::COMPLETED)
            ->where('executor_id', $user->id)
            ->where('user_id', '!=', 'executor_id')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Заполняем массив нулями для отсутствующих месяцев
        $filledData = [];
        for ($i = 1; $i <= 12; $i++) {
            $filledData[$i] = $data[$i] ?? 0;
        }

        return response()->json(['data' => array_values($filledData)]);
    }
}
