<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SitemapRunLog extends Model
{
    protected $fillable = [
        'run_id',
        'status',
        'total_products',
        'processed_products',
        'total_chunks',
        'force_mode',
        'started_at',
        'completed_at',
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
}
