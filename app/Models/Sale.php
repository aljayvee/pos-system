<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    // Allow these fields to be filled by the controller
    protected $fillable = [
        'user_id', 
        'customer_id', 
        'total_amount', 
        'amount_paid', 
        'payment_method'
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
}