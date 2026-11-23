<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;

/**
 * Серверные проверки управления ролями и полномочиями внутри отдела.
 *
 * Правила:
 *  - админ может всё;
 *  - менеджер текущего отдела может всё в своём отделе;
 *  - остальные — только при наличии соответствующего полномочия, только в своём текущем отделе
 *    и только выдавать полномочия, которые есть у них самих (иначе можно выдать себе всё).
 */
class DepartmentAccess
{
    public static function isManagerOfCurrent(User $actor): bool
    {
        $departmentId = $actor->getDepartmentId();

        return $departmentId
            && Department::whereKey($departmentId)->where('manager_id', $actor->id)->exists();
    }

    public static function isPrivileged(User $actor): bool
    {
        return (bool) $actor->is_admin || self::isManagerOfCurrent($actor);
    }

    /** Сотрудник состоит (или руководит) в текущем отделе того, кто действует. */
    public static function inCurrentDepartment(User $actor, User $target): bool
    {
        $departmentId = $actor->getDepartmentId();

        return $departmentId && in_array((int) $departmentId, $target->accessibleDepartmentIds(), true);
    }

    /** Может менять роли и прямые полномочия сотрудника. */
    public static function canManageUser(User $actor, User $target): bool
    {
        if ($actor->is_admin) {
            return true;
        }

        return self::inCurrentDepartment($actor, $target)
            && (self::isManagerOfCurrent($actor) || $actor->hasPermissions('accesses', User::class));
    }

    /** Может видеть раздел «Права доступа» текущего отдела. */
    public static function canViewRoles(User $actor): bool
    {
        return self::isPrivileged($actor)
            || $actor->hasPermissions('show', Role::class)
            || $actor->hasPermissions('create', Role::class);
    }

    /** Может создавать/менять роль (action = create) или удалять её (action = delete). */
    public static function canManageRole(User $actor, Role $role, string $action = 'create'): bool
    {
        if ($actor->is_admin) {
            return true;
        }

        if ((int) $role->department_id !== (int) $actor->getDepartmentId()) {
            return false;
        }

        return self::isManagerOfCurrent($actor) || $actor->hasPermissions($action, Role::class);
    }

    /**
     * Можно ли выдать эти полномочия: привилегированным — любые,
     * остальным — только те, что есть у них самих.
     *
     * @param array<int|string> $permissionIds
     */
    public static function canGrant(User $actor, array $permissionIds): bool
    {
        if (! $permissionIds || self::isPrivileged($actor)) {
            return true;
        }

        $own = self::effectivePermissionIds($actor);

        return ! array_diff(array_map('intval', $permissionIds), $own);
    }

    /** @return array<int> */
    public static function effectivePermissionIds(User $user): array
    {
        $user->loadMissing(['permissions', 'roles.permissions']);

        return $user->permissions->pluck('id')
            ->merge($user->roles->flatMap(fn (Role $role) => $role->permissions->pluck('id')))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
