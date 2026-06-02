<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'title',
        'name_en',
        'store',
        'product_count',
        'vector',
        'vector_source',
        'vector_model',
        'external_id',
    ];

    protected $casts = [
        'vector' => 'json',
        'product_count' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function mappings(): HasMany
    {
        if ($this->store === 'digikala') {
            return $this->hasMany(CategoryMapping::class, 'digikala_category_id');
        }
        return $this->hasMany(CategoryMapping::class, 'basalam_category_id');
    }

}
