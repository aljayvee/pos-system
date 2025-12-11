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
        // NEW: Fetch Categories for the filter buttons
        $categories = \App\Models\Category::has('products')->orderBy('name')->get();
       // Check if Loyalty is Enabled (Default to '0' / Off)
        $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';

        return view('cashier.index', compact('products', 'customers', 'categories', 'loyaltyEnabled'));
    }

    public function store(Request $request)
    {
        // 1. VALIDATION
        $request->validate([
            'cart' => 'required|array',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|in:cash,digital,credit',
            'amount_paid' => 'nullable|numeric',
            'reference_number' => 'required_if:payment_method,digital',
            'credit_details.name' => 'required_if:payment_method,credit',
            'credit_details.due_date' => 'required_if:payment_method,credit|date'
        ]);

        DB::beginTransaction();
        try {
            // 2. FETCH SETTINGS
            $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';
            $loyaltyRatio = \App\Models\Setting::where('key', 'loyalty_ratio')->value('value') ?? 100; // Default 100
            $pointsValue = \App\Models\Setting::where('key', 'points_conversion')->value('value') ?? 1;

            $customerId = $request->customer_id;
            $customer = null;

            // 3. RESOLVE CUSTOMER
            if ($customerId === 'new' && $request->payment_method === 'credit') {
                // Create New Customer
                $details = $request->input('credit_details');
                $customer = Customer::create([
                    'name' => $details['name'],
                    'address' => $details['address'] ?? null,
                    'contact' => $details['contact'] ?? null,
                    'points' => 0
                ]);
                $customerId = $customer->id;
            } 
            elseif ($customerId && $customerId !== 'walk-in' && $customerId !== 'new') {
                // Existing Customer
                $customer = Customer::find($customerId);
            } else {
                // Walk-in (No ID)
                $customerId = null;
            }

            // 4. PROCESS LOYALTY REDEMPTION (Use Points)
            $pointsUsed = 0;
            $pointsDiscount = 0;

            if ($loyaltyEnabled == '1' && $customer && $request->points_used > 0) {
                if ($customer->points >= $request->points_used) {
                    $pointsUsed = $request->points_used;
                    $pointsDiscount = $pointsUsed * $pointsValue;
                    
                    // Deduct points immediately
                    $customer->decrement('points', $pointsUsed);
                } else {
                    throw new \Exception("Customer does not have enough points.");
                }
            }

            // 5. CREATE SALE RECORD
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'customer_id' => $customerId,
                'total_amount' => $request->total_amount, // Net amount (after discount)
                'amount_paid' => $request->payment_method === 'credit' ? 0 : ($request->amount_paid ?? 0),
                'payment_method' => $request->payment_method,
                'reference_number' => $request->payment_method === 'digital' ? $request->reference_number : null,
                'points_used' => $pointsUsed,
                'points_discount' => $pointsDiscount,
            ]);

            // 6. PROCESS INVENTORY & SALE ITEMS
            foreach ($request->cart as $item) {
                // Lock row to prevent race conditions
                $product = Product::lockForUpdate()->find($item['id']);
                
                if (!$product) {
                    throw new \Exception("Product ID " . $item['id'] . " not found.");
                }
                if ($product->stock < $item['qty']) {
                    throw new \Exception("Insufficient stock for " . $product->name);
                }

                // Deduct Stock
                $product->decrement('stock', $item['qty']);

                // Create Sale Item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'price' => $product->price
                ]);
            }

            // 7. PROCESS LOYALTY ACCUMULATION (Earn Points)
            // Logic: Earn points based on the Total Amount Paid / Ratio
            if ($loyaltyEnabled == '1' && $customer) {
                // Use the configured ratio (e.g., 1 point per 50 pesos)
                $pointsEarned = floor($request->total_amount / $loyaltyRatio);
                
                if ($pointsEarned > 0) {
                    $customer->increment('points', $pointsEarned);
                }
            }

            // 8. PROCESS CREDIT (If Credit Sale)
            if ($request->payment_method === 'credit' && $customer) {
                $dueDate = $request->input('credit_details.due_date');
                
                // Update customer info if provided
                if ($request->input('credit_details.contact') || $request->input('credit_details.address')) {
                    $customer->update([
                        'contact' => $request->input('credit_details.contact') ?? $customer->contact,
                        'address' => $request->input('credit_details.address') ?? $customer->address,
                    ]);
                }

                CustomerCredit::create([
                    'customer_id' => $customer->id,
                    'sale_id' => $sale->id,
                    'total_amount' => $request->total_amount,
                    'remaining_balance' => $request->total_amount,
                    'amount_paid' => 0,
                    'is_paid' => false,
                    'due_date' => $dueDate,
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