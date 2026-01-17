<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\StoreScope);
    }

    protected $fillable = [
        'name',
        'description',
        'store_id',
        'category_id',
        'tax_type',
        'price',
        'cost',
        'sku',
        'stock',
        'unit',
        'reorder_point',
        'image',
        'expiration_date'
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function pricingTiers()
    {
        return $this->hasMany(PricingTier::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get stock for a specific store.
     * 
     * @param int $storeId
     * @return int
     */
    public function getStockForStore(int $storeId): int
    {
        $inventory = $this->inventories->where('store_id', $storeId)->first();
        return $inventory ? $inventory->stock : 0;
    }

    /**
     * Get reorder point for a specific store.
     * 
     * @param int $storeId
     * @return int
     */
    public function getReorderPointForStore(int $storeId): int
    {
        $inventory = $this->inventories->where('store_id', $storeId)->first();
        return $inventory ? $inventory->reorder_point : 10;
    }
}