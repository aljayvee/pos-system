<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'contact',
        'address',
        'points'
    ];

    // Relationship to Sales
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Relationship to Credits (This was missing)
    public function credits()
    {
        return $this->hasMany(CustomerCredit::class);
    }
}