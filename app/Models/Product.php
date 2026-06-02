<?php

namespace App\Models;

use App\Traits\HasBookmarks;
use App\Traits\HasComments;
use App\Traits\HasLikes;
use Illuminate\Database\Eloquent\Attributes\Casts;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\PrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[PrimaryKey('id', type: 'string', incrementing: false)]
#[Fillable([
    'id',
    'title',
    'store',
    'category_id',
    'is_active',
    'api_status',
    'last_checked_at'
])]
#[Casts([
    'is_active' => 'boolean',
    'category_id' => 'integer',
    'api_status' => 'json',
    'last_checked_at' => 'datetime',
])]
class Product extends Model
{
    use HasFactory;

    public function getStoreLabelAttribute()
    {
        return $this->store === 'basalam' ? 'باسلام' : 'دیجی‌کالا';
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
