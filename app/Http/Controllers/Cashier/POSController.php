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
       // Check if Loyalty is Enabled (Default to '0' / Off)
        $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';

        return view('cashier.index', compact('products', 'customers', 'loyaltyEnabled'));
    }

    public function store(Request $request)
    {

        // Check Setting
            $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';
           
        // 1. VALIDATE FIRST
        $request->validate([
            'cart' => 'required|array',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|in:cash,digital,credit',
            'amount_paid' => 'required_if:payment_method,cash|numeric',
            'reference_number' => 'required_if:payment_method,digital',
            'credit_details.name' => 'required_if:payment_method,credit',
            'credit_details.due_date' => 'required_if:payment_method,credit|date'
        ]);

        DB::beginTransaction();
        try {
            $customerId = $request->customer_id;

            $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';

            // ... (Customer Logic) ...

            // 1. LOYALTY REDEMPTION LOGIC (Wrap in Check)
            $pointsUsed = $request->points_used ?? 0;
            $pointsDiscount = 0;

            if ($loyaltyEnabled == '1' && $pointsUsed > 0 && $customerId) {
                // ... (Existing redemption logic) ...
            } else {
                // Force 0 if disabled
                $pointsUsed = 0;
                $pointsDiscount = 0;
            }

            // 3. LOYALTY ACCUMULATION LOGIC (Wrap in Check)
            if ($loyaltyEnabled == '1' && $customerId) {
                $pointsEarned = floor($request->total_amount / 100);
                if ($pointsEarned > 0) {
                    $customer = Customer::find($customerId);
                    if ($customer) {
                        $customer->increment('points', $pointsEarned);
                    }
                }
            }

            
            // 2. HANDLE CUSTOMER (Create New or Update Existing)
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
                    // Update Existing Customer details if provided
                    $customer = Customer::find($customerId);
                    if ($customer) {
                        $customer->update([
                            'address' => $details['address'],
                            'contact' => $details['contact']
                        ]);
                    }
                }
            } else {
                // For Cash/Digital, if 'walk-in' is selected, set customer_id to null
                if ($customerId === 'walk-in') $customerId = null;
            }

            // 3. CREATE SALE (Now that we have the $customerId)
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'customer_id' => $customerId,
                'total_amount' => $request->total_amount,
                'amount_paid' => $request->payment_method === 'credit' ? 0 : $request->amount_paid,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->payment_method === 'digital' ? $request->reference_number : null, 
            ]);

            // 4. LOYALTY POINTS LOGIC (After sale is created)
            if ($customerId) {
                $pointsEarned = floor($request->total_amount / 100);
                if ($pointsEarned > 0) {
                    $customer = Customer::find($customerId);
                    if ($customer) {
                        $customer->increment('points', $pointsEarned);
                    }
                }
            }

            // 5. CREATE CREDIT LEDGER
            if ($request->payment_method === 'credit') {
                // Fix date handling
                $dueDate = $request->input('credit_details.due_date');
                if (empty($dueDate)) $dueDate = null;

                CustomerCredit::create([
                    'customer_id' => $customerId,
                    'sale_id' => $sale->id,
                    'total_amount' => $request->total_amount,
                    'remaining_balance' => $request->total_amount,
                    'amount_paid' => 0,
                    'is_paid' => false,
                    'due_date' => $dueDate, 
                ]);
            }

            // 6. UPDATE INVENTORY
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
            
            // Return Success
            return response()->json(['success' => true, 'sale_id' => $sale->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- NEW METHOD: Show Receipt ---
    public function showReceipt(Sale $sale)
    {
        // Eager load relationships for efficiency
        $sale->load('saleItems.product', 'user', 'customer');
        return view('cashier.receipt', compact('sale'));
    }
}