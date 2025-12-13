<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn; // Import SalesReturn
use App\Models\CreditPayment; 
use App\Models\Customer;
use App\Models\CustomerCredit;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt; // Import Crypt

class POSController extends Controller
{
    public function index()
    {
        $storeId = $this->getActiveStoreId(); 

        $products = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->where('inventories.stock', '>', 0)
            ->whereNull('products.deleted_at')
            ->select('products.*', 'inventories.stock as current_stock')
            ->get();

        $customers = Customer::withSum(['credits as balance' => function($q) use ($storeId) {
            $q->where('is_paid', false)
              ->whereHas('sale', function($q2) use ($storeId) {
                  $q2->where('store_id', $storeId);
              });
        }], 'remaining_balance')->orderBy('name')->get();

        $categories = \App\Models\Category::has('products')->orderBy('name')->get();

        // FETCH SETTINGS
        $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';
        
        // --- FIXED: Fetch BIR/Tax Setting ---
        $birEnabled = \App\Models\Setting::where('key', 'enable_tax')->value('value') ?? '0';

        return view('cashier.index', compact('products', 'customers', 'categories', 'loyaltyEnabled', 'birEnabled'));
    }

    public function payCredit(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $paymentAmount = $request->amount;
        $customerId = $request->customer_id;
        $storeId = $this->getActiveStoreId(); 

        DB::beginTransaction();
        try {
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

    // --- SALES RETURN LOGIC ---
    public function searchSale(Request $request)
    {
        $request->validate(['query' => 'required']);
        $q = $request->query('query');

        $sale = Sale::with(['saleItems.product', 'customer'])
                    ->where('id', $q)
                    ->orWhere('reference_number', $q)
                    ->first();

        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.']);
        }

        $items = $sale->saleItems->map(function($item) {
            $returned = SalesReturn::where('sale_id', $item->sale_id)
                                   ->where('product_id', $item->product_id)
                                   ->sum('quantity');
            
            return [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'sold_qty' => $item->quantity,
                'price' => $item->price,
                'returned_qty' => $returned,
                'available_qty' => $item->quantity - $returned
            ];
        });

        return response()->json([
            'success' => true,
            'sale' => [
                'id' => $sale->id,
                'date' => $sale->created_at->format('M d, Y h:i A'),
                'customer' => $sale->customer ? $sale->customer->name : 'Walk-in',
                'total' => $sale->total_amount,
                'payment_method' => ucfirst($sale->payment_method)
            ],
            'items' => $items
        ]);
    }

    public function processReturn(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:good,damaged',
        ]);

        $saleId = $request->sale_id;
        $storeId = $this->getActiveStoreId();

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                $pid = $itemData['product_id'];
                $qty = $itemData['quantity'];
                
                $saleItem = SaleItem::where('sale_id', $saleId)->where('product_id', $pid)->firstOrFail();
                $alreadyReturned = SalesReturn::where('sale_id', $saleId)->where('product_id', $pid)->sum('quantity');

                if (($qty + $alreadyReturned) > $saleItem->quantity) {
                    throw new \Exception("Cannot return more than sold qty for {$saleItem->product->name}");
                }

                $refundAmount = $saleItem->price * $qty;

                SalesReturn::create([
                    'sale_id' => $saleId,
                    'product_id' => $pid,
                    'user_id' => Auth::id(),
                    'quantity' => $qty,
                    'refund_amount' => $refundAmount,
                    'condition' => $itemData['condition'],
                    'reason' => $itemData['reason'] ?? 'Customer Return'
                ]);

                if ($itemData['condition'] === 'good') {
                    $inventory = Inventory::firstOrCreate(
                        ['product_id' => $pid, 'store_id' => $storeId],
                        ['stock' => 0, 'reorder_point' => 10]
                    );
                    $inventory->increment('stock', $qty);
                }

                $sale = Sale::find($saleId);
                if ($sale->payment_method === 'credit' && $sale->customer_id) {
                    $credit = CustomerCredit::where('sale_id', $saleId)->first();
                    if ($credit) {
                        $credit->remaining_balance -= $refundAmount;
                        if ($credit->remaining_balance <= 0) {
                            $credit->remaining_balance = 0;
                            $credit->is_paid = true;
                        }
                        $credit->save();
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Return processed successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|in:cash,digital,credit',
            'amount_paid' => 'nullable|numeric',
            'reference_number' => 'required_if:payment_method,digital',
            'credit_details.name' => 'required_if:payment_method,credit',
            'credit_details.due_date' => 'required_if:payment_method,credit|date',

            // NEW: Details required ONLY if creating a NEW Customer
            'credit_details.name' => 'required_if:customer_id,new',
            'credit_details.address' => 'required_if:customer_id,new',
            'credit_details.contact' => 'required_if:customer_id,new',
        ],
    [
        // Custom Error Messages for clarity
            'credit_details.name.required_if' => 'Customer Name is required.',
            'credit_details.address.required_if' => 'Full Address is required for new customers.',
            'credit_details.contact.required_if' => 'Mobile Number is required for new customers.',
            'credit_details.due_date.required_if' => 'Due Date is required for credit transactions.'
    ]);

        DB::beginTransaction();
        try {
            $storeId = $this->getActiveStoreId();

            $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';
            $loyaltyRatio = \App\Models\Setting::where('key', 'loyalty_ratio')->value('value') ?? 100;
            $pointsValue = \App\Models\Setting::where('key', 'points_conversion')->value('value') ?? 1;

            $customerId = $request->customer_id;
            $customer = null;

            // 2. CREATE NEW CUSTOMER WITH FULL DETAILS
            if ($customerId === 'new' && $request->payment_method === 'credit') {
                $customer = Customer::create([
                    'store_id' => $storeId, 
                    'name' => $request->input('credit_details.name'),
                    'address' => $request->input('credit_details.address'),
                    'contact' => $request->input('credit_details.contact'),
                    'points' => 0
                ]);
                $customerId = $customer->id;
            } 
            elseif ($customerId && $customerId !== 'walk-in' && $customerId !== 'new') {
                $customer = Customer::find($customerId);
            } else {
                $customerId = null;
            }

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

            foreach ($request->cart as $item) {
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

                $inventory->decrement('stock', $item['qty']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],  
                    'cost' => $inventory->product->cost ?? 0 
                ]);
            }

            if ($loyaltyEnabled == '1' && $customer) {
                $pointsEarned = floor($request->total_amount / $loyaltyRatio);
                if ($pointsEarned > 0) {
                    $customer->increment('points', $pointsEarned);
                }
            }

            // 3. CREDIT RECORD CREATION
            // 6. RECORD CREDIT (UTANG)
            if ($request->payment_method === 'credit' && $customer) {
                $dueDate = $request->input('credit_details.due_date');
                
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

    // --- API: Get Latest Debtors ---
    public function getDebtors()
    {
        $storeId = $this->getActiveStoreId();

        $debtors = Customer::withSum(['credits as balance' => function($q) use ($storeId) {
            $q->where('is_paid', false)
              ->whereHas('sale', function($q2) use ($storeId) {
                  $q2->where('store_id', $storeId);
              });
        }], 'remaining_balance')
        ->get()
        ->filter(function($customer) {
            return $customer->balance > 0; // Only return people with debt
        })
        ->values(); // Reset array keys for JSON

        return response()->json($debtors);
    }

    // --- NEW: BIR COMPLIANCE REPORTS (X/Z Reading) ---
    public function showReading(Request $request, $type = 'x')
    {
        $storeId = $this->getActiveStoreId();
        $date = \Carbon\Carbon::now()->toDateString();

        // 1. Fetch Today's Gross Sales
        $todaySales = Sale::where('store_id', $storeId)
                          ->whereDate('created_at', $date)
                          ->get();

        // 2. Fetch Today's Returns (Refunds)
        $todayReturns = SalesReturn::whereDate('sales_returns.created_at', $date)
                            ->whereHas('sale', function($q) use ($storeId) {
                                $q->where('store_id', $storeId);
                            })
                            ->sum('refund_amount');

        // 3. Decrypt TIN
        $rawTin = \App\Models\Setting::where('key', 'store_tin')->value('value');
        try {
            $tin = $rawTin ? \Illuminate\Support\Facades\Crypt::decryptString($rawTin) : '000-000-000';
        } catch (\Exception $e) {
            $tin = $rawTin; // Fallback if plain text
        }

        // 4. Calculate Stats
        $grossSales = $todaySales->sum('total_amount');
        $netSales = $grossSales - $todayReturns;

        $data = [
            'type' => strtoupper($type) . '-READING',
            'date' => now()->format('Y-m-d H:i:s'),
            'store_name' => \App\Models\Setting::where('key', 'store_name')->value('value'),
            'tin' => $tin,
            'machine_no' => 'POS-' . str_pad($storeId, 3, '0', STR_PAD_LEFT),
            
            'gross_sales' => $grossSales,
            'returns' => $todayReturns,
            'net_sales' => $netSales,
            'trans_count' => $todaySales->count(),
            'beg_or' => $todaySales->first()->id ?? '-',
            'end_or' => $todaySales->last()->id ?? '-',
            
            'cash_sales' => $todaySales->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $todaySales->where('payment_method', 'digital')->sum('total_amount'), 
            'credit_sales' => $todaySales->where('payment_method', 'credit')->sum('total_amount'),
        ];

        // 5. Tax Breakdown (Based on NET Sales)
        $taxRate = 12; // Standard VAT
        $data['vatable_sales'] = $netSales / 1.12; 
        $data['vat_amount'] = $netSales - $data['vatable_sales'];
        $data['vat_exempt'] = 0; 

        // 6. ACCUMULATED GRAND TOTAL
        $totalHistoricalSales = Sale::where('store_id', $storeId)->sum('total_amount');
        $totalHistoricalReturns = SalesReturn::whereHas('sale', fn($q) => $q->where('store_id', $storeId))->sum('refund_amount');
        $grandTotal = $totalHistoricalSales - $totalHistoricalReturns;
        
        $data['new_accumulated_sales'] = $grandTotal;
        $data['old_accumulated_sales'] = $grandTotal - $netSales;

        return view('cashier.reading', compact('data'));
    }
}