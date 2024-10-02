<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Policies\ReportPolicy;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function users()
    {
        $this->authorize('users', ReportPolicy::class);
        $user = auth()->user();
        $deptUsers = $user->deptAllUsers();

        return view('cabinet.reports.users', compact('deptUsers'));
    }

    public function depts()
    {
        $this->authorize('depts', ReportPolicy::class);
        return view('cabinet.reports.depts');
    }

    public function tags()
    {
        $this->authorize('tags', ReportPolicy::class);
        return view('cabinet.reports.tags');
    }
}
