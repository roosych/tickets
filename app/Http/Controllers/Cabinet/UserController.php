<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy('group');
        return view('cabinet.users.show2', compact('user', 'groupedPermissions'));
    }
}
