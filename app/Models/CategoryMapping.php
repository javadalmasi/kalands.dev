<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryMapping extends Model
{
    protected $fillable = [
        'digikala_category_id',
        'source_category_id',
        'confidence',
        'is_manual',
    ];

    protected $casts = [
        'confidence' => 'float',
        'is_manual' => 'boolean',
    ];

    public function digikalaCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'digikala_category_id');
    }

    public function sourceCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'source_category_id');
    }
}
