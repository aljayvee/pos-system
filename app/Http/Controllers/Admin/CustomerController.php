<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        // Sort by name for easier searching
        $customers = Customer::orderBy('name')->get();
        return view('admin.customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255', // Added validation for address
        ]);

        Customer::create($request->all());
        return back()->with('success', 'Customer added successfully.');
    }

    // NEW: Update Function
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $customer->update($request->all());
        return back()->with('success', 'Customer details updated.');
    }

    public function destroy(Customer $customer)
    {
        // Optional: Check for unpaid credits before deleting?
        // For now, we allow delete (which might cascade delete credits depending on migration)
        $customer->delete();
        return back()->with('success', 'Customer deleted.');
    }
}