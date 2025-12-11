<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Import this

class Product extends Model
{
    use SoftDeletes; // Enable Soft Deletes

    protected $fillable = [
        'name', 'unit', 'price', 'cost', 'category_id', 'sku', 'stock', 'reorder_point'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}