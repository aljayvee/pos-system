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

    // REPLACE the 'store' method with this Financial-Grade version:

    public function store(Request $request)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction(); // Start ACID

        try {
            $storeId = $this->getActiveStoreId();

            // 1. Handle Supplier
            $supplierId = $request->supplier_id;
            if ($request->filled('new_supplier_name')) {
                $supplier = \App\Models\Supplier::firstOrCreate(
                    ['name' => $request->new_supplier_name],
                    ['contact_info' => $request->new_supplier_contact]
                );
                $supplierId = $supplier->id;
            }

            // 2. Create Purchase Record
            // We calculate the totals after processing items to be precise
            $purchase = Purchase::create([
                'store_id' => $storeId,
                'user_id' => Auth::id(),
                'supplier_id' => $supplierId,
                'purchase_date' => $request->purchase_date,
                'total_cost' => 0, // Will update later
                'input_vat' => 0,
                'is_vat_registered_supplier' => false 
            ]);

            $grandTotalCost = 0;

            foreach ($request->items as $itemData) {
                $pid = $itemData['product_id'];
                $newQty = $itemData['quantity'];
                $newCost = $itemData['unit_cost'];

                // ---------------------------------------------------------
                // START: WEIGHTED AVERAGE COST LOGIC (The Fix)
                // ---------------------------------------------------------
                
                // A. LOCK the Product Global Record
                $product = Product::where('id', $pid)->lockForUpdate()->firstOrFail();

                // B. Get Current Global Stock (Sum of all branches)
                // We need the TOTAL existing stock to weight the price correctly.
                $currentTotalStock = Inventory::where('product_id', $pid)->sum('stock');
                $currentCost = $product->cost ?? 0;

                // C. Calculate New Moving Average Cost
                // Formula: ((OldStock * OldCost) + (NewQty * NewCost)) / (OldStock + NewQty)
                $oldValue = $currentTotalStock * $currentCost;
                $newValue = $newQty * $newCost;
                $totalQty = $currentTotalStock + $newQty;

                $averageCost = $totalQty > 0 ? ($oldValue + $newValue) / $totalQty : $newCost;

                // D. Update Product with New Cost
                $product->cost = round($averageCost, 2); 
                $product->save();

                // ---------------------------------------------------------
                // END: COST LOGIC
                // ---------------------------------------------------------

                // 3. Create Purchase Item
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $pid,
                    'quantity' => $newQty,
                    'unit_cost' => $newCost // Keep record of what we actually paid
                ]);

                // 4. Update Branch Inventory (Atomic Increment)
                // Use firstOrCreate to ensure the record exists, then lock and update
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $pid, 'store_id' => $storeId],
                    ['stock' => 0, 'reorder_point' => 10]
                );
                
                // Refresh and Lock for safety
                $inventory = Inventory::where('id', $inventory->id)->lockForUpdate()->first();
                $inventory->stock += $newQty;
                $inventory->save();

                $grandTotalCost += ($newQty * $newCost);
            }

            // 5. Update Purchase Totals
            $purchase->total_cost = $grandTotalCost;
            
            // Optional: Simple VAT Logic (Inclusive)
            // If you need complex VAT, use the logic from your previous file
            $purchase->input_vat = $grandTotalCost - ($grandTotalCost / 1.12);
            $purchase->save();

            // 6. Log Activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'store_id' => $storeId,
                'action' => 'Restocking',
                'description' => "Restocked items (Purchase #{$purchase->id}). Total: ₱" . number_format($grandTotalCost, 2)
            ]);

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Stocks added and Cost Averaged successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Restock Failed: ' . $e->getMessage())->withInput();
        }
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