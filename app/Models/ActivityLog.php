<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action',
        'description',
        'ip_address',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
