<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'store_id'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\StoreScope);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
