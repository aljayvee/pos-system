<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CreditPayment; 
use App\Models\Customer;
use App\Models\CustomerCredit;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index()
    {
        // 1. Get Current Active Store
        $storeId = $this->getActiveStoreId(); 

        // 2. Fetch Products (Already Fixed: Checks Branch Inventory)
        $products = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->where('inventories.stock', '>', 0)
            ->whereNull('products.deleted_at')
            ->select(
                'products.*', 
                'inventories.stock as current_stock'
            )
            ->get();

        // 3. Fetch Customers (FIXED: Filter Debt by Branch)
        // We only sum up credits that belong to Sales made in THIS store.
        $customers = Customer::withSum(['credits as balance' => function($q) use ($storeId) {
            $q->where('is_paid', false)
              ->whereHas('sale', function($q2) use ($storeId) {
                  $q2->where('store_id', $storeId);
              });
        }], 'remaining_balance')
        ->orderBy('name')
        ->get();

        // 4. Fetch Settings & Categories
        $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';
        $categories = \App\Models\Category::has('products')->orderBy('name')->get();

        return view('cashier.index', compact('products', 'customers', 'categories', 'loyaltyEnabled'));
    }

    // Process Debt Payment (FIXED: Only pay Branch-Specific Debt)
    public function payCredit(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $paymentAmount = $request->amount;
        $customerId = $request->customer_id;
        $storeId = $this->getActiveStoreId(); // Get Current Branch

        DB::beginTransaction();
        try {
            // FIXED: Only fetch credits linked to sales from THIS store
            $credits = CustomerCredit::where('customer_id', $customerId)
                        ->where('is_paid', false)
                        ->whereHas('sale', function($q) use ($storeId) {
                            $q->where('store_id', $storeId);
                        })
                        ->orderBy('created_at', 'asc')
                        ->lockForUpdate()
                        ->get();

            if ($credits->isEmpty()) {
                throw new \Exception("Customer has no outstanding balance in this branch.");
            }

            $totalDebt = $credits->sum('remaining_balance');
            
            if ($paymentAmount > $totalDebt + 0.01) { 
                throw new \Exception("Payment exceeds branch debt of ₱" . number_format($totalDebt, 2));
            }

            $remainingPayment = $paymentAmount;

            foreach ($credits as $credit) {
                if ($remainingPayment <= 0) break;

                $toPay = min($remainingPayment, $credit->remaining_balance);

                $credit->amount_paid += $toPay;
                $credit->remaining_balance -= $toPay;

                if ($credit->remaining_balance <= 0) {
                    $credit->remaining_balance = 0;
                    $credit->is_paid = true;
                }
                $credit->save();

                // Create Log Entry
                CreditPayment::create([
                    'customer_credit_id' => $credit->credit_id ?? $credit->id, 
                    'user_id' => Auth::id(),
                    'amount' => $toPay,
                    'payment_date' => now(),
                    'notes' => 'Paid via POS (Branch Collection)'
                ]);

                $remainingPayment -= $toPay;
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment collected successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
            // 2. Identify Current Active Store
            $storeId = $this->getActiveStoreId();

            // 3. Settings & Customer Logic
            $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';
            $loyaltyRatio = \App\Models\Setting::where('key', 'loyalty_ratio')->value('value') ?? 100;
            $pointsValue = \App\Models\Setting::where('key', 'points_conversion')->value('value') ?? 1;

            $customerId = $request->customer_id;
            $customer = null;

            if ($customerId === 'new' && $request->payment_method === 'credit') {
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
                $customer = Customer::find($customerId);
            } else {
                $customerId = null;
            }

            // 4. Loyalty Redemption
            $pointsUsed = 0;
            $pointsDiscount = 0;
            if ($loyaltyEnabled == '1' && $customer && $request->points_used > 0) {
                if ($customer->points >= $request->points_used) {
                    $pointsUsed = $request->points_used;
                    $pointsDiscount = $pointsUsed * $pointsValue;
                    $customer->decrement('points', $pointsUsed);
                } else {
                    throw new \Exception("Customer does not have enough points.");
                }
            }

            // 5. Create Sale Record (Associated with Current Store)
            $sale = Sale::create([
                'store_id' => $storeId,
                'user_id' => Auth::id(),
                'customer_id' => $customerId,
                'total_amount' => $request->total_amount, 
                'amount_paid' => $request->payment_method === 'credit' ? 0 : ($request->amount_paid ?? 0),
                'payment_method' => $request->payment_method,
                'reference_number' => $request->payment_method === 'digital' ? $request->reference_number : null,
                'points_used' => $pointsUsed,
                'points_discount' => $pointsDiscount,
            ]);

            // 6. PROCESS INVENTORY & SALE ITEMS
            foreach ($request->cart as $item) {
                
                // Find Inventory Record for this specific Store
                $inventory = Inventory::where('product_id', $item['id'])
                                ->where('store_id', $storeId)
                                ->lockForUpdate()
                                ->first();

                if (!$inventory) {
                    $prod = Product::find($item['id']);
                    $prodName = $prod ? $prod->name : "Item #".$item['id'];
                    throw new \Exception("Stock record not found for '{$prodName}' in this branch.");
                }

                if ($inventory->stock < $item['qty']) {
                    $prodName = $inventory->product->name ?? 'Item';
                    throw new \Exception("Insufficient stock for '$prodName' in this branch (Available: {$inventory->stock}).");
                }

                // Deduct Stock from BRANCH Inventory
                $inventory->decrement('stock', $item['qty']);

                // Create Sale Item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],  
                    'cost' => $inventory->product->cost ?? 0 
                ]);
            }

            // 7. Earn Points
            if ($loyaltyEnabled == '1' && $customer) {
                $pointsEarned = floor($request->total_amount / $loyaltyRatio);
                if ($pointsEarned > 0) {
                    $customer->increment('points', $pointsEarned);
                }
            }

            // 8. Credit Logic
            if ($request->payment_method === 'credit' && $customer) {
                $dueDate = $request->input('credit_details.due_date');
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
            return response()->json(['success' => true, 'sale_id' => $sale->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function showReceipt(Sale $sale)
    {
        $sale->load('saleItems.product', 'user', 'customer');
        return view('cashier.receipt', compact('sale'));
    }
}