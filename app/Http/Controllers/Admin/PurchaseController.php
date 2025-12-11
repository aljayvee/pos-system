<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // 1. Show History
    public function index()
    {
        $purchases = Purchase::with('supplier')->latest()->get();
        return view('admin.purchases.index', compact('purchases'));
    }

    // 2. Show Stock-In Form
    public function create()
    {
        $products = Product::all();
        $suppliers = Supplier::all();
        return view('admin.purchases.create', compact('products', 'suppliers'));
    }

    // 3. Save Restocking
    public function store(Request $request)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Handle Supplier (Create new if name provided, or select existing)
            $supplierId = $request->supplier_id;
            if ($request->filled('new_supplier_name')) {
                $supplier = Supplier::create(['name' => $request->new_supplier_name]);
                $supplierId = $supplier->id;
            }

            // Create Purchase Header
            $purchase = Purchase::create([
                'supplier_id' => $supplierId,
                'purchase_date' => $request->purchase_date,
                'total_cost' => 0 // Will calculate below
            ]);

            $totalCost = 0;

            foreach ($request->items as $itemData) {
                // Create Item Record
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost']
                ]);

                // UPDATE INVENTORY (Core Requirement)
                $product = Product::find($itemData['product_id']);
                $product->increment('stock', $itemData['quantity']);
                
                // Optional: Update product "Cost Price" to the latest buying price
                $product->update(['cost' => $itemData['unit_cost']]);

                $totalCost += ($itemData['quantity'] * $itemData['unit_cost']);
            }

            // Update Total
            $purchase->update(['total_cost' => $totalCost]);
        });

        return redirect()->route('purchases.index')->with('success', 'Stocks added successfully!');
    }

    public function show(Purchase $purchase)
    {
        // Eager load items and products for performance
        $purchase->load(['purchaseItems.product', 'supplier', 'user']);
        
        return view('admin.purchases.show', compact('purchase'));
    }
}