<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
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
        $returnsCollection = SalesReturn::whereHas('sale', function ($q) use ($storeId) {
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

        // 5. Realized Sales (Cash Basis for Tithes/Cash Flow)
        // Cash Sales + Credit Collections + Digital Sales
        // Exclude Credit Sales (Unpaid)
        $cashAndDigitalSales = Sale::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->whereIn('payment_method', ['cash', 'digital'])
            ->sum('total_amount');

        $collections = CreditPayment::whereDate('payment_date', $date)
            ->whereHas('credit.sale', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->sum('amount');

        $realizedSales = $cashAndDigitalSales + $collections - $totalRefunds;

        return [
            'gross_sales' => $grossSales,
            'net_sales' => $netSales,
            'realized_sales' => $realizedSales, // [NEW] Cash Basis
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
        // 1. Profit from Cash/Digital Sales (Realized Immediately)
        // OPTIMIZED: Use SQL Aggregation instead of loading all models
        $cashSalesCost = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.store_id', $storeId)
            ->whereDate('sales.created_at', $date)
            ->whereIn('sales.payment_method', ['cash', 'digital'])
            ->sum(DB::raw('sale_items.quantity * COALESCE(NULLIF(sale_items.cost, 0), products.cost, 0)'));

        // 2. Profit from Debt Collections (Realized Now)
        // Logic: Collection Amount - Proportional Cost of that amount
        $collections = CreditPayment::whereDate('payment_date', $date)
            ->whereHas('credit.sale', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->with(['credit']) // Needed for sale_id
            ->get();

        $collectionProfit = 0;
        $totalCollectionCost = 0;

        if ($collections->isNotEmpty()) {
            // OPTIMIZED: Batch fetch sale costs to avoid N+1 problem
            $saleIds = $collections->pluck('credit.sale_id')->unique()->filter();

            if ($saleIds->isNotEmpty()) {
                $salesData = DB::table('sales')
                    ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
                    ->join('products', 'products.id', '=', 'sale_items.product_id')
                    ->whereIn('sales.id', $saleIds)
                    ->select(
                        'sales.id',
                        'sales.total_amount',
                        DB::raw('SUM(sale_items.quantity * COALESCE(NULLIF(sale_items.cost, 0), products.cost, 0)) as total_cost')
                    )
                    ->groupBy('sales.id', 'sales.total_amount')
                    ->get()
                    ->keyBy('id'); // Key by Sale ID for O(1) lookup

                foreach ($collections as $payment) {
                    $saleId = $payment->credit->sale_id;
                    $saleInfo = $salesData[$saleId] ?? null;

                    if (!$saleInfo)
                        continue;

                    $saleTotal = $saleInfo->total_amount > 0 ? $saleInfo->total_amount : 1;
                    $paymentRatio = $payment->amount / $saleTotal;

                    // Recovered Cost for this payment
                    $recoveredCost = $saleInfo->total_cost * $paymentRatio;

                    $collectionProfit += ($payment->amount - $recoveredCost);
                    $totalCollectionCost += $recoveredCost;
                }
            }
        }

        // 3. Cost of Returns (Deduct from Cost / Add to Profit)
        // Only deduct cost for Returns that were originally Cash/Digital (since we excluded Credit Costs)
        // If we refund a Credit sale, we haven't recognized the cost yet, so we shouldn't "recover" it?
        // Actually, if we refund a Credit Sale, the Credit Balance decreases.
        // Let's stick to Cash Refunds for Cash Basis Profit.

        $cashReturnCost = 0;
        foreach ($returnsCollection as $ret) {
            if ($ret->sale && $ret->sale->payment_method === 'credit') {
                continue; // Ignore credit returns for Realized Profit (Cost wasn't recognized yet)
            }

            // Find cost
            $originalItem = null;
            if ($ret->sale && $ret->sale->saleItems) {
                $originalItem = $ret->sale->saleItems->where('product_id', $ret->product_id)->first();
            }
            $returnUnitCost = ($originalItem && $originalItem->cost > 0) ? $originalItem->cost : ($ret->product->cost ?? 0);
            $cashReturnCost += ($returnUnitCost * $ret->quantity);
        }

        // REALIZED Gross Cost = (Cost of Cash Sales + Cost of Collections) - Cost of Cash Returns
        $realizedGrossCost = ($cashSalesCost + $totalCollectionCost) - $cashReturnCost;

        // REALIZED Profit = (Cash Sales + Collections) - Returns - Realized Cost
        // But simpler: Sum margin of Cash Sales + Sum margin of Collections - Sum margin lost on Returns
        // Let's use the explicit Revenue - Cost approach.

        // We need Realized Revenue here to be precise, but we can reconstruct it or trust the logic
        // CashSalesRev + CollectionRev - CashRefundRev
        // Using the passed $netSales is risky because it includes Credit Sales!

        // Let's calculate Profit directly from the components we iterated:

        // A. Cash Sales Profit
        $cashSalesRev = Sale::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->whereIn('payment_method', ['cash', 'digital'])
            ->sum('total_amount');

        // B. Collections Profit (calculated above as $collectionProfit)
        //    $collectionProfit includes (Amount - Cost)

        // C. Cash Returns Impact (Negative Profit)
        $cashRefunds = $returnsCollection->filter(fn($r) => $r->sale && $r->sale->payment_method !== 'credit')->sum('refund_amount');
        // Profit lost = RefundAmount - RecoveredCost
        $profitLostOnReturns = $cashRefunds - $cashReturnCost;

        $realizedProfit = ($cashSalesRev - $cashSalesCost) + $collectionProfit - $profitLostOnReturns;

        return [
            'gross_cost' => $realizedGrossCost, // For reference
            'cost_of_returns' => $cashReturnCost,
            'net_cost' => $realizedGrossCost,
            'profit' => $realizedProfit
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
            ->whereHas('credit.sale', function ($q) use ($storeId) {
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
        $cashRefunds = SalesReturn::whereHas('sale', function ($q) use ($storeId) {
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
