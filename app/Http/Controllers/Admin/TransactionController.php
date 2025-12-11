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
        $query = Sale::with(['user', 'customer'])->latest();

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
            foreach ($sale->saleItems as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
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

        return redirect()->route('transactions.index')->with('success', 'Transaction #' . $id . ' has been voided and inventory restored.');
    }
}