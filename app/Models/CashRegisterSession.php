<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterSession extends Model
{
    protected $fillable = [
        'store_id', 'user_id', 'opening_amount', 
        'closing_amount', 'expected_amount', 'variance', 
        'status', 'opened_at', 'closed_at', 'notes'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'variance' => 'decimal:2',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function adjustments() {
        return $this->hasMany(CashRegisterAdjustment::class);
    }
}
