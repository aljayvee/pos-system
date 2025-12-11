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
        // If credit, customer is required
        'customer_id' => 'required_if:payment_method,credit|nullable|exists:customers,id'
    ]);

    DB::beginTransaction();
    try {
        // 1. Create Sale
        $sale = Sale::create([
            'user_id' => Auth::id(),
            'customer_id' => $request->customer_id, // Save the customer
            'total_amount' => $request->total_amount,
            'amount_paid' => $request->payment_method === 'credit' ? 0 : $request->total_amount, // 0 paid if credit
            'payment_method' => $request->payment_method,
        ]);

        // 2. Create Credit Record if needed
        if ($request->payment_method === 'credit') {
            CustomerCredit::create([
                'customer_id' => $request->customer_id,
                'sale_id' => $sale->id,
                'total_amount' => $request->total_amount,
                'remaining_balance' => $request->total_amount,
                'due_date' => now()->addDays(30), // Default 30 days due
            ]);
        }

        // 3. Process Items & Inventory
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
        return response()->json(['success' => true, 'sale_id' => $sale->id]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
}