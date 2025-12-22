<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{

    

    // Allow these fields
    protected $fillable = [
        'sale_id', 
        'product_id', 
        'quantity', 
        'price',
        'cost',
        'subtotal' // <--- Add this
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}