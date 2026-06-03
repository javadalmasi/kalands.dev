<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSession extends Model
{
    protected $fillable = [
        'session_id',
        'visitor_hash',
        'user_id',
        'date',
        'started_at',
        'ended_at',
        'duration_seconds',
        'pageviews_count',
        'is_bounce',
        'entry_path',
        'exit_path',
        'referrer_type',
        'country_code',
        'device_type',
        'browser',
        'platform',
        'landing_source',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_bounce' => 'boolean',
        'pageviews_count' => 'integer',
        'duration_seconds' => 'integer',
    ];
}
