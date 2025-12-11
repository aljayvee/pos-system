<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $fillable = [
        'customer_credit_id',
        'amount',
        'payment_date',
        'user_id',
        'notes'
    ];

    public function credit()
    {
        return $this->belongsTo(CustomerCredit::class, 'customer_credit_id', 'credit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}