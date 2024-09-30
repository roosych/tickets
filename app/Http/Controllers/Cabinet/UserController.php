<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AttachPermissionsRequest;
use App\Http\Requests\User\AttachRolesRequest;
use App\Models\Permission;
use App\Models\User;

class UserController extends Controller
{
    public function show(User $user)
    {
        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy('group');
        return view('cabinet.users.show2', compact('user', 'groupedPermissions'));
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
