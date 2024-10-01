<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\TicketStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $tickets = Ticket::where('executor_id', $user->id)->get();
        $ticketCounts = [
            'opened' => $tickets->where('status', TicketStatusEnum::OPENED)->count(),
            'in_progress' => $tickets->where('status', TicketStatusEnum::IN_PROGRESS)->count(),
            'done' => $tickets->where('status', TicketStatusEnum::DONE)->count(),
            'completed' => $tickets->where('status', TicketStatusEnum::COMPLETED)->count(),
            'canceled' => $tickets->where('status', TicketStatusEnum::CANCELED)->count(),
        ];

        $done_tickets_count = $tickets->where('status', TicketStatusEnum::DONE)->count();
        $data = $this->getTopPerformers();
        $topPerformers = $data['topPerformers'];
        $totalTickets = $data['totalDepartmentTickets'];
        return view('cabinet.index', compact('ticketCounts','done_tickets_count', 'topPerformers', 'totalTickets'));
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
            ->limit(5)
            ->get();

        return [
            'topPerformers' => $topPerformers,
            'totalDepartmentTickets' => $totalTickets
        ];
    }

    public function getChartData()
    {
        $user = Auth::user();

        $data = Ticket::where('status', TicketStatusEnum::DONE)
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
