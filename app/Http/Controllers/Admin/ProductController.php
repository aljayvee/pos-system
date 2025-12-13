<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\Inventory; // <--- IMPORTANT IMPORT

class ProductController extends Controller
{

    public function import(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        fgetcsv($handle); // Skip header

        DB::beginTransaction();
        try {
            $count = 0;
            while (($row = fgetcsv($handle)) !== false) {
                // Expected CSV Format: Name, Category, Price, Stock, SKU
                $name = $row[0] ?? null;
                $categoryName = $row[1] ?? 'General';
                $price = $row[2] ?? 0;
                $stock = $row[3] ?? 0;
                $sku = $row[4] ?? null;

                if (!$name) continue; 

                // Find or Create Category
                $category = Category::firstOrCreate(['name' => trim($categoryName)]);

                // Create Product Global Record
                $product = Product::create([
                    'name' => $name,
                    'category_id' => $category->id,
                    'price' => floatval($price),
                    'stock' => 0, // Global stock is less relevant in multi-store, set 0 or aggregate later
                    'sku' => $sku,
                ]);

                // Update Inventory for CURRENT Store
                $storeId = $this->getActiveStoreId();
                Inventory::updateOrCreate(
                    ['product_id' => $product->id, 'store_id' => $storeId],
                    ['stock' => intval($stock), 'reorder_point' => 10]
                );

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
        // 1. Get Active Store ID
        $storeId = $this->getActiveStoreId();

        // 2. Start Query
        $query = Product::with('category');

        // 3. Existing Filters
        if ($request->has('archived')) {
            $query->onlyTrashed();
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // 4. Dynamic Sorting Logic
        $sort = $request->input('sort', 'created_at'); 
        $dir  = $request->input('direction', 'desc');

        // Handle Sorting Types
        if ($sort === 'category') {
            $query->join('categories', 'products.category_id', '=', 'categories.id')
                  ->orderBy('categories.name', $dir)
                  ->select('products.*');
        } 
        elseif ($sort === 'stock') {
            // Sort by BRANCH SPECIFIC Stock
            $query->leftJoin('inventories', function($join) use ($storeId) {
                    $join->on('products.id', '=', 'inventories.product_id')
                         ->where('inventories.store_id', '=', $storeId);
                })
                ->orderBy(DB::raw('COALESCE(inventories.stock, 0)'), $dir)
                ->select('products.*');
        } 
        elseif (in_array($sort, ['name', 'price'])) {
            $query->orderBy($sort, $dir);
        } 
        else {
            $query->latest(); 
        }

        // 5. "Low Stock" Filter (Updated to check Branch Inventory)
        if ($request->filled('filter') && $request->filter == 'low') {
            // Filter post-query for simplicity, or add join logic
             $products = $query->get()->filter(function($p) {
                return $p->stock <= $p->reorder_point; // Accessor automatically gets branch stock
            })->toQuery()->paginate(10);
        } else {
            $products = $query->paginate(10)->withQueryString();
        }

        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    // Restore Archived Product
    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return back()->with('success', 'Product restored successfully.');
    }

    // Force Delete (Permanent)
    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        if($product->saleItems()->exists()) {
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

    // 3. Store New Product (FIXED for Multi-Store)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'unit' => 'required|string|max:20',
            'category_id' => 'required|exists:categories,id',
            'cost' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'stock' => 'integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create Product Global Record
            // We set 'stock' to 0 here because the real stock is in Inventory table
            $productData = $request->except(['stock', 'reorder_point']);
            $product = Product::create($productData);

            // 2. Identify Current Active Store
            $storeId = $this->getActiveStoreId();

            // 3. Create Inventory Record for THIS Store
            Inventory::create([
                'product_id' => $product->id,
                'store_id' => $storeId,
                'stock' => $request->stock ?? 0,
                'reorder_point' => $request->reorder_point ?? 10
            ]);

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product created successfully in current branch.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    // 4. Show Edit Form
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // 5. Update Product (FIXED for Multi-Store)
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|unique:products,sku,' . $product->id,
            'stock' => 'nullable|integer|min:0', // Ensure this is validated
            'reorder_point' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update Global Product Details (Name, Price, etc.)
            $product->update($request->except(['stock', 'reorder_point']));

            // 2. Identify Current Active Store
            $storeId = $this->getActiveStoreId();

            // 3. Update or Create Inventory for THIS Store
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $product->id, 'store_id' => $storeId],
                ['stock' => 0, 'reorder_point' => 10]
            );

            // Only update fields if they are present in request
            if ($request->has('stock')) {
                $inventory->stock = $request->stock;
            }
            if ($request->has('reorder_point')) {
                $inventory->reorder_point = $request->reorder_point;
            }
            
            $inventory->save();

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // Generate Barcode Label
    public function printBarcode(\App\Models\Product $product)
    {
        // Re-fetch product to be safe
        $product = Product::findOrFail($product->id);
        
        $isEnabled = \App\Models\Setting::where('key', 'enable_barcode')
                        ->where('store_id', $this->getActiveStoreId())
                        ->value('value') ?? '0';
        
        if ($isEnabled !== '1') {
            return back()->with('error', 'Barcode printing is currently disabled in Settings.');
        }

        if (!$product->sku) {
            return back()->with('error', 'Product does not have an SKU/Barcode to print.');
        }
        
        return view('admin.products.barcode', compact('product'));
    }

    // 6. Delete/Archive Product
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