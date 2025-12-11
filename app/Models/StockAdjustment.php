<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity', // Positive for adding, Negative for deducting
        'type',   // e.g., 'Spoilage', 'Theft', 'Internal Use'
        'remarks'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}