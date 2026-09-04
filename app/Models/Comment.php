<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'product_id',
    'user_id',
    'parent_id',
    'name',
    'email',
    'content',
    'ip_address',
    'status',
])]
class Comment extends Model
{
    use HasFactory;

    const STATUS_APPROVED = 'approved';

    const STATUS_PENDING = 'pending';

    const STATUS_REJECTED = 'rejected';

    const STATUS_SPAM = 'spam';

    const status = [self::STATUS_APPROVED, self::STATUS_PENDING, self::STATUS_REJECTED, self::STATUS_SPAM];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function votes()
    {
        return $this->hasMany(CommentVote::class);
    }
}
