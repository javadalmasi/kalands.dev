<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Attributes\Casts;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'full_name',
    'username',
    'email_address',
    'mobile_number',
    'password_hash',
    'password_salt',
    'is_active',
    'dashboard_authkey',
    'dashboard_authkey_expires_at',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'theme_preference',
])]
#[Hidden([
    'password_hash',
    'password_salt',
    'remember_token',
    'two_factor_secret',
    'two_factor_recovery_codes',
])]
class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasPermissions;

    protected function casts(): array
    {
        return [
            'dashboard_authkey_expires_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
