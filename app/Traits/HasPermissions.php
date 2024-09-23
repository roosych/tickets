<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

trait HasPermissions
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    //todo вклчить кэш на продакшне
//    public function hasPermissions(string $action, string $model): bool
//    {
//        $cacheKey = 'permissions_' . $action . '_' . $model;
//
//        return Cache::remember($cacheKey, 60, function () use ($action, $model) {
//            return $this->hasDirectPermission($action, $model)
//                || $this->hasRolePermissions($action, $model);
//        });
//    }
    public function hasPermissions(string $action, string $model) :bool
    {
        return $this->hasDirectPermission($action, $model)
            || $this->hasRolePermissions($action, $model);
    }

    //прямые полномочия
    public function hasDirectPermission(string $action, string $model):bool
    {
        // $this это экземпляр данного трейта(модели) - User
        return $this->permissions
            ->where('action', $action)
            ->where('model', $model)
            ->isNotEmpty();
    }

    //полномочия роли
    public function hasRolePermissions(string $action, string $model):bool
    {
        //перед перебором одним запросом подгружаем с базы пермишены для каждой роли
        $this->roles->loadMissing('permissions');

        foreach ($this->roles as $role)
        {
            //проверяем у каждой роли есть ли указанные пермишены
            $exists = $role->permissions
                ->where('action', $action)
                ->where('model', $model)
                ->isNotEmpty();

            if ($exists)
            {
                return true;
            }
        }

        return false;
    }

}
