<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'config_key',
        'config_value',
        'is_sensitive',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'config_value' => 'array',
    ];
}
