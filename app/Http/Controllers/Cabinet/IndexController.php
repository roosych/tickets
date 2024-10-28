<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\TicketStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Priorities;
use App\Models\Ticket;
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
        $user = Auth::user();
        $tickets = Ticket::with('performer', 'department')->get();

        $openedTickets = $tickets
            ->where('executor_id', $user->id)
            ->where('status', TicketStatusEnum::OPENED);

        $doneTickets = $tickets
            ->where('department_id', Auth::user()->getDepartmentId())
            ->where('status', TicketStatusEnum::DONE)
            ->where('executor_id', '!=', auth()->id());

        //dd($openedTickets);

        $statusLabels = [];
        foreach (TicketStatusEnum::cases() as $status) {
            $statusLabels[$status->value] = $status->label();
        }
        $priorities = Priorities::getCachedPriorities();
        $departments = Department::where('active', true)->get();

        $data = $this->getTopPerformers();
        $topPerformers = $data['topPerformers'];
        $totalTickets = $data['totalDepartmentTickets'];

        return Auth::user()->getDepartment()->active
            ? view('cabinet.index', compact('priorities','openedTickets', 'departments', 'topPerformers', 'totalTickets', 'tickets', 'doneTickets'))
            : view('cabinet.deactive', compact('priorities', 'departments'));
    }

    private function getTopPerformers()
    {
        $departmentId = Auth::user()->getDepartmentId();

        $totalTickets = Ticket::where('department_id', $departmentId)->count();

        $topPerformers = User::select('users.*')
            ->join('tickets', 'users.id', '=', 'tickets.executor_id')
            ->where('tickets.department_id', $departmentId)
            ->groupBy('users.id')
            ->withCount(['tickets as ticket_count' => function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            }])
            ->orderBy('ticket_count', 'desc')
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
