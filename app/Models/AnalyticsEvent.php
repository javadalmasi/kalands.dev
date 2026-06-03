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
        'funnel_key',
        'funnel_step',
        'funnel_step_name',
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
        'city',
        'region',
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
        'scroll_depth_pct',
        'session_num',
        'session_duration',
        'is_bounce',
        'occurred_at',
        'processed_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'processed_at' => 'datetime',
        'is_bounce' => 'boolean',
        'funnel_step' => 'integer',
        'scroll_depth_pct' => 'integer',
        'session_duration' => 'integer',
    ];
}
