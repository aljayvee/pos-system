<?php

namespace App\Services\POS;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\Inventory;
use App\Models\CustomerCredit;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnService
{
    /**
     * Process sales return.
     * 
     * @param array $data
     * @param int $storeId
     * @param int $userId
     * @return void
     * @throws \Exception
     */
    public function processReturn(array $data, int $storeId, int $userId)
    {
        $saleId = $data['sale_id'];

        return DB::transaction(function () use ($data, $saleId, $storeId, $userId) {
            // 1. LOCK THE PARENT SALE RECORD
            $sale = Sale::where('id', $saleId)->lockForUpdate()->firstOrFail();

            $totalRefund = 0;

            foreach ($data['items'] as $itemData) {
                $pid = $itemData['product_id'];
                $qty = $itemData['quantity'];

                // 2. FETCH SALE ITEM
                $saleItem = SaleItem::where('sale_id', $saleId)
                    ->where('product_id', $pid)
                    ->firstOrFail();

                // 3. CALCULATE ALREADY RETURNED
                $alreadyReturned = SalesReturn::where('sale_id', $saleId)
                    ->where('product_id', $pid)
                    ->sum('quantity');

                if (($qty + $alreadyReturned) > $saleItem->quantity) {
                    throw new \Exception("Cannot return {$qty} items. Only " . ($saleItem->quantity - $alreadyReturned) . " left eligible for return.");
                }

                $refundAmount = $saleItem->price * $qty;
                $totalRefund += $refundAmount;

                // 4. CREATE RETURN RECORD
                SalesReturn::create([
                    'sale_id' => $saleId,
                    'product_id' => $pid,
                    'user_id' => $userId,
                    'quantity' => $qty,
                    'refund_amount' => $refundAmount,
                    'condition' => $itemData['condition'],
                    'reason' => $itemData['reason'] ?? 'Customer Return (POS)'
                ]);

                // 5. RESTORE STOCK (If Good Condition)
                if ($itemData['condition'] === 'good') {
                    $inventory = Inventory::where('product_id', $pid)
                        ->where('store_id', $storeId)
                        ->lockForUpdate()
                        ->first();

                    if ($inventory) {
                        $inventory->increment('stock', $qty);
                    }
                }

                // Handle Credit Reduction
                if ($sale->payment_method === 'credit' && $sale->customer_id) {
                    $credit = CustomerCredit::where('sale_id', $saleId)
                        ->lockForUpdate()
                        ->first();

                    if ($credit) {
                        $credit->remaining_balance -= $refundAmount;
                        if ($credit->remaining_balance <= 0.01) {
                            $credit->remaining_balance = 0;
                            $credit->is_paid = true;
                        }
                        $credit->save();
                    }
                }
            }

            // LOGGING
            ActivityLog::create([
                'user_id' => $userId,
                'store_id' => $storeId,
                'action' => 'Sale Return',
                'description' => "Return processed for Sale #{$saleId} | Total Refund: " . number_format($totalRefund, 2)
            ]);

            return ['success' => true, 'message' => 'Return processed successfully.'];
        });
    }
}
