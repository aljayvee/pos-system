<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Inventory; // <--- Import Inventory
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index()
    {
       $storeId = $this->getActiveStoreId();
       
       // Show purchases for the current store
       $purchases = Purchase::where('store_id', $storeId)
                        ->with('supplier', 'user')
                        ->latest()
                        ->paginate(15);
                        
       return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('admin.purchases.create', compact('products', 'suppliers'));
    }

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
            $storeId = $this->getActiveStoreId(); // Identify Active Branch

            // 1. Handle Supplier
            $supplierId = $request->supplier_id;
            if ($request->filled('new_supplier_name')) {
                $supplier = Supplier::create([
                    'name' => $request->new_supplier_name,
                    'contact_info' => $request->new_supplier_contact ?? null
                ]);
                $supplierId = $supplier->id;
            }

            // 2. Create Purchase Record linked to Store
            $purchase = Purchase::create([
                'store_id' => $storeId, // <--- Link to Branch
                'user_id' => Auth::id(),
                'supplier_id' => $supplierId,
                'purchase_date' => $request->purchase_date,
                'total_cost' => 0
            ]);

            $totalCost = 0;

            foreach ($request->items as $itemData) {
                // 3. Create Purchase Items
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost']
                ]);

                // 4. UPDATE BRANCH INVENTORY (Critical Fix)
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $itemData['product_id'], 'store_id' => $storeId],
                    ['stock' => 0, 'reorder_point' => 10]
                );
                $inventory->increment('stock', $itemData['quantity']);

                // 5. Update Global Product Cost (Moving Average or Last Price)
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $product->update(['cost' => $itemData['unit_cost']]);
                    // Optional: Update global stock sum if you maintain it
                    // $product->increment('stock', $itemData['quantity']); 
                }

                $totalCost += ($itemData['quantity'] * $itemData['unit_cost']);
            }

            $purchase->update(['total_cost' => $totalCost]);

            // Log Activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'store_id' => $storeId,
                'action' => 'Restocking',
                'description' => "Restocked items (Purchase #{$purchase->id}). Total: ₱" . number_format($totalCost, 2)
            ]);
        });

        return redirect()->route('purchases.index')->with('success', 'Stocks added to branch inventory successfully!');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['purchaseItems.product', 'supplier', 'user']);
        return view('admin.purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            // Reverse Stock from Branch Inventory
            foreach ($purchase->purchaseItems as $item) {
                $inventory = Inventory::where('product_id', $item->product_id)
                                ->where('store_id', $purchase->store_id) // Match the original store
                                ->first();
                                
                if ($inventory) {
                    $inventory->decrement('stock', $item->quantity);
                }
            }
            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase voided and inventory reversed.');
    }
}