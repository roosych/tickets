<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckDepartmentStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $departmentId = $user->getDepartmentId();

        $department = DB::table('departments')
            ->where('id', $departmentId)
            ->first();

        if ($department && !$department->active) {
            abort(404);
        }
        return $next($request);
    }
}
