<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AttachPermissionsRequest;
use App\Http\Requests\User\AttachRolesRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show(User $user)
    {
        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy('group');

       // dd(auth()->user()->getDepartmentId() . '-' . $user->getDepartmentId());

        return Auth::user()->getDepartmentId() == $user->getDepartmentId()
            ? view('cabinet.users.show2', compact('user', 'groupedPermissions'))
            : view('cabinet.users.show-stranger-user', compact('user'));
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
