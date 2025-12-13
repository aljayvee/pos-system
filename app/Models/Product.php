<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Import this

class Product extends Model
{
    use SoftDeletes; // Enable Soft Deletes

    protected $fillable = [
    'name', 
    'description', 
    'category_id', 
    'price', 
    'cost',   // <--- Add this
    'sku',    // <--- Add this
    'stock', 
    'unit', 
    'reorder_point',
    'image', 
    'reorder_point'
];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}