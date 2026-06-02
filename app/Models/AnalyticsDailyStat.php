<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyStat extends Model
{
    protected $fillable = [
        'date',
        'metric',
        'dimension_key',
        'dimension_label',
        'count',
    ];

    protected $casts = [
        'date' => 'date',
        'count' => 'integer',
    ];
}
