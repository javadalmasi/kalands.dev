<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One product sub-sitemap's metadata (keyset boundaries + counts).
 *
 * @property int $generation
 * @property int $shard_index
 * @property string $first_product_id
 * @property string $last_product_id
 * @property int $url_count
 * @property Carbon|null $lastmod
 */
class SitemapShard extends Model
{
    protected $fillable = [
        'generation',
        'shard_index',
        'first_product_id',
        'last_product_id',
        'url_count',
        'lastmod',
    ];

    protected function casts(): array
    {
        return [
            'generation' => 'integer',
            'shard_index' => 'integer',
            'url_count' => 'integer',
            'lastmod' => 'datetime',
        ];
    }

    public function scopeGeneration($query, int $generation)
    {
        return $query->where('generation', $generation);
    }
}
