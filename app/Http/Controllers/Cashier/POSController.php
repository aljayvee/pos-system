<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\CustomerCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index()
    {
        $products = Product::where('stock', '>', 0)->get();
        $customers = Customer::orderBy('name')->get();
        return view('cashier.index', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|in:cash,digital,credit',
            
            // Validations
            'amount_paid' => 'required_if:payment_method,cash|numeric',
            'reference_number' => 'required_if:payment_method,digital',
            
            // Credit Validations
            'credit_details.name' => 'required_if:payment_method,credit',
            'credit_details.due_date' => 'required_if:payment_method,credit|date'
        ]);

        DB::beginTransaction();
        try {
            $customerId = $request->customer_id;

            // 1. Handle NEW Customer Creation or UPDATE Existing
            if ($request->payment_method === 'credit') {
                $details = $request->input('credit_details');

                if ($customerId === 'new') {
                    // Create New Customer
                    $newCustomer = Customer::create([
                        'name' => $details['name'],
                        'address' => $details['address'],
                        'contact' => $details['contact']
                    ]);
                    $customerId = $newCustomer->id;
                } else {
                    // Update Existing Customer
                    $customer = Customer::find($customerId);
                    if ($customer) {
                        $customer->update([
                            'address' => $details['address'],
                            'contact' => $details['contact']
                        ]);
                    }
                }
            } else {
                // For Cash/Digital, treat 'walk-in' as null
                if ($customerId === 'walk-in') $customerId = null;
            }

            // 2. Create Sale
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'customer_id' => $customerId,
                'total_amount' => $request->total_amount,
                'amount_paid' => $request->payment_method === 'credit' ? 0 : $request->amount_paid,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
            ]);

            // 3. Create Credit Ledger
            if ($request->payment_method === 'credit') {
                CustomerCredit::create([
                    'customer_id' => $customerId,
                    'sale_id' => $sale->id,
                    'total_amount' => $request->total_amount,
                    'remaining_balance' => $request->total_amount,
                    'due_date' => $request->input('credit_details.due_date'),
                ]);
            }

            // 4. Update Inventory
            foreach ($request->cart as $item) {
                $product = Product::lockForUpdate()->find($item['id']);
                if ($product->stock < $item['qty']) {
                    throw new \Exception("Insufficient stock for " . $product->name);
                }
                $product->decrement('stock', $item['qty']);
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'price' => $product->price
                ]);
            }
            
            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}