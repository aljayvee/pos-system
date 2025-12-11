<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'price', 'cost', 'category_id', 'sku', 'stock', 
        'reorder_point' // <--- ADD THIS
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}