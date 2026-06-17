<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitemapGroup extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'version',
        'group_index',
        'filename',
        'url_count',
        'first_product_id',
        'last_product_id',
        'is_complete',
        'is_active',
        'created_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'group_index' => 'integer',
            'url_count' => 'integer',
            'is_complete' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVersion($query, string $version)
    {
        return $query->where('version', $version);
    }

    public function scopeIncomplete($query)
    {
        return $query->where('is_complete', false);
    }

    public function scopeComplete($query)
    {
        return $query->where('is_complete', true);
    }

    public function hasSpace(): bool
    {
        return $this->url_count < 50000;
    }

    public function remainingCapacity(): int
    {
        return max(0, 50000 - $this->url_count);
    }
}
