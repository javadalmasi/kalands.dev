<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsFunnel extends Model
{
    protected $fillable = [
        'key',
        'name',
        'steps',
        'is_active',
    ];

    protected $casts = [
        'steps' => 'array',
        'is_active' => 'boolean',
    ];
}
