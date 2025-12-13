<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $storeId = $this->getActiveStoreId();
        $suppliers = \App\Models\Supplier::where('store_id', $storeId)->get(); // <--- Filter   
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {

        $data = $request->all();
        $data['store_id'] = $this->getActiveStoreId(); // <--- Assign to current store
        $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_info' => 'nullable|string|max:255',
        ]);

        Supplier::create($data);

        return back()->with('success', 'Supplier added successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id,
            'contact_info' => 'nullable|string|max:255',
        ]);

        $supplier->update($request->all());

        return back()->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // Prevent deleting if they have transaction history
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Cannot delete supplier because they have existing purchase records.');
        }

        $supplier->delete();
        return back()->with('success', 'Supplier deleted successfully.');
    }

    public function show(\App\Models\Supplier $supplier)
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
        $lastPurchaseDate = $lastPurchase ? \Carbon\Carbon::parse($lastPurchase->purchase_date)->format('M d, Y') : 'Never';

        return view('admin.suppliers.show', compact('supplier', 'purchases', 'totalSpent', 'totalTransactions', 'lastPurchaseDate'));
    }
}