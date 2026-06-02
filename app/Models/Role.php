<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'label'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function admins()
    {
        return $this->morphedByMany(Admin::class, 'model', 'model_has_roles');
    }

    public function users()
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_roles');
    }
}
