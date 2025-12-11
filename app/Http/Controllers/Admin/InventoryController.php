<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog; // <--- Add this at the top
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // 1. Show Current Inventory Levels
    // 1. Show Current Inventory Levels & Financial Summary
    public function index(Request $request)
    {
        $query = \App\Models\Product::with('category')->where('stock', '>', 0);

        // Filters (Search/Category)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Clone query for totals calculation before pagination
        $allProducts = $query->get();

        // STATISTICS: Calculate Financial Value
        $totalItems = $allProducts->sum('stock');
        
        // Calculate Total Cost Value (Capital)
        $totalCostValue = $allProducts->sum(function($p) { 
            return $p->stock * ($p->cost ?? 0); 
        });
        
        // Calculate Total Sales Value (Potential Revenue)
        $totalSalesValue = $allProducts->sum(function($p) { 
            return $p->stock * $p->price; 
        });
        
        // Calculate Potential Profit
        $potentialProfit = $totalSalesValue - $totalCostValue;

        // Fetch Data for Table
        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('admin.inventory.index', compact(
            'products', 'categories', 
            'totalItems', 'totalCostValue', 'totalSalesValue', 'potentialProfit'
        ));
    }

   // 1. Show Adjustment Form
    public function adjust()
    {
        $products = \App\Models\Product::orderBy('name')->get();
        // Fetch recent adjustments for the history table
        $adjustments = \App\Models\StockAdjustment::with('product', 'user')
                        ->latest()
                        ->paginate(10);

        return view('admin.inventory.adjust', compact('products', 'adjustments'));
    }

    // 3. Process the Adjustment
    public function process(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:wastage,damage,loss,correction,return',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::lockForUpdate()->find($request->product_id);

            // Determine if we are adding or subtracting stock
            // Usually 'correction' could be +/- but let's assume this form is primarily for REMOVING bad stock.
            // If you want to add found stock, you could use Purchase or a separate "Add" type.
            // For this implementation: ALL types here DEDUCT stock.
            
            if ($product->stock < $request->quantity) {
                throw new \Exception("Cannot remove $request->quantity items. Only $product->stock in stock.");
            }

            // Deduct Stock
            $product->decrement('stock', $request->quantity);

            // Log Adjustment
            StockAdjustment::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'quantity' => $request->quantity,
                'type' => $request->type,
                'remarks' => $request->remarks
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Stock adjustment recorded successfully.');
    }
    

    // 2. Process Stock Adjustment
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:add,subtract',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'required|string',
            'remarks'    => 'nullable|string'
        ]);

        $product = \App\Models\Product::findOrFail($request->product_id);
        
        // Calculate adjustment value (Positive or Negative)
        $qty = intval($request->quantity);
        if ($request->type === 'subtract') {
            $qty = -$qty; // Make it negative
            
            // Prevent going below zero? (Optional, but safe)
            if ($product->stock + $qty < 0) {
                return back()->withErrors(['quantity' => 'Cannot remove more stock than currently available.']);
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $product, $qty) {
            // A. Create Log Record
            \App\Models\StockAdjustment::create([
                'user_id'    => \Illuminate\Support\Facades\Auth::id(),
                'product_id' => $product->id,
                'quantity'   => $qty,
                'type'     => $request->type,
                'remarks'    => $request->remarks
            ]);

            // B. Update Actual Product Stock
            $product->stock += $qty;
            $product->save();

            // C. LOG ACTION (Activity Logs Table) -> NEW
            $actionWord = $qty > 0 ? 'Added' : 'Removed';
            $absQty = abs($qty);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Stock Adjustment',
                'description' => "{$actionWord} {$absQty} stocks of '{$product->name}'. Reason: {$request->reason}"
            ]);
        });

        return back()->with('success', 'Stock adjusted successfully.');
    }

    // 4. Show Adjustment History (Optional but recommended)
    public function history()
    {
        $adjustments = StockAdjustment::with('product', 'user')->latest()->get();
        return view('admin.inventory.history', compact('adjustments'));
    }

    // NEW: Export Inventory to CSV
    public function export()
    {
        $products = Product::with('category')->get();

        $filename = "inventory_report_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            // Header Row
            fputcsv($file, ['ID', 'Product Name', 'Category', 'Cost Price', 'Selling Price', 'Current Stock', 'Stock Value (Cost)', 'Stock Value (Selling)']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->category->name ?? 'N/A',
                    $product->cost ?? 0,
                    $product->price,
                    $product->stock,
                    ($product->cost ?? 0) * $product->stock, // Total Cost Value
                    $product->price * $product->stock        // Total Sales Value
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}