<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = ['name', 'address', 'contact_number', 'is_active'];

    public function inventories() {
        return $this->hasMany(Inventory::class);
    }
}