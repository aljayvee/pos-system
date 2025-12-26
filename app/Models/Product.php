<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Import this

class Product extends Model
{
    use SoftDeletes; // Enable Soft Deletes

    protected $fillable = [
    'name', 
    'description', 
    'category_id', 
    'price', 
    'cost',   // <--- Add this
    'sku',    // <--- Add this
    'stock', 
    'unit',
    'reorder_point',
    'image', 
    'reorder_point',
    'expiration_date'
];

    // Optional: Tell Laravel this is a date so it formats correctly
    protected $casts = [
        'expiration_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // --- ADD THIS MISSING RELATIONSHIP ---
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Relationship to Pricing Tiers (Multi-Buy Strategy)
    public function pricingTiers()
    {
        return $this->hasMany(PricingTier::class);
    }

    // Relationship to Multi-Store Inventory
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    // DYNAMIC ACCESSOR: $product->stock
    // Automatically fetches stock for the current active store (defaults to Store 1)
    public function getStockAttribute()
    {
        // Check if multi-store is enabled
        $multiStoreEnabled = \App\Models\Setting::where('key', 'enable_multi_store')->value('value') ?? '0';
        
        // Determine Current Store ID (Default to 1)
        $storeId = 1;
        if ($multiStoreEnabled == '1') {
            $storeId = session('active_store_id', auth()->user()->store_id ?? 1);
        }

        // Fetch from Inventory Table
        // We use the relationship to avoid N+1 queries if eager loaded
        $inventory = $this->inventories->where('store_id', $storeId)->first();
        
        return $inventory ? $inventory->stock : 0;
    }

    // DYNAMIC ACCESSOR: $product->reorder_point
    public function getReorderPointAttribute()
    {
        $multiStoreEnabled = \App\Models\Setting::where('key', 'enable_multi_store')->value('value') ?? '0';
        $storeId = ($multiStoreEnabled == '1') ? session('active_store_id', auth()->user()->store_id ?? 1) : 1;
        
        $inventory = $this->inventories->where('store_id', $storeId)->first();
        return $inventory ? $inventory->reorder_point : 10;
    }

}