<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterAdjustment extends Model
{
    protected $fillable = [
        'cash_register_session_id', 'user_id', 'approved_by', 
        'original_amount', 'new_amount', 'reason', 'status'
    ];

    public function session() {
        return $this->belongsTo(CashRegisterSession::class, 'cash_register_session_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
