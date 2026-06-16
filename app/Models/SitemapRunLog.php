<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SitemapRunLog extends Model
{
    protected $fillable = [
        'run_id',
        'version',
        'status',
        'total_products',
        'processed_products',
        'total_chunks',
        'force_mode',
        'rebuild_type',
        'started_at',
        'completed_at',
        'last_full_rebuild_at',
        'error_message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'force_mode' => 'boolean',
            'total_products' => 'integer',
            'processed_products' => 'integer',
            'total_chunks' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_full_rebuild_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function progress(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->total_products
                ? round(($this->processed_products / $this->total_products) * 100, 1)
                : 0,
        );
    }

    public function getPersianStartedAtAttribute(): ?string
    {
        if (!$this->started_at) return null;
        return verta($this->started_at)->format('Y/m/d H:i:s');
    }

    public function getPersianCompletedAtAttribute(): ?string
    {
        if (!$this->completed_at) return null;
        return verta($this->completed_at)->format('Y/m/d H:i:s');
    }
    
    public function isFullRebuild(): bool
    {
        return $this->rebuild_type === 'full';
    }
    
    public function isIncremental(): bool
    {
        return $this->rebuild_type === 'incremental';
    }
}
