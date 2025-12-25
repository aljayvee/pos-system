<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\CustomerCredit;
use App\Models\CreditPayment;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get Daily Sales Metrics (Net Sales, Returns, etc.)
     *
     * @param int $storeId
     * @param string $date
     * @return array
     */
    public function getDailySalesMetrics(int $storeId, string $date): array
    {
        // 1. Gross Sales (All Payment Methods)
        $grossSales = Sale::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->sum('total_amount');

        // 2. Total Refunds (All Return Types - Cash, Credit Reversals, etc.)
        $returnsCollection = SalesReturn::whereHas('sale', function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->whereDate('created_at', $date)
            ->get();
            
        $totalRefunds = $returnsCollection->sum('refund_amount');

        // 3. Net Sales
        $netSales = $grossSales - $totalRefunds;

        // 4. Transaction Count
        $transactionCount = Sale::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->count();

        return [
            'gross_sales' => $grossSales,
            'net_sales' => $netSales,
            'total_refunds' => $totalRefunds,
            'transaction_count' => $transactionCount,
            'returns_collection' => $returnsCollection // Return collection for profit calc optimization
        ];
    }

    /**
     * Get Daily Profit Metrics (Profit, Net Cost)
     *
     * @param int $storeId
     * @param string $date
     * @param float $netSales
     * @param \Illuminate\Database\Eloquent\Collection $returnsCollection
     * @return array
     */
    public function getDailyProfitMetrics(int $storeId, string $date, float $netSales, $returnsCollection): array
    {
        // 1. Total Cost of Goods Sold (Initial)
        // Optimization: Eager load relations
        $soldItems = SaleItem::whereHas('sale', function($q) use ($storeId, $date) {
                $q->where('store_id', $storeId)->whereDate('created_at', $date);
            })
            ->with(['product'])
            ->get();

        $totalCost = 0;
        foreach($soldItems as $item) {
            $unitCost = $item->cost > 0 ? $item->cost : ($item->product->cost ?? 0);
            $totalCost += ($unitCost * $item->quantity);
        }

        // 2. Cost of Returns (Recovered Inventory Value)
        $costOfReturns = 0;
        // We iterate the passed collection to avoid re-querying
        // But we need to make sure we load necessary relations if not already loaded
        // Ideally the controller/caller ensures relations, but we can check.
        // For simplicity in Service, we can assume standard loading or lazy load.

        foreach ($returnsCollection as $ret) {
             // Find original cost from the specific sale item
             // Note: This relies on the returns being loaded with 'sale.saleItems' and 'product'
             // If not loaded, this will trigger N+1, but acceptable for daily dashboard volume.
             
             $originalItem = null;
             if ($ret->sale && $ret->sale->saleItems) {
                 $originalItem = $ret->sale->saleItems->where('product_id', $ret->product_id)->first();
             }
             
             $returnUnitCost = ($originalItem && $originalItem->cost > 0) ? $originalItem->cost : ($ret->product->cost ?? 0);
             $costOfReturns += ($returnUnitCost * $ret->quantity);
        }

        $netCost = $totalCost - $costOfReturns;
        $profit = $netSales - $netCost;

        return [
            'gross_cost' => $totalCost,
            'cost_of_returns' => $costOfReturns,
            'net_cost' => $netCost,
            'profit' => $profit
        ];
    }

    /**
     * Get Debt Collections for the day (Actual Cash Inflow from existing Debts)
     *
     * @param int $storeId
     * @param string $date
     * @return float
     */
    public function getDebtCollections(int $storeId, string $date): float
    {
        // CreditPayments link to CustomerCredit -> Sale -> Store
        return CreditPayment::whereDate('payment_date', $date)
            ->whereHas('credit.sale', function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->sum('amount');
    }

    /**
     * Get Daily Cash Flow (Operating Cash in Drawer)
     * Formula: Cash Sales + Debt Collections - Cash Refunds
     *
     * @param int $storeId
     * @param string $date
     * @return array
     */
    public function getDailyCashFlow(int $storeId, string $date): array
    {
        // 1. Cash Sales (Strictly Cash payment method)
        $cashSales = Sale::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        // 2. Debt Collections (Assumed Cash/Liquid)
        $collections = $this->getDebtCollections($storeId, $date);

        // 3. Cash Refunds (Returns where original sale was CASH)
        $cashRefunds = SalesReturn::whereHas('sale', function($q) use ($storeId) {
                $q->where('store_id', $storeId)
                  ->where('payment_method', 'cash'); // Only subtract refunds if we actually gave back cash
            })
            ->whereDate('created_at', $date)
            ->sum('refund_amount');

        $netCashFlow = ($cashSales + $collections) - $cashRefunds;

        return [
            'cash_sales' => $cashSales,
            'collections' => $collections,
            'cash_refunds' => $cashRefunds,
            'net_cash_flow' => $netCashFlow
        ];
    }
}
