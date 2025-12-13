<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    // Allow these fields to be filled by the controller
    protected $fillable = [
        'store_id',
        'user_id', 
        'customer_id', 
        'total_amount', 
        'amount_paid', 
        'payment_method',
        'reference_number', // Ensure this is here too
        'points_used',      // And this
        'points_discount'
    ];

    // Define relationships
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}