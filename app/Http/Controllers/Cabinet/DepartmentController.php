<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\TicketStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Priorities;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function tickets()
    {
        $statusLabels = [];
        foreach (TicketStatusEnum::cases() as $status) {
            $statusLabels[$status->value] = $status->label();
        }

        $tickets = Ticket::query()
            ->where('department_id', auth()
            ->user()->head->department_id)
            ->latest()
            ->get();
        //dd($tickets);
        $priorities = Priorities::all();
        $departments = Department::query()
            ->where('name', 'IT Department')
            ->get();
        //$departments = Department::all();
        return view('cabinet.department.tickets', compact('tickets', 'priorities', 'statusLabels', 'departments'));
    }

    public function users()
    {
        //$users = auth()->user()->deptAllUsers()->except(auth()->id());
        $users = auth()->user()->deptAllUsers();
        return view('cabinet.department.users', compact('users'));
    }

//    public function roles()
//    {
//        return view('cabinet.department.roles');
//    }

}
