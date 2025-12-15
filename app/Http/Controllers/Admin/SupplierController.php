<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Updated Index with Search & Pagination
    public function index(Request $request)
    {
        $storeId = $this->getActiveStoreId();
        
        $suppliers = Supplier::where('store_id', $storeId)
            ->withCount('purchases')
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('contact_info', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['store_id'] = $this->getActiveStoreId(); 
        
        $request->validate([
            'name' => 'required|string|max:255', // Removed unique strict check for simplicity in multi-store, or add composite unique rule if needed
            'contact_info' => 'nullable|string|max:255',
        ]);

        Supplier::create($data);

        return back()->with('success', 'Supplier added successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_info' => 'nullable|string|max:255',
        ]);

        $supplier->update($request->all());

        return back()->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Cannot delete supplier because they have existing purchase records.');
        }

        $supplier->delete();
        return back()->with('success', 'Supplier deleted successfully.');
    }

    public function show(Supplier $supplier)
    {
        // 1. Get Purchase History
        $purchases = $supplier->purchases()
                              ->with('user')
                              ->latest('purchase_date')
                              ->paginate(10);

        // 2. Calculate Stats
        $totalSpent = $supplier->purchases()->sum('total_cost');
        $totalTransactions = $supplier->purchases()->count();
        
        // Get Last Purchase Date
        $lastPurchase = $supplier->purchases()->latest('purchase_date')->first();
        // Fixed: Use created_at or purchase_date correctly. Assuming purchase_date is a datetime/date cast
        $lastPurchaseDate = $lastPurchase ? \Carbon\Carbon::parse($lastPurchase->purchase_date)->format('M d, Y') : 'Never';

        return view('admin.suppliers.show', compact('supplier', 'purchases', 'totalSpent', 'totalTransactions', 'lastPurchaseDate'));
    }
}