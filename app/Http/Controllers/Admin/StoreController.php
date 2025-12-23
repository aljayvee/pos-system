<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::all();
        return view('admin.stores.index', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $store = Store::create($request->all());

        // Initialize Inventory for new store (0 stock for all existing products)
        $products = Product::all();
        foreach($products as $product) {
            Inventory::create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'stock' => 0
            ]);
        }

        return back()->with('success', 'New branch created successfully.');
    }
    
    // Switch Active Store (For Admin Context)
    public function switch(Request $request, $id) {
        session(['active_store_id' => $id]);
        return back()->with('success', 'Switched active store context.');
    }

    public function update(Request $request, Store $store)
    {
        $request->validate(['name' => 'required']);
        $store->update($request->all());
        return back()->with('success', 'Store details updated successfully.');
    }
}