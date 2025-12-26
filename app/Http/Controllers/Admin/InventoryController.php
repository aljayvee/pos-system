<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\ActivityLog;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // 1. Show Inventory & Stats
    public function index(Request $request)
    {
        $storeId = $this->getActiveStoreId();

        // Query Products that have stock IN THIS BRANCH
        $query = Product::with('category')
                    ->whereHas('inventories', function($q) use ($storeId) {
                        $q->where('store_id', $storeId)
                          ->where('stock', '>', 0);
                    });

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $allProducts = $query->get();

        // Stats - Accessor ($p->stock) handles the store logic automatically now
        $totalItems = $allProducts->sum('stock');
        $totalCostValue = $allProducts->sum(function($p) { return $p->stock * ($p->cost ?? 0); });
        $totalSalesValue = $allProducts->sum(function($p) { return $p->stock * $p->price; });
        $potentialProfit = $totalSalesValue - $totalCostValue;

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('admin.inventory.index', compact(
            'products', 'categories', 
            'totalItems', 'totalCostValue', 'totalSalesValue', 'potentialProfit'
        ));
    }

    // 2. Show Adjustment Form
    public function adjust()
    {
        $products = Product::orderBy('name')->get();
        $adjustments = StockAdjustment::with('product', 'user')->latest()->paginate(10);

        return view('admin.inventory.adjust', compact('products', 'adjustments'));
    }

    // 3. Process Adjustment (Consolidated)
    // REPLACE the 'storeAdjustment' method with this:
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:add,subtract',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'required|string',
            'remarks'    => 'nullable|string'
        ]);

        // Start ACID Transaction
        DB::beginTransaction();

        try {
            $storeId = $this->getActiveStoreId(); // Ensure we adjust the correct branch
            $qty = intval($request->quantity);

            // 1. LOCK the specific Branch Inventory Record
            // We use firstOrNew because the record might not exist yet for this branch
            $inventory = Inventory::where('product_id', $request->product_id)
                            ->where('store_id', $storeId)
                            ->lockForUpdate() // <--- PREVENTS RACE CONDITIONS
                            ->first();

            // If no inventory record exists yet, we create a temporary instance to check logic
            if (!$inventory) {
                $inventory = new Inventory([
                    'product_id' => $request->product_id,
                    'store_id' => $storeId,
                    'stock' => 0,
                    'reorder_point' => 10
                ]);
            }

            // 2. Validate Stock Logic (Consistency)
            if ($request->type === 'subtract') {
                if ($inventory->stock < $qty) {
                    throw new \Exception("Cannot remove {$qty} items. Current branch stock is only {$inventory->stock}.");
                }
                $finalQty = -$qty; // Make negative for math
            } else {
                $finalQty = $qty;
            }

            // 3. Update Inventory (Atomicity)
            // If the record was new, this creates it. If it existed, this updates it.
            $inventory->stock += $finalQty;
            $inventory->save();

            // 4. Log the Adjustment
            StockAdjustment::create([
                'user_id'    => Auth::id(),
                'product_id' => $request->product_id,
                'store_id'   => $storeId,
                'quantity'   => $finalQty,
                'type'       => $request->reason,
                'remarks'    => $request->remarks
            ]);

            // 5. Activity Log
            $productName = Product::where('id', $request->product_id)->value('name');
            $actionWord = $request->type === 'add' ? 'Added' : 'Removed';
            
            ActivityLog::create([
                'user_id' => Auth::id(),
                'store_id' => $storeId,
                'action' => 'Stock Adjustment',
                'description' => "{$actionWord} {$qty} stocks of '{$productName}'. New Balance: {$inventory->stock}."
            ]);

            DB::commit(); // Save everything
            return back()->with('success', 'Stock adjusted successfully.');

        } catch (\Exception $e) {
            DB::rollBack(); // Undo everything if error
            return back()->with('error', 'Adjustment Failed: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $adjustments = StockAdjustment::with('product', 'user')->latest()->paginate(20);
        return view('admin.inventory.history', compact('adjustments'));
    }

    public function export()
    {
        $products = Product::with('category')->get();
        $filename = "inventory_report_" . date('Y-m-d') . ".csv";
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$filename", "Pragma" => "no-cache" ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Product Name', 'Category', 'Cost', 'Price', 'Stock', 'Total Cost', 'Total Value']);
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->category->name ?? 'N/A',
                    $product->cost ?? 0,
                    $product->price,
                    $product->stock,
                    ($product->cost ?? 0) * $product->stock,
                    $product->price * $product->stock
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}