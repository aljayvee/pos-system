<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // 1. List All Transactions
    public function index(Request $request)
    {
        // Get Active Store
        $multiStoreEnabled = \App\Models\Setting::where('key', 'enable_multi_store')->value('value') ?? '0';
        $storeId = ($multiStoreEnabled == '1') ? session('active_store_id', auth()->user()->store_id ?? 1) : 1;

        // Filter Query
        $query = Sale::with(['user', 'customer', 'salesReturns'])
            ->where('store_id', $storeId);

        if ($request->has('archived')) {
            $query->onlyTrashed();
        }

        $query->latest();

        // Search by ID or Reference
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('id', $search)
                ->orWhere('reference_number', 'like', "%$search%");
        }

        $transactions = $query->paginate(15);

        return view('admin.transactions.index', compact('transactions'));
    }

    // 2. Show Transaction Details
    public function show($id)
    {
        $sale = Sale::with(['saleItems.product', 'user', 'customer'])->findOrFail($id);
        return view('admin.transactions.show', compact('sale'));
    }

    // 3. Void / Refund Transaction
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $sale = Sale::with('saleItems')->findOrFail($id);

            // A. Restore Inventory
            // A. Restore Inventory (FIXED: Target Inventory table, not Product)
            foreach ($sale->saleItems as $item) {
                if ($item->product_id) {
                    $inventory = \App\Models\Inventory::where('product_id', $item->product_id)
                        ->where('store_id', $sale->store_id)
                        ->first();

                    if ($inventory) {
                        $inventory->increment('stock', $item->quantity);
                    }
                }
            }

            // B. Revert Loyalty Points (If used/earned)
            if ($sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    // 1. Remove points earned from this purchase
                    $loyaltyRatio = \App\Models\Setting::where('key', 'loyalty_ratio')->value('value') ?? 100;
                    $pointsEarned = floor($sale->total_amount / $loyaltyRatio);
                    if ($pointsEarned > 0) {
                        $customer->decrement('points', $pointsEarned);
                    }

                    // 2. Return points used (Redemption refund)
                    if ($sale->points_used > 0) {
                        $customer->increment('points', $sale->points_used);
                    }
                }
            }

            // C. Cancel Credit Record (If credit sale)
            if ($sale->payment_method === 'credit') {
                CustomerCredit::where('sale_id', $sale->id)->delete();
            }

            // D. Delete Items & Sale Record
            $sale->saleItems()->delete();
            $sale->delete();
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction #' . $id . ' has been archived (Voided) and inventory restored.');
    }

    // NEW: Print Receipt from Admin
    // NEW: Print Receipt from Admin
    public function printReceipt(\App\Models\Sale $sale)
    {
        $sale->load('saleItems.product', 'user', 'customer');
        $storeId = $sale->store_id;

        // 1. Fetch Basic Settings
        $settings = \App\Models\Setting::where('store_id', $storeId)
            ->whereIn('key', ['store_name', 'store_address', 'store_contact', 'receipt_footer'])
            ->pluck('value', 'key');

        // 2. Decrypt TIN
        $rawTin = \App\Models\Setting::where('store_id', $storeId)->where('key', 'store_tin')->value('value');
        try {
            $tin = $rawTin ? \Illuminate\Support\Facades\Crypt::decryptString($rawTin) : '';
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            $tin = $rawTin;
        }

        // 3. Decrypt Permit
        $rawPermit = \App\Models\Setting::where('store_id', $storeId)->where('key', 'business_permit')->value('value');
        try {
            $permit = $rawPermit ? \Illuminate\Support\Facades\Crypt::decryptString($rawPermit) : '';
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            $permit = $rawPermit;
        }

        // 4. Use COMPLIANT or GENERIC view based on flag
        if (config('safety_flag_features.bir_tax_compliance')) {
            return view('cashier.receipt_invoice', compact('sale', 'settings', 'tin', 'permit'));
        } else {
            return view('cashier.receipt_generic', compact('sale', 'settings', 'tin', 'permit'));
        }
    }
}