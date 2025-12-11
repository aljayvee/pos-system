<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchases')->latest()->get(); // Count purchases for info
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_info' => 'nullable|string|max:255',
        ]);

        Supplier::create($request->all());

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
}