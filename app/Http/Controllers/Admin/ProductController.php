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
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        fgetcsv($handle); // Skip header
        
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
        $storeId = $this->getActiveStoreId();
        $query = Product::with('category'); 
        $query->whereHas('inventories', function($q) use ($storeId) {
             $q->where('store_id', $storeId);
        });

        // 0. Filter: Show Archived (Trash)
        if ($request->has('archived')) {
            $query->onlyTrashed(); // Query ONLY deleted items
        }

        // E. Search Filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // 2. Category Filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // G. Stock Level Filter
        if ($request->filled('filter') && $request->filter == 'low') {
            // Note: This checks the Accessor logic in Product model (which uses session active_store_id)
            $query->get()->filter(function($p) {
                return $p->stock <= $p->reorder_point;
            });
            // Optimization: For large datasets, use join on inventories table instead of filter()
        }

        $products = $query->latest()->paginate(10); // Keep filters in pagination links
        
        // Fetch categories for the dropdown
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    // NEW: Restore Archived Product
    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return back()->with('success', 'Product restored successfully.');
    }

    // NEW: Force Delete (Permanent)
    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        if($product->saleItems()->exists()) { // Removed purchaseItems check for simplicity unless needed
            return back()->with('error', 'Cannot permanently delete. This item has sales history.');
        }
        $product->forceDelete();
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
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku', // <--- New
            'stock' => 'integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
        ]);

        $product = Product::create($request->all());

        // In Multi-Store, we should create an Inventory record for the current store
        $storeId = $this->getActiveStoreId();

        // Check if Inventory exists, if not create it
        \App\Models\Inventory::create([
            'product_id' => $product->id,
            'store_id' => $storeId,
            'stock' => $request->stock ?? 0,
            'reorder_point' => $request->reorder_point ?? 10
        ]);

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
            'reorder_point' => 'nullable|integer|min:0',
            
        ]);

        $product->update($request->all());

        // Update Inventory Reorder Point for this store
        $storeId = $this->getActiveStoreId();
        $inventory = \App\Models\Inventory::where('product_id', $product->id)
                        ->where('store_id', $storeId)->first();

                        if ($inventory && $request->has('reorder_point')) {
            $inventory->reorder_point = $request->reorder_point;
            $inventory->save();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    // NEW: Generate Barcode Label (With Toggle Check)
    public function printBarcode(\App\Models\Product $product)
    {

        $product = Product::findOrFail($id);
        $isEnabled = \App\Models\Setting::where('key', 'enable_barcode')
                        ->where('store_id', $this->getActiveStoreId()) // Check Branch Setting
                        ->value('value') ?? '0';
        
        if ($isEnabled !== '1') {
            return back()->with('error', 'Barcode printing is currently disabled in Settings.');
        }

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
            'store_id' => $this->getActiveStoreId(),
            'action' => 'Archived Product',
            'description' => "Archived product: {$product->name}"
        ]);

        $product->delete();
        return back()->with('success', 'Product deleted successfully.');
    }
}