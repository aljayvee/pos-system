<?php
namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index()
    {
        // Fetch products for the UI grid
        $products = Product::where('stock', '>', 0)->get();
        return view('cashier.index', compact('products'));
    }

    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'cart' => 'required|array',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|in:cash,digital,credit'
        ]);

        DB::beginTransaction(); // Ensure data integrity
        try {
            // 2. Create Sale Record
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'total_amount' => $request->total_amount,
                'amount_paid' => $request->amount_paid,
                'payment_method' => $request->payment_method,
                // Add customer_id logic here if 'credit'
            ]);

            // 3. Process Items & Update Inventory
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