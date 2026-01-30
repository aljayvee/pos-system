<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\CreditPayment;
use App\Models\Customer;
use App\Models\CustomerCredit;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log; // [NEW]
use Illuminate\Support\Facades\Hash; // [NEW]
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\POS\CheckoutService; // [NEW]
use App\Services\POS\ReturnService;   // [NEW]

class POSController extends Controller
{
    protected $checkoutService;
    protected $returnService;
    protected $zReadingProcessor; // Kept if used elsewhere

    public function __construct(
        CheckoutService $checkoutService,
        ReturnService $returnService,
        \App\Services\BIR\ZReadingProcessor $zReadingProcessor
    ) {
        $this->checkoutService = $checkoutService;
        $this->returnService = $returnService;
        $this->zReadingProcessor = $zReadingProcessor;
    }

    public function index()
    {
        $storeId = $this->getActiveStoreId();

        $products = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->where('inventories.stock', '>', 0)
            ->whereNull('products.deleted_at')
            ->with(['category', 'pricingTiers'])
            ->select(
                'products.*',
                'inventories.stock as current_stock',
                'inventories.reorder_point'
            )
            ->get();

        $customers = Customer::withSum([
            'credits as balance' => function ($q) use ($storeId) {
                $q->where('is_paid', false)
                    ->whereHas('sale', function ($q2) use ($storeId) {
                        $q2->where('store_id', $storeId);
                    });
            }
        ], 'remaining_balance')->orderBy('name')->get();

        $categories = \App\Models\Category::has('products')->orderBy('name')->get();

        // FETCH SETTINGS
        $loyaltyEnabled = \App\Models\Setting::where('key', 'enable_loyalty')->value('value') ?? '0';

        // --- FIXED: Fetch BIR/Tax Setting ---
        $birEnabled = config('safety_flag_features.bir_tax_compliance') ? 1 : 0;
        $taxType = \App\Models\Setting::where('key', 'tax_type')->value('value') ?? 'inclusive';

        // --- FETCH Register Log Setting ---
        $registerLogsEnabled = \App\Models\Setting::where('key', 'enable_register_logs')->value('value') ?? '0';

        // [FIX] Check if register is open OR if feature is disabled (bypass lock)
        if ($registerLogsEnabled == '1') {
            $isRegisterOpen = \App\Models\CashRegisterSession::where('store_id', $storeId)
                ->where('status', 'open')
                ->exists();
        } else {
            $isRegisterOpen = true; // Always open if feature disabled
        }

        // --- FETCH REAL-TIME STATS (Feature Flagged) ---
        $sessionSales = 0;
        $totalOrders = 0;
        $performance = 'Normal';

        if (config('safety_flag_features.cashier_stats_widgets')) {
            $today = \Carbon\Carbon::today();
            $userId = Auth::id();

            $sessionSales = Sale::where('store_id', $storeId)
                ->where('user_id', $userId)
                ->whereDate('created_at', $today)
                ->sum('total_amount');

            $totalOrders = Sale::where('store_id', $storeId)
                ->where('user_id', $userId)
                ->whereDate('created_at', $today)
                ->count();

            // Simple Performance Logic (Target: 10,000)
            $target = 10000;
            if ($sessionSales >= $target) {
                $performance = 'High';
            } elseif ($sessionSales < $target && $sessionSales > 0) {
                $performance = 'Medium';
            } else {
                $performance = 'Low';
            }
        }

        return view('cashier.index', compact(
            'products',
            'customers',
            'categories',
            'loyaltyEnabled',
            'birEnabled',
            'taxType',
            'isRegisterOpen',
            'registerLogsEnabled',
            'sessionSales',
            'totalOrders',
            'performance'
        ));
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
                ->whereHas('sale', function ($q) use ($storeId) {
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
                if ($remainingPayment <= 0)
                    break;

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
        try {
            $request->validate(['query' => 'required']);
            $q = $request->query('query');

            Log::info('Return Search Query: ' . $q);

            $sale = Sale::with(['saleItems.product', 'customer'])
                ->where('id', $q)
                ->orWhere('reference_number', $q)
                ->first();

            if (!$sale) {
                Log::info('Return Search: Sale not found for ' . $q);
                return response()->json(['success' => false, 'message' => 'Sale not found.']);
            }

            $items = $sale->saleItems->map(function ($item) {
                // ... same logic
                $returned = SalesReturn::where('sale_id', $item->sale_id)
                    ->where('product_id', $item->product_id)
                    ->sum('quantity');

                $available = $item->quantity - $returned;

                // Filter logic will be applied after map
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'sold_qty' => $item->quantity,
                    'price' => $item->price,
                    'returned_qty' => $returned,
                    'available_qty' => $available
                ];
            })->filter(function ($item) {
                return $item['available_qty'] > 0;
            })->values(); // Reset keys

            return response()->json([
                'success' => true,
                'sale' => [
                    'id' => $sale->id,
                    'date' => $sale->created_at->format('M d, Y h:i A'),
                    'customer' => $sale->customer ? $sale->customer->name : 'Walk-in',
                    'total' => $sale->total_amount,
                    'payment_method' => ucfirst($sale->payment_method),
                    'items' => $items
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Return Search Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server Error'], 500);
        }
    }

    public function processReturn(Request $request)
    {
        Log::info('Return Process Request:', $request->all());

        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:good,damaged',
        ]);

        try {
            // [REFACTORED] Use Service
            $this->returnService->processReturn(
                $request->all(),
                $this->getActiveStoreId(),
                Auth::id()
            );

            return response()->json(['success' => true, 'message' => 'Return processed successfully.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStockUpdates()
    {
        $storeId = $this->getActiveStoreId();

        // Fetch only ID and Stock for active products
        $updates = DB::table('products')
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->whereNull('products.deleted_at')
            ->select('products.id', 'inventories.stock')
            ->get();

        return response()->json($updates);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            // 'total_amount' => 'required|numeric', // We no longer trust this for calculations
            'payment_method' => 'required|in:cash,digital,credit',
            'amount_paid' => 'nullable|numeric',
            'reference_number' => 'required_if:payment_method,digital',

            // Validation for new customers
            'credit_details.name' => 'required_if:customer_id,new',
            'credit_details.address' => 'required_if:customer_id,new',
            'credit_details.contact' => 'required_if:customer_id,new',
            'credit_details.due_date' => 'required_if:payment_method,credit|date',
        ], [
            'credit_details.name.required_if' => 'Customer Name is required.',
            'credit_details.address.required_if' => 'Full Address is required for new customers.',
            'credit_details.contact.required_if' => 'Mobile Number is required for new customers.',
            'credit_details.due_date.required_if' => 'Due Date is required for credit transactions.'
        ]);

        try {
            // [REFACTORED] Use Service
            $sale = $this->checkoutService->processCheckout(
                $request->all(),
                $this->getActiveStoreId(),
                Auth::id()
            );

            return response()->json(['success' => true, 'sale_id' => $sale->id]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function showReceipt(Sale $sale)
    {
        $sale->load('saleItems.product', 'user', 'customer');
        $storeId = $sale->store_id;

        // 1. Fetch Basic Settings
        $settings = \App\Models\Setting::where('store_id', $storeId)
            ->whereIn('key', ['store_name', 'store_address', 'store_contact', 'receipt_footer'])
            ->pluck('value', 'key');

        // 2. Decrypt TIN (Your Logic + Store Check)
        $rawTin = \App\Models\Setting::where('store_id', $storeId)->where('key', 'store_tin')->value('value');
        try {
            $tin = $rawTin ? Crypt::decryptString($rawTin) : '';
        } catch (DecryptException $e) {
            $tin = $rawTin; // Fallback to raw text if not encrypted
        }

        // 3. Decrypt Permit
        $rawPermit = \App\Models\Setting::where('store_id', $storeId)->where('key', 'business_permit')->value('value');
        try {
            $permit = $rawPermit ? Crypt::decryptString($rawPermit) : '';
        } catch (DecryptException $e) {
            $permit = $rawPermit; // Fallback
        }

        // --- BIR COMPLIANCE: Receipt Switching ---
        if (config('safety_flag_features.bir_tax_compliance')) {
            return view('cashier.receipt_invoice', compact('sale', 'settings', 'tin', 'permit'));
        } else {
            return view('cashier.receipt_generic', compact('sale', 'settings', 'tin', 'permit'));
        }
    }

    // --- API: Get Latest Debtors ---
    public function getDebtors()
    {
        $storeId = $this->getActiveStoreId();

        $debtors = Customer::withSum([
            'credits as balance' => function ($q) use ($storeId) {
                $q->where('is_paid', false)
                    ->whereHas('sale', function ($q2) use ($storeId) {
                        $q2->where('store_id', $storeId);
                    });
            }
        ], 'remaining_balance')
            ->get()
            ->filter(function ($customer) {
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
        $isBirEnabled = config('safety_flag_features.bir_tax_compliance');

        // 0. [BIR] If Z-Reading, try to fetch exact snapshot from Electronic Journal first
        if (strtoupper($type) === 'Z') {
            $ejEntry = \App\Models\ElectronicJournal::where('store_id', $storeId)
                ->where('type', 'Z-READING')
                ->latest('generated_at')
                ->first();

            if ($ejEntry && !empty($ejEntry->data_snapshot)) {
                $data = $ejEntry->data_snapshot;
                // Ensure type is set correctly in snapshot or override
                $data['type'] = 'Z-READING';

                // Return immediately with snapshot data
                return view('cashier.reading', compact('data'));
            }
        }

        // 1. Fetch Today's Gross Sales
        $todaySales = Sale::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->get();

        // 2. Fetch Today's Returns (Refunds)
        $todayReturns = SalesReturn::whereDate('sales_returns.created_at', $date)
            ->whereHas('sale', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->sum('refund_amount');

        // 3. Fetch Store & BIR Details
        $store = \App\Models\Store::find($storeId);
        $rawTin = \App\Models\Setting::where('key', 'store_tin')->value('value');
        try {
            $tin = $rawTin ? Crypt::decryptString($rawTin) : '000-000-000';
        } catch (\Exception $e) {
            $tin = $rawTin;
        }

        // 4. Calculate Stats
        $grossSales = $todaySales->sum('total_amount');
        $netSales = $grossSales - $todayReturns;

        // Beg/End Numbers (Invoice # if BIR, else ID)
        if ($isBirEnabled && $todaySales->isNotEmpty()) {
            $begNo = $todaySales->first()->invoice_number;
            $endNo = $todaySales->last()->invoice_number;
        } else {
            $begNo = $todaySales->first()->id ?? '-';
            $endNo = $todaySales->last()->id ?? '-';
        }

        $data = [
            'type' => strtoupper($type) . '-READING',
            'date' => now()->format('Y-m-d H:i:s'),
            'store_name' => $store->name ?? \App\Models\Setting::where('key', 'store_name')->value('value'),
            'tin' => $tin,
            'machine_no' => $store->min_number ?? ('POS-' . str_pad($storeId, 3, '0', STR_PAD_LEFT)),
            'serial_no' => $store->serial_number ?? 'N/A',
            'ptu_no' => $store->ptu_number ?? 'N/A',
            'is_bir' => $isBirEnabled,

            'gross_sales' => $grossSales,
            'returns' => $todayReturns,
            'net_sales' => $netSales,
            'trans_count' => $todaySales->count(),
            'beg_or' => $begNo,
            'end_or' => $endNo,

            'cash_sales' => $todaySales->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $todaySales->where('payment_method', 'digital')->sum('total_amount'),
            'credit_sales' => $todaySales->where('payment_method', 'credit')->sum('total_amount'),
        ];

        // 5. Tax Breakdown
        if (\App\Models\Setting::where('key', 'tax_type')->value('value') == 'non_vat') {
            $data['vatable_sales'] = 0;
            $data['vat_amount'] = 0;
            $data['vat_exempt'] = $netSales;
        } else {
            $data['vatable_sales'] = $netSales / 1.12;
            $data['vat_amount'] = $netSales - $data['vatable_sales'];
            $data['vat_exempt'] = 0;
        }

        // 6. ACCUMULATED GRAND TOTAL
        $persistentGrandTotal = $store->accumulated_grand_total ?? 0;
        $data['old_accumulated_sales'] = $persistentGrandTotal;
        $data['new_accumulated_sales'] = $persistentGrandTotal + $netSales;

        return view('cashier.reading', compact('data'));
    }

    // Add this method at the very bottom of your POSController class
    public function verifyAdmin(Request $request)
    {
        $request->validate(['password' => 'required']);

        // Get all admins
        $admins = \App\Models\User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            // Check if the password matches ANY admin account
            if (Hash::check($request->password, $admin->password)) {
                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid Admin Password'], 403);
    }
}