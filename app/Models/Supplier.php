<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    // FIX: Added 'store_id' to allow mass assignment
    protected $fillable = ['name', 'contact_info', 'store_id'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\StoreScope);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}