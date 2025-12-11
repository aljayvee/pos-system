<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCredit extends Model
{
    protected $guarded = []; // Allow mass assignment for all fields

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function sale() {
        return $this->belongsTo(Sale::class);
    }
}