<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\Inventory; // <--- IMPORTANT IMPORT

class ProductController extends Controller
{

    public function import(Request $request)
    {
        if (!Auth::user()->hasPermission(\App\Enums\Permission::INVENTORY_EDIT->value)) {
            abort(403);
        }
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
            });

            if ($products->isEmpty()) {
                // Return an empty paginator if no results found
                $products = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            } else {
                $products = $products->toQuery()->paginate(10);
            }
        } else {
            $products = $query->paginate(10)->withQueryString();
        }

        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    // Restore Archived Product
    public function restore($id)
    {
        if (!Auth::user()->hasPermission(\App\Enums\Permission::INVENTORY_EDIT->value)) {
            abort(403);
        }
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return back()->with('success', 'Product restored successfully.');
    }

    // Force Delete (Permanent)
    public function forceDelete($id)
    {
        if (!Auth::user()->hasPermission(\App\Enums\Permission::INVENTORY_EDIT->value)) {
            abort(403);
        }
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
        if (!Auth::user()->hasPermission(\App\Enums\Permission::INVENTORY_EDIT->value)) {
            abort(403);
        }
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // ... inside ProductController class ...

    // --- ADD THIS METHOD ---
    // 1. UPDATE THE VALIDATION METHOD
    public function checkDuplicate(Request $request)
    {
        $sku = $request->input('sku');
        $name = strtolower($request->input('name'));
        $excludeId = $request->input('exclude_id'); // New parameter

        $query = Product::query();

        // Check for SKU or Name match
        $query->where(function($q) use ($sku, $name) {
            if ($sku) {
                $q->where('sku', $sku);
            }
            if ($name) {
                $q->orWhereRaw('LOWER(name) = ?', [$name]);
            }
        });

        // IMPORTANT: Exclude the product we are currently editing
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $product = $query->first();

        if ($product) {
            return response()->json([
                'exists' => true,
                'message' => "Product '{$product->name}' (SKU: {$product->sku}) already exists.",
                'product_id' => $product->id
            ]);
        }

        return response()->json(['exists' => false]);
    }
    // -----------------------

    public function store(Request $request)
{
    if (!Auth::user()->hasPermission(\App\Enums\Permission::INVENTORY_EDIT->value)) {
        abort(403);
    }
    $validated = $request->validate([
        'name' => 'required',
        'price' => 'required|numeric',
        'unit' => 'required|string|max:50', // Extended max length
        'category_id' => 'required|exists:categories,id',
        'cost' => 'nullable|numeric|min:0',
        'sku' => 'nullable|string|unique:products,sku',
        'stock' => 'integer|min:0',
        'reorder_point' => 'nullable|integer|min:0',
        'expiration_date' => 'nullable|date',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        'tiers.*.quantity' => 'required|integer|min:2',
        'tiers.*.price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        // 1. Handle Image Upload FIRST so we have the path
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // 2. Create Global Product Record (ONLY ONCE)
        $product = Product::create([
            'name' => \Illuminate\Support\Str::title($request->name),
            'price' => $request->price,
            'unit' => $request->unit,
            'category_id' => $request->category_id,
            'cost' => $request->cost,
            'sku' => $request->sku,
            'expiration_date' => $request->expiration_date,
            'image' => $imagePath,
            'stock' => 0, // dummy global stock
        ]);

        // Save Pricing Tiers
        if ($request->has('tiers')) {
            foreach ($request->tiers as $tier) {
                if ($tier['quantity'] && $tier['price']) {
                    $product->pricingTiers()->create([
                        'quantity' => $tier['quantity'],
                        'price' => $tier['price'],
                        'name' => $tier['name'] ?? null
                    ]);
                }
            }
        }

        // 3. Create Inventory Record for current branch
        $storeId = $this->getActiveStoreId();
        Inventory::create([
            'product_id' => $product->id,
            'store_id' => $storeId,
            'stock' => $request->stock ?? 0,
            'reorder_point' => $request->reorder_point ?? 10
        ]);

        // LOGGING
        ActivityLog::create([
            'user_id' => Auth::id(),
            'store_id' => $storeId,
            'action' => 'Created Product',
            'description' => "Created product: {$product->name} (Price: {$product->price}, Stock: {$request->stock})"
        ]);

        DB::commit();
        return redirect()->route('products.index')->with('success', 'Product created successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        if($imagePath) Storage::disk('public')->delete($imagePath);
        
        // Use back() to stay on the page and show the specific error message
        return back()->withInput()->with('error', 'Failed to add product: ' . $e->getMessage());
    }
}

    // 4. Show Edit Form
    public function edit(Product $product)
    {
        if (!Auth::user()->hasPermission(\App\Enums\Permission::INVENTORY_EDIT->value)) {
            abort(403);
        }
        $product->load('pricingTiers'); // Eager load tiers
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // 5. Update Product (FIXED for Multi-Store)
    public function update(Request $request, Product $product)
    {
        if (!Auth::user()->hasPermission(\App\Enums\Permission::INVENTORY_EDIT->value)) {
            abort(403);
        }
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|unique:products,sku,' . $product->id,
            'unit' => 'required|string|max:50', // Extended max length
            'stock' => 'nullable|integer|min:0', // Ensure this is validated
            'reorder_point' => 'nullable|integer|min:0',
            'expiration_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'tiers.*.quantity' => 'required|integer|min:2',
            'tiers.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {

            $data = $request->except(['stock', 'reorder_point', 'image', 'tiers']);
            $data['name'] = Str::title($request->name);

            // Handle Image Upload
            if ($request->hasFile('image')) {
                // Delete Old Image
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                // Store New
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            // Apply Title Case Formatting (Consistency)
            $request->merge([
                'name' => \Illuminate\Support\Str::title($request->name) 
            ]);

            // Update Global Product Details
            $product->update($data);

            // Sync Pricing Tiers
            $product->pricingTiers()->delete(); // Clear old tiers
            if ($request->has('tiers')) {
                foreach ($request->tiers as $tier) {
                    if ($tier['quantity'] && $tier['price']) {
                        $product->pricingTiers()->create([
                            'quantity' => $tier['quantity'],
                            'price' => $tier['price'],
                            'name' => $tier['name'] ?? null
                        ]);
                    }
                }
            }

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

            // LOGGING
            ActivityLog::create([
                'user_id' => Auth::id(),
                'store_id' => $storeId,
                'action' => 'Updated Product',
                'description' => "Updated product: {$product->name} (Price: {$product->price}, Stock: {$inventory->stock})"
            ]);

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
        if (!Auth::user()->hasPermission(\App\Enums\Permission::INVENTORY_EDIT->value)) {
            abort(403);
        }
        // Log BEFORE deleting
        ActivityLog::create([
            'user_id' => auth()->id(),
            'store_id' => $this->getActiveStoreId(),
            'action' => 'Archived Product',
            'description' => "Archived product: {$product->name}"
        ]);

        Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Product deleted successfully.');
    }
}