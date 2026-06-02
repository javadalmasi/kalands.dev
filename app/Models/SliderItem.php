<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SliderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'slider_id',
        'image',
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'sort_order',
        'is_active',
        'slide_type',
        'meta_json',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta_json' => 'array',
    ];

    public function slider(): BelongsTo
    {
        return $this->belongsTo(Slider::class);
    }
}
