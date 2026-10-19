<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AttachPermissionsRequest;
use App\Http\Requests\User\AttachRolesRequest;
use App\Enums\TicketStatusEnum;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Priorities;
use App\Models\User;
use App\Services\TicketReportMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(private readonly TicketReportMetrics $metrics)
    {
    }

    public function show(Request $request, User $user)
    {
        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy('group');

        if (Auth::user()->getDepartmentId() != $user->getDepartmentId()) {
            $user->load(['permissions', 'roles.permissions']);
            $department = $user->getDepartment();

            $priorities = Priorities::getCachedPriorities();
            $departments = Department::where('active', true)->get();

            return view('cabinet.users.show-stranger-user', compact('user', 'department', 'priorities', 'departments'));
        }

        $period = trim((string) $request->query('period'));
        $dates = array_map('trim', explode(' - ', $period));
        $from = count($dates) === 2 ? Carbon::createFromFormat('d.m.Y', $dates[0]) : false;
        $to = count($dates) === 2 ? Carbon::createFromFormat('d.m.Y', $dates[1]) : false;
        if (! $from || ! $to) {
            $period = '';
        }

        $tickets = $user->ticketsByExecutor()
            ->visible()
            ->visibleToUser()
            ->when($period !== '', fn ($query) => $query->whereBetween('tickets.created_at', [$from->startOfDay(), $to->endOfDay()]))
            ->with(['creator', 'priority'])
            ->latest()
            ->get();

        $counts = $tickets->countBy(fn ($ticket) => $ticket->status->value);

        $activeTickets = $tickets
            ->filter(fn ($ticket) => in_array($ticket->status, [TicketStatusEnum::OPENED, TicketStatusEnum::IN_PROGRESS]))
            ->sortBy(fn ($ticket) => $ticket->due_date?->timestamp ?? PHP_INT_MAX)
            ->values();

        $finishedTickets = $tickets
            ->filter(fn ($ticket) => in_array($ticket->status, [TicketStatusEnum::DONE, TicketStatusEnum::COMPLETED]))
            ->take(30);

        $canceledTickets = $tickets
            ->filter(fn ($ticket) => $ticket->status === TicketStatusEnum::CANCELED)
            ->take(30);

        $overdueCount = $activeTickets->filter(fn ($ticket) => $ticket->isDue())->count();

        $metricTickets = $tickets->load('histories');
        $metrics = $this->metrics->summarize($metricTickets);
        $stuckCount = $user->active ? 0 : $activeTickets->count();

        return view('cabinet.users.show2', compact(
            'user', 'groupedPermissions', 'counts', 'activeTickets', 'finishedTickets', 'canceledTickets', 'overdueCount', 'period', 'metrics', 'stuckCount'
        ));
    }

    public function attach_roles(AttachRolesRequest $request, User $user)
    {
        $data = $request->validated();
        $user->roles()->sync($data['roles'] ?? []);

        return response()->json(['success' => true]);
    }

    public function attach_permissions(AttachPermissionsRequest $request, User $user)
    {
        $data = $request->validated();
        $user->permissions()->sync($data['permissions'] ?? []);

        return response()->json(['success' => true]);
    }
}
