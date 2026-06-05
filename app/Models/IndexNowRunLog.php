<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndexNowRunLog extends Model
{
    protected $table = 'index_now_run_logs';

    protected $fillable = [
        'run_id',
        'hour',
        'engine',
        'status',
        'total_queued',
        'total_submitted',
        'total_failed',
        'started_at',
        'completed_at',
        'error_message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'hour' => 'integer',
            'total_queued' => 'integer',
            'total_submitted' => 'integer',
            'total_failed' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}
