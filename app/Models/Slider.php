<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_name',
        'title',
        'status',
        'config_json',
    ];

    protected $casts = [
        'status' => 'boolean',
        'config_json' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SliderItem::class)->orderBy('sort_order');
    }
}
