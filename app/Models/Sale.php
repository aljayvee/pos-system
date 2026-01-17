<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;
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
        'points_discount',
        'vatable_sales',
        'vat_exempt_sales',
        'vat_zero_rated_sales',
        'vat_amount',
        'output_vat',
        'invoice_number',
        'discount_type',
        'discount_card_no',
        'discount_name',
        'discount_amount'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\StoreScope);
    }

    // Define relationships
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
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