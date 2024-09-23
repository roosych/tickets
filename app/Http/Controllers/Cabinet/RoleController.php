<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\AttachUsersRequest;
use App\Http\Requests\Roles\DetachUserRequest;
use App\Http\Requests\Roles\StoreRequest;
use App\Http\Requests\Roles\UpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $roles = auth()->user()->getDepartment()->roles;
        $permissions = Permission::getCachedPermissions(); // Права из кеша
        $groupedPermissions = $permissions->groupBy('group');

        return view('cabinet.department.roles.index', compact('groupedPermissions', 'roles'));
    }

    public function show(Role $role)
    {
        //$this->authorize('show', $role);

        $permissions = Permission::getCachedPermissions(); // Права из кеша
        $groupedPermissions = $permissions->groupBy('group');
        $groupedRolePermissions = $role->permissions->groupBy('group');
        $employees = auth()->user()->deptUsers();

        return view('cabinet.department.roles.show',
            compact(
            'role',
            'groupedPermissions',
            'groupedRolePermissions',
            'employees')
        );
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $data['department_id'] = auth()->user()->head->getDepartmentId(); // может быть null
        $data['user_id'] = auth()->id(); // не может быть null

        try {
            DB::beginTransaction();
            $role = Role::query()->create($data);
            $role->permissions()->sync($data['permissions'] ?? []);
            DB::commit();
        }

        catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Something went wrong!');
        }

        return response()->json(['success' => true], 201);
    }

    public function update(UpdateRequest $request, Role $role)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $role->update(['name' => $data['name']]);
            $role->permissions()->sync($data['permissions'] ?? []);
            DB::commit();
        }

        catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Something went wrong!');
        }
        return response()->json(['success' => true]);
    }

    public function delete(Role $role)
    {
        $role->delete();
        return response(['success' => true], 200);
    }

    public function attach_users(AttachUsersRequest $request, Role $role)
    {
        $data = $request->validated();
        $role->users()->sync($data['users'] ?? []);
        return response()->json(['success' => true]);
    }

    public function detach_user(DetachUserRequest $request, Role $role)
    {
        $data = $request->validated();
        $role->users()->detach($data['user_id']);
        return response()->json(['success'=> true]);
    }
}
