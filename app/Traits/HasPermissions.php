<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasPermissions
{
    public function roles()
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles');
    }

    public function hasPermission($permissionName)
    {
        // 1. Check Super Admin
        if ($this->relationLoaded('roles')) {
            if ($this->roles->contains('name', 'super_admin')) {
                return true;
            }
        } else {
            if ($this->roles()->where('name', 'super_admin')->exists()) {
                return true;
            }
        }

        // 2. Check Permissions across all roles
        if ($this->relationLoaded('roles')) {
            foreach ($this->roles as $role) {
                if ($role->relationLoaded('permissions')) {
                    if ($role->permissions->contains('name', $permissionName)) {
                        return true;
                    }
                } else {
                    if ($role->permissions()->where('name', $permissionName)->exists()) {
                        return true;
                    }
                }
            }
        } else {
            return $this->roles()->whereHas('permissions', function ($q) use ($permissionName) {
                $q->where('name', $permissionName);
            })->exists();
        }

        return false;
    }

    public function syncRoles($roleIds)
    {
        $this->roles()->sync($roleIds);
    }
}
