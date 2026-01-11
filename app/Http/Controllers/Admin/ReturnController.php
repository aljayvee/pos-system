<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\Product;
use App\Models\CustomerCredit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    // Show return form for a specific sale
    public function create($saleId)
    {
        $sale = Sale::with(['saleItems.product', 'customer'])->findOrFail($saleId);
        return view('admin.transactions.return', compact('sale'));
    }

    // REPLACE the existing 'store' method with this:

    public function store(Request $request, $saleId)
    {
        // ADVANCED PERMISSION CHECK: refund.approve
        if (!auth()->user()->hasPermission(\App\Enums\Permission::REFUND_APPROVE)) {
            abort(403, 'You do not have permission to approve refunds.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:good,damaged',
            'items.*.reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction(); // Start Transaction

        try {
            // Lock the Sale record first to ensure it exists and isn't being modified
            $sale = Sale::with(['saleItems.product', 'customer'])
                ->where('id', $saleId)
                ->lockForUpdate()
                ->firstOrFail();

            foreach ($request->items as $itemData) {
                $productId = $itemData['product_id'];
                $returnQty = $itemData['quantity'];

                // 1. Verify Item Quantity (Consistency)
                $saleItem = SaleItem::where('sale_id', $sale->id)
                    ->where('product_id', $productId)
                    ->firstOrFail();

                // Check previously returned quantity
                // We lock this query too to prevent double returns happening simultaneously
                $alreadyReturned = SalesReturn::where('sale_id', $sale->id)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->sum('quantity');

                if (($returnQty + $alreadyReturned) > $saleItem->quantity) {
                    throw new \Exception("Cannot return more items than purchased for Product ID: $productId");
                }

                // 2. Calculate Refund
                $refundAmount = $saleItem->price * $returnQty;

                // 3. Create Return Record
                SalesReturn::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'user_id' => Auth::id(),
                    'quantity' => $returnQty,
                    'refund_amount' => $refundAmount,
                    'reason' => $itemData['reason'] ?? null,
                    'condition' => $itemData['condition']
                ]);

                // 4. Restore Stock (Only if condition is GOOD)
                if ($itemData['condition'] === 'good') {
                    // Fix: Update INVENTORY (Store-specific), not Product (Global)
                    \App\Models\Inventory::where('product_id', $productId)
                        ->where('store_id', $sale->store_id)
                        ->increment('stock', $returnQty);
                }

                // 5. Adjust Financials (The Critical Fix)
                if ($sale->payment_method === 'credit' && $sale->customer_id) {

                    // LOCK the credit record before modifying it
                    $credit = CustomerCredit::where('sale_id', $sale->id)
                        ->lockForUpdate()
                        ->first();

                    if ($credit) {
                        // Safe to modify now
                        $credit->remaining_balance -= $refundAmount;

                        if ($credit->remaining_balance <= 0) {
                            $credit->remaining_balance = 0;
                            $credit->is_paid = true;
                        } else {
                            // If balance reappears (e.g., negative refund?), mark unpaid
                            $credit->is_paid = false;
                        }

                        $credit->save();
                    }
                }
            }

            DB::commit(); // Commit all changes
            return redirect()->route('transactions.show', $sale->id)
                ->with('success', 'Return processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing return: ' . $e->getMessage());
        }
    }
}