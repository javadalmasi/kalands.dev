<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'model',
        'action',
        'tokens_used',
        'request_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'tokens_used' => 'integer',
        'request_count' => 'integer',
    ];
}
