<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueExecutionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'executed_at',
        'status',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'meta' => 'array',
    ];
}
