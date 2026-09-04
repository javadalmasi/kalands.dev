<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductIdMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_product_id',
        'new_product_id',
        'store',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function oldProduct()
    {
        return $this->belongsTo(Product::class, 'old_product_id', 'id');
    }

    public function newProduct()
    {
        return $this->belongsTo(Product::class, 'new_product_id', 'id');
    }
}
