<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateDailyStat extends Model
{
    protected $fillable = [
        'date',
        'store',
        'clicks',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
