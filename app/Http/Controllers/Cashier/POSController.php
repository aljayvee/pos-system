<?php
namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
// Add these at the top
use App\Models\Customer;
use App\Models\CustomerCredit; // We will create this model next

class POSController extends Controller
{
    public function index()
    {
        // Fetch products for the UI grid
        $products = Product::where('stock', '>', 0)->get();
    $customers = Customer::all(); // Add this
    return view('cashier.index', compact('products', 'customers'));
    }

    // Replace the store method with this updated version
public function store(Request $request)
{
    $request->validate([
        'cart' => 'required|array',
        'total_amount' => 'required|numeric',
        'payment_method' => 'required|in:cash,digital,credit',
        
        // Validation per flow
        'amount_paid' => 'required_if:payment_method,cash|numeric',
        'reference_number' => 'required_if:payment_method,digital',
        'customer_id' => 'required_if:payment_method,credit', // Credit must have ID
        'credit_details.due_date' => 'required_if:payment_method,credit|date'
    ]);

    DB::beginTransaction();
    try {
        // 1. Handle Credit Form Updates (Flow 3 Requirement)
        if ($request->payment_method === 'credit' && $request->customer_id) {
            $customer = Customer::find($request->customer_id);
            if ($customer) {
                // Update customer profile with latest info from form
                $customer->update([
                    'address' => $request->input('credit_details.address'),
                    'contact' => $request->input('credit_details.contact')
                ]);
            }
        }

        // 2. Create Sale
        $sale = Sale::create([
            'user_id' => Auth::id(),
            'customer_id' => $request->customer_id, // Null if walk-in
            'total_amount' => $request->total_amount,
            'amount_paid' => $request->payment_method === 'credit' ? 0 : $request->amount_paid,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
        ]);

        // 3. Create Credit Ledger
        if ($request->payment_method === 'credit') {
            CustomerCredit::create([
                'customer_id' => $request->customer_id,
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