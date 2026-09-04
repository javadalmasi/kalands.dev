<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'first_name',
    'last_name',
    'email',
    'phone',
    'password_hash',
    'password_salt',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'dashboard_authkey',
    'dashboard_authkey_expires_at',
    'theme_preference',
    'is_active',
    'profile_bio',
    'marketing_opt_in',
])]
#[Hidden([
    'password_hash',
    'password_salt',
    'remember_token',
    'two_factor_secret',
    'two_factor_recovery_codes',
])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPermissions, Notifiable;

    public function otp()
    {
        return $this->hasOne(OTP::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkCategories()
    {
        return $this->hasMany(BookmarkCategory::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'dashboard_authkey_expires_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'is_active' => 'boolean',
            'marketing_opt_in' => 'boolean',
        ];
    }

    public function name(): Attribute
    {
        return Attribute::get(fn () => trim($this->first_name.' '.$this->last_name));
    }
}
