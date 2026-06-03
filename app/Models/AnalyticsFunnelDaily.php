<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsFunnelDaily extends Model
{
    protected $fillable = [
        'funnel_key',
        'date',
        'step',
        'step_name',
        'entered',
        'exited',
    ];

    protected $casts = [
        'date' => 'date',
        'step' => 'integer',
        'entered' => 'integer',
        'exited' => 'integer',
    ];
}
