<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsAlert extends Model
{
    protected $fillable = [
        'name',
        'metric',
        'condition',
        'threshold',
        'cooldown_hours',
        'last_triggered_at',
        'is_active',
    ];

    protected $casts = [
        'last_triggered_at' => 'datetime',
        'is_active' => 'boolean',
        'threshold' => 'float',
        'cooldown_hours' => 'integer',
    ];
}
