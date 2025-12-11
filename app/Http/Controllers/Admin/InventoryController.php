<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // 1. Show Inventory & Stats
    public function index(Request $request)
    {
        $query = Product::with('category')->where('stock', '>', 0);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $allProducts = $query->get();

        // Stats
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
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:add,subtract',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'required|string',
            'remarks'    => 'nullable|string'
        ]);

        $product = Product::findOrFail($request->product_id);
        
        $qty = intval($request->quantity);
        if ($request->type === 'subtract') {
            $qty = -$qty;
            if ($product->stock + $qty < 0) {
                return back()->withErrors(['quantity' => 'Cannot remove more stock than available.']);
            }
        }

        DB::transaction(function () use ($request, $product, $qty) {
            // A. Log Adjustment
            StockAdjustment::create([
                'user_id'    => Auth::id(),
                'product_id' => $product->id,
                'quantity'   => $qty,
                'type'       => $request->reason,
                'remarks'    => $request->remarks
            ]);

            // B. Update Stock
            $product->stock += $qty;
            $product->save();

            // C. Activity Log
            $actionWord = $qty > 0 ? 'Added' : 'Removed';
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Stock Adjustment',
                'description' => "{$actionWord} " . abs($qty) . " stocks of '{$product->name}'. Reason: {$request->reason}"
            ]);
        });

        return back()->with('success', 'Stock adjusted successfully.');
    }

    public function history()
    {
        $adjustments = StockAdjustment::with('product', 'user')->latest()->get();
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