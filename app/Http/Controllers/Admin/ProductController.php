<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\DB; // <--- ADD THIS LINE
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ProductController extends Controller
{

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        
        // Skip the header row
        fgetcsv($handle);

        DB::beginTransaction();
        try {
            $count = 0;
            while (($row = fgetcsv($handle)) !== false) {
                // Expected CSV Format: Name, Category, Price, Stock, SKU
                // Adjust indices based on your CSV structure
                $name = $row[0] ?? null;
                $categoryName = $row[1] ?? 'General';
                $price = $row[2] ?? 0;
                $stock = $row[3] ?? 0;
                $sku = $row[4] ?? null;

                if (!$name) continue; // Skip empty rows

                // Find or Create Category
                $category = Category::firstOrCreate(['name' => trim($categoryName)]);

                // Create Product
                Product::create([
                    'name' => $name,
                    'category_id' => $category->id,
                    'price' => floatval($price),
                    'stock' => intval($stock),
                    'sku' => $sku,
                ]);
                $count++;
            }
            
            DB::commit();
            fclose($handle);
            
            return back()->with('success', "$count products imported successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // 1. Show List
   public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        // 0. Filter: Show Archived (Trash)
        if ($request->has('archived')) {
            $query->onlyTrashed(); // Query ONLY deleted items
        }

        // 1. Search Filter (Name or SKU)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%");
            });
        }

        // 2. Category Filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // 3. Stock Level Filter (Optional: "Low Stock")
        if ($request->filled('filter') && $request->filter == 'low') {
            $query->whereColumn('stock', '<=', 'reorder_point');
        }

        $products = $query->paginate(10)->withQueryString(); // Keep filters in pagination links
        
        // Fetch categories for the dropdown
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    // NEW: Restore Archived Product
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return back()->with('success', 'Product restored successfully.');
    }

    // NEW: Force Delete (Permanent)
    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->forceDelete(); // Permanently remove from DB

        return back()->with('success', 'Product permanently deleted.');
    }

    // 2. Show Create Form
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // 3. Store New Product
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'unit' => 'required|string|max:20', // New validation
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|unique:products,sku',
            'stock' => 'integer|min:0',
            'reorder_point' => 'nullable|integer|min:0'
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    // 4. Show Edit Form (This was missing!)
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // 5. Update Product (This was missing!)
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|unique:products,sku,' . $product->id,
            'reorder_point' => 'nullable|integer|min:0'
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    // NEW: Generate Barcode Label (With Toggle Check)
    public function printBarcode(\App\Models\Product $product)
    {
        // 1. Check if feature is enabled
        $isEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
        
        if ($isEnabled !== '1') {
            return back()->with('error', 'Barcode printing is currently disabled in Settings.');
        }

        // 2. Check SKU
        if (!$product->sku) {
            return back()->with('error', 'Product does not have an SKU/Barcode to print.');
        }
        
        return view('admin.products.barcode', compact('product'));
    }

    // 6. Delete Product
    public function destroy(Product $product)
    {
        // Log BEFORE deleting
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Archived Product',
            'description' => "Archived product: {$product->name}"
        ]);

        $product->delete();
        return back()->with('success', 'Product deleted successfully.');
    }
}