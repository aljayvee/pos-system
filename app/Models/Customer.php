<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'contact', 'address', 'points'];
    
    // Optional: Helper to check if they are a VIP (e.g., > 100 points)
    public function isVip() {
        return $this->points >= 100;
    }
}