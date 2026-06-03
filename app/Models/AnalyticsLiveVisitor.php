<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsLiveVisitor extends Model
{
    protected $fillable = [
        'session_id',
        'visitor_hash',
        'user_id',
        'url',
        'path',
        'title',
        'product_id',
        'country_code',
        'country_name',
        'device_type',
        'device_brand',
        'ip_address',
        'user_agent',
        'first_seen_at',
        'last_seen_at',
        'pageviews_count',
        'is_bounced',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'pageviews_count' => 'integer',
        'is_bounced' => 'boolean',
    ];
}
