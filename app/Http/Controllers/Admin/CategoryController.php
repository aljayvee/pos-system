<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // 1. Show the list of categories
    public function index()
    {
        $categories = Category::withCount('products')->get();
        return view('admin.categories.index', compact('categories'));
    }

    // New: Get products for a category (AJAX)
    public function getProducts(Category $category)
    {
        return response()->json($category->products()
            ->select('id', 'name', 'price', 'image') 
            ->get()->map(function($p) {
                return [
                    'name' => $p->name,
                    'price' => number_format($p->price, 2),
                    'image' => $p->image ? asset('storage/' . $p->image) : null
                ];
            }));
    }

    // 2. Save a new category
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = Category::create($request->all());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Category added successfully'
            ]);
        }

        return back()->with('success', 'Category added!');
    }

    // 3. Update a category
    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category->update($request->all());
        return back()->with('success', 'Category updated!');
    }

    // 4. Delete a category
    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted!');
    }
}