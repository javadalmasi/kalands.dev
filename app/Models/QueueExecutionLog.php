<?php

namespace App\Models;

use Hekmatinasser\Verta\Verta;
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

    public function getPersianExecutedAtAttribute(): ?string
    {
        if (!$this->executed_at) {
            return null;
        }

        $v = new Verta($this->executed_at);
        $v->timezone('Asia/Tehran');

        return $v->format('Y/m/d H:i:s');
    }
}
