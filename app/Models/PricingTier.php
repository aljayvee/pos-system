<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingTier extends Model
{
    use HasFactory;

    protected $table = 'product_pricing_tiers';

    protected $fillable = [
        'product_id',
        'quantity',
        'price',
        'name',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
