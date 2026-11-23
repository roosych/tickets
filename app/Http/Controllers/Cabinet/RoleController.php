<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\AttachUsersRequest;
use App\Http\Requests\Roles\DetachUserRequest;
use App\Http\Requests\Roles\StoreRequest;
use App\Http\Requests\Roles\UpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\DepartmentAccess;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        abort_unless(DepartmentAccess::canViewRoles(auth()->user()), 403);

        $department = auth()->user()->getDepartment();
        abort_unless($department, 404);
        $roles = $department->roles;
        $permissions = Permission::getCachedPermissions(); // Права из кеша
        $groupedPermissions = $permissions->groupBy('group');

        return view('cabinet.department.roles.index', compact('groupedPermissions', 'roles'));
    }

    public function show(Role $role)
    {
        $this->authorize('show', $role);

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
        $actor = auth()->user();

        abort_unless(DepartmentAccess::isPrivileged($actor) || $actor->hasPermissions('create', Role::class), 403);
        abort_unless($actor->getDepartmentId(), 422, 'Нет текущего отдела');
        abort_unless(DepartmentAccess::canGrant($actor, $data['permissions'] ?? []), 403,
            'Нельзя включить в роль полномочия, которых нет у вас самих');

        $data['department_id'] = $actor->getDepartmentId();
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
        $actor = auth()->user();

        abort_unless(DepartmentAccess::canManageRole($actor, $role), 403);
        $added = array_diff(array_map('intval', $data['permissions'] ?? []), $role->permissions()->pluck('permissions.id')->map(fn ($id) => (int) $id)->all());
        abort_unless(DepartmentAccess::canGrant($actor, $added), 403,
            'Нельзя включить в роль полномочия, которых нет у вас самих');

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
        abort_unless(DepartmentAccess::canManageRole(auth()->user(), $role, 'delete'), 403);

        $role->delete();
        return response(['success' => true], 200);
    }

    public function attach_users(AttachUsersRequest $request, Role $role)
    {
        $data = $request->validated();
        $actor = auth()->user();

        abort_unless(DepartmentAccess::canManageRole($actor, $role), 403);
        abort_unless(DepartmentAccess::canGrant($actor, $role->permissions()->pluck('permissions.id')->all()), 403,
            'Нельзя назначать роль с полномочиями, которых нет у вас самих');

        // В роль можно добавлять только сотрудников отдела этой роли
        $users = User::whereIn('id', $data['users'] ?? [])->get();
        $foreign = $users->reject(fn (User $u) => in_array((int) $role->department_id, $u->accessibleDepartmentIds(), true));
        abort_if($foreign->isNotEmpty(), 422, 'В роль можно добавить только сотрудников отдела');

        $role->users()->sync($users->pluck('id')->all());
        return response()->json(['success' => true]);
    }

    public function detach_user(DetachUserRequest $request, Role $role)
    {
        $data = $request->validated();
        abort_unless(DepartmentAccess::canManageRole(auth()->user(), $role), 403);

        $role->users()->detach($data['user_id']);
        return response()->json(['success'=> true]);
    }
}
