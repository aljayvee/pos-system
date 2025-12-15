<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    // Add this method inside SupplierController class
    public function create()
    {
        return view('admin.suppliers.create');
    }
    public function index(Request $request)
    {
        $storeId = $this->getActiveStoreId();
        
        // FIX: Used paginate() instead of get()
        // FIX: Added withCount('purchases') so the badge in the view works
        $suppliers = Supplier::where('store_id', $storeId)
            ->withCount('purchases') 
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10); // <--- Required for $suppliers->links() in the view

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['store_id'] = $this->getActiveStoreId(); 

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_info' => 'nullable|string|max:255',
        ]);

        Supplier::create($data);

        return back()->with('success', 'Supplier added successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
       $request->validate([
        // Ensure we ignore the current supplier's ID during unique check
        'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id,
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
        $purchases = $supplier->purchases()
                              ->with('user')
                              ->latest('purchase_date')
                              ->paginate(10);

        $totalSpent = $supplier->purchases()->sum('total_cost');
        $totalTransactions = $supplier->purchases()->count();
        
        $lastPurchase = $supplier->purchases()->latest('purchase_date')->first();
        $lastPurchaseDate = $lastPurchase ? \Carbon\Carbon::parse($lastPurchase->purchase_date)->format('M d, Y') : 'Never';

        return view('admin.suppliers.show', compact('supplier', 'purchases', 'totalSpent', 'totalTransactions', 'lastPurchaseDate'));
    }

    // Add this inside the SupplierController class
    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }
}