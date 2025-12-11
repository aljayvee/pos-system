<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCredit extends Model
{

    protected $primaryKey = 'credit_id';
    protected $guarded = []; // Allow mass assignment for all fields
    // Allow these fields to be updated
    protected $fillable = [
        'customer_id', 
        'sale_id', 
        'total_amount', 
        'amount_paid', 
        'remaining_balance', 
        'due_date', 
        'is_paid'
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function sale() {
        return $this->belongsTo(Sale::class);
    }
}