<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'session_id',
        'visitor_hash',
        'user_id',
        'event_type',
        'goal_key',
        'goal_label',
        'product_id',
        'category_key',
        'seller_id',
        'search_term',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'search_engine',
        'url',
        'path',
        'title',
        'referrer_url',
        'referrer_host',
        'referrer_type',
        'country_code',
        'country_name',
        'device_type',
        'device_brand',
        'browser',
        'platform',
        'ip_address',
        'ip_hash',
        'user_agent',
        'user_agent_hash',
        'error_message',
        'error_source',
        'error_line',
        'occurred_at',
        'processed_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
