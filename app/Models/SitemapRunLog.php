<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SitemapRunLog extends Model
{
    protected $fillable = [
        'run_id',
        'mode',
        'status',
        'total_products',
        'processed_products',
        'started_at',
        'completed_at',
        'error_message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'total_products' => 'integer',
            'processed_products' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function progress(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->total_products > 0
                ? min(100.0, round(($this->processed_products / $this->total_products) * 100, 1))
                : 0.0,
        );
    }

    public function isFull(): bool
    {
        return $this->mode === 'full';
    }

    public function isIncremental(): bool
    {
        return $this->mode === 'incremental';
    }
}
