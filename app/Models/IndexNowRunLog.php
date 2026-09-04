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

    public function getPersianStartedAtAttribute(): ?string
    {
        if (! $this->started_at) {
            return null;
        }

        return verta($this->started_at)->format('Y/m/d H:i:s');
    }

    public function getPersianCompletedAtAttribute(): ?string
    {
        if (! $this->completed_at) {
            return null;
        }

        return verta($this->completed_at)->format('Y/m/d H:i:s');
    }
}
