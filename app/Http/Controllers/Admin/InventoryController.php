<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // 1. Show Current Inventory Levels
    public function index()
    {
        // Fetch products with their category
        $products = Product::with('category')->orderBy('stock', 'asc')->get();
        return view('admin.inventory.index', compact('products'));
    }

    // 2. Show the Adjustment Form
    public function adjust()
    {
        $products = Product::orderBy('name')->get();
        return view('admin.inventory.adjust', compact('products'));
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