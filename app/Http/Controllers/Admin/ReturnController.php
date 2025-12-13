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

    // Process the return
    public function store(Request $request, $saleId)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:good,damaged',
            'items.*.reason' => 'nullable|string|max:255',
        ]);

        $sale = Sale::findOrFail($saleId);

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                $productId = $itemData['product_id'];
                $returnQty = $itemData['quantity'];
                
                // 1. Verify user isn't returning more than they bought
                $saleItem = SaleItem::where('sale_id', $sale->id)
                            ->where('product_id', $productId)
                            ->firstOrFail();

                // Check previously returned quantity to prevent double returns
                $alreadyReturned = SalesReturn::where('sale_id', $sale->id)
                                    ->where('product_id', $productId)
                                    ->sum('quantity');

                if (($returnQty + $alreadyReturned) > $saleItem->quantity) {
                    throw new \Exception("Cannot return more items than purchased.");
                }

                // 2. Calculate Refund Amount
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
                    Product::where('id', $productId)->increment('stock', $returnQty);
                }
                // If 'damaged', stock is not restored (it's essentially wastage)

                // 5. Adjust Financials
                // If Sale was Credit (Utang), reduce the debt instead of giving cash
                if ($sale->payment_method === 'credit' && $sale->customer_id) {
                    $credit = CustomerCredit::where('sale_id', $sale->id)->first();
                    if ($credit) {
                        $credit->remaining_balance -= $refundAmount;
                        if ($credit->remaining_balance < 0) $credit->remaining_balance = 0;
                        
                        // If balance is now 0, mark as paid
                        if ($credit->remaining_balance == 0) $credit->is_paid = true;
                        
                        $credit->save();
                    }
                }
                
                // If Sale was Cash, this represents a Cash Out from the drawer (Handled via physical cash return)
            }

            DB::commit();
            return redirect()->route('admin.transactions.show', $sale->id)
                             ->with('success', 'Return processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing return: ' . $e->getMessage());
        }
    }
}