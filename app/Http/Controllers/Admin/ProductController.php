<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

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
    public function index()
    {
        $products = Product::with('category')->latest()->get();
        return view('admin.products.index', compact('products'));
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

    // 6. Delete Product
    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product deleted successfully.');
    }
}