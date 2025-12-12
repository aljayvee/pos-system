<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $query = Customer::withCount(['credits as unpaid_count' => function($q){
            $q->where('is_paid', false);
        }]);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $customers = $query->orderBy('name')->paginate(10);
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
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        $customer->update($request->all());
        return back()->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->credits()->exists() || $customer->sales()->exists()) {
            return back()->with('error', 'Cannot delete customer with existing records.');
        }
        $customer->delete();
        return back()->with('success', 'Customer deleted.');
    }

    // NEW: Show Customer Profile & History
    public function show(Customer $customer)
    {
        // 1. Load Sales History
        $sales = Sale::where('customer_id', $customer->id)
                     ->with('user')
                     ->latest()
                     ->paginate(10);

        // 2. Statistics
        $totalSpent = Sale::where('customer_id', $customer->id)->sum('total_amount');
        $totalVisits = Sale::where('customer_id', $customer->id)->count();
        
        $currentDebt = $customer->credits()->where('is_paid', false)->sum('remaining_balance');
        $paidDebt = $customer->credits()->where('is_paid', true)->count();

        return view('admin.customers.show', compact('customer', 'sales', 'totalSpent', 'totalVisits', 'currentDebt', 'paidDebt'));
    }
}