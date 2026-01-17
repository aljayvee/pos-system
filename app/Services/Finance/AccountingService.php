<?php

namespace App\Services\Finance;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product; // <--- IMPORTS
use App\Models\Purchase; // <--- IMPORTS
use App\Models\SalesReturn;
use App\Models\CreditPayment;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountingService
{
    public function getFinancialSummary(int $storeId, string $type, string $startDate, ?string $endDate = null, ?string $targetStore = null)
    {
        // 1. Resolve Target Store for Filtering (Admin can filter specific store or All)
        $targetStore = $targetStore ?? $storeId;

        // 2. Build Base Query
        $query = Sale::with('user', 'customer')->latest();
        $returnQuery = SalesReturn::with('sale');

        // Apply Store Filter
        if ($targetStore !== 'all') {
            $query->where('store_id', $targetStore);
            $returnQuery->whereHas('sale', function ($q) use ($targetStore) {
                $q->where('store_id', $targetStore);
            });
        }

        // Apply Date Filter
        if ($type === 'daily') {
            $query->whereDate('created_at', $startDate);
            $returnQuery->whereDate('created_at', $startDate);
        } elseif ($type === 'weekly') {
            $start = Carbon::parse($startDate)->startOfWeek();
            $end = Carbon::parse($startDate)->endOfWeek();
            $query->whereBetween('created_at', [$start, $end]);
            $returnQuery->whereBetween('created_at', [$start, $end]);
        } elseif ($type === 'monthly') {
            $query->whereMonth('created_at', Carbon::parse($startDate)->month)
                ->whereYear('created_at', Carbon::parse($startDate)->year);
            $returnQuery->whereMonth('created_at', Carbon::parse($startDate)->month)
                ->whereYear('created_at', Carbon::parse($startDate)->year);
        }

        $sales = $query->get();
        $salesIds = $sales->pluck('id');

        // Fetch Returns
        $returns = $returnQuery->with(['sale.saleItems', 'product'])->get();

        // 3. Basic Sales Metrics
        $grossSales = $sales->sum('total_amount');
        $totalReturns = $returns->sum('refund_amount');
        $netSales = $grossSales - $totalReturns;

        $totalTransactions = $sales->count();
        $cashSales = $sales->where('payment_method', 'cash')->sum('total_amount');
        $creditSales = $sales->where('payment_method', 'credit')->sum('total_amount');
        $digitalSales = $sales->where('payment_method', 'digital')->sum('total_amount');

        // 4. Realized Revenue (Cash Basis) for Tithes
        $realizedData = $this->calculateRealizedRevenue($targetStore, $type, $startDate, $cashSales, $digitalSales, $returns);
        $realizedRevenue = $realizedData['revenue'];
        $collectedAmount = $realizedData['collected'];

        // 5. Tithes
        $tithesEnabled = Setting::where('key', 'enable_tithes')->value('value') ?? '1';
        $tithesAmount = ($tithesEnabled == '1') ? max(0, $realizedRevenue * 0.10) : 0;

        // 6. Gross Profit Logic (Complex)
        $grossProfit = $this->calculateGrossProfit($sales, $salesIds, $returns, $targetStore, $type, $startDate);

        // 7. Analytics (Moved here to return everything in one go, or keep separate?)
        // The Controller called them separately. Let's return them here if we want "Get All Report Data"
        // But for now, let's just stick to the financial summary.

        $analytics = $this->getAnalytics($salesIds);

        return array_merge([
            'sales' => $sales,
            'total_sales' => $netSales,
            'gross_sales' => $grossSales,
            'total_returns' => $totalReturns,
            'total_transactions' => $totalTransactions,
            'cash_sales' => $cashSales,
            'credit_sales' => $creditSales,
            'digital_sales' => $digitalSales,
            'realized_revenue' => $realizedRevenue,
            'collected_amount' => $collectedAmount, // <--- EXPOSED
            'tithes_amount' => $tithesAmount,
            'tithes_enabled' => $tithesEnabled,
            'gross_profit' => $grossProfit,
        ], $analytics);
    }

    private function calculateRealizedRevenue($targetStore, $type, $startDate, $cashSales, $digitalSales, $returns)
    {
        // 1. Credit Collections
        $collectionQuery = CreditPayment::query();
        if ($targetStore !== 'all') {
            $collectionQuery->whereHas('credit.sale', function ($q) use ($targetStore) {
                $q->where('store_id', $targetStore);
            });
        }

        // Match Date Filter
        if ($type === 'daily') {
            $collectionQuery->whereDate('payment_date', $startDate);
        } elseif ($type === 'weekly') {
            $start = Carbon::parse($startDate)->startOfWeek();
            $end = Carbon::parse($startDate)->endOfWeek();
            $collectionQuery->whereBetween('payment_date', [$start, $end]);
        } elseif ($type === 'monthly') {
            $collectionQuery->whereMonth('payment_date', Carbon::parse($startDate)->month)
                ->whereYear('payment_date', Carbon::parse($startDate)->year);
        }
        $collected = $collectionQuery->sum('amount');

        // 2. Cash Refunds (Only deduct refunds if original sale was NOT credit - strict cash basis)
        // Actually, we deduct refunds that were PAID OUT in cash.
        // Assuming 'returns' collection implies cash output for refund unless specified otherwise.
        $cashRefunds = $returns->filter(fn($r) => $r->sale && $r->sale->payment_method !== 'credit')->sum('refund_amount');

        return [
            'revenue' => ($cashSales + $digitalSales + $collected) - $cashRefunds,
            'collected' => $collected
        ];
    }

    private function calculateGrossProfit($sales, $salesIds, $returns, $targetStore, $type, $startDate)
    {
        // A. Profit from Cash/Digital Sales (Immediate Realization)
        $soldItems = SaleItem::with(['sale', 'product'])->whereIn('sale_id', $salesIds)->get();

        $cashSoldItems = $soldItems->filter(function ($item) {
            return $item->sale && in_array($item->sale->payment_method, ['cash', 'digital']);
        });

        $cashSalesCost = 0;
        foreach ($cashSoldItems as $item) {
            $itemCost = ($item->cost > 0) ? $item->cost : ($item->product->cost ?? 0);
            $cashSalesCost += ($itemCost * $item->quantity);
        }
        $cashSalesRev = $sales->whereIn('payment_method', ['cash', 'digital'])->sum('total_amount');
        $cashProfit = $cashSalesRev - $cashSalesCost;

        // B. Profit from Collections (Realized when paid)
        $collectionQuery = CreditPayment::query()->with(['credit.sale.saleItems']);

        if ($targetStore !== 'all') {
            $collectionQuery->whereHas('credit.sale', fn($q) => $q->where('store_id', $targetStore));
        }

        if ($type === 'daily') {
            $collectionQuery->whereDate('payment_date', $startDate);
        } elseif ($type === 'weekly') {
            $start = Carbon::parse($startDate)->startOfWeek();
            $end = Carbon::parse($startDate)->endOfWeek();
            $collectionQuery->whereBetween('payment_date', [$start, $end]);
        } elseif ($type === 'monthly') {
            $collectionQuery->whereMonth('payment_date', Carbon::parse($startDate)->month)
                ->whereYear('payment_date', Carbon::parse($startDate)->year);
        }

        $collections = $collectionQuery->get();
        $collectionProfit = 0;

        foreach ($collections as $payment) {
            $sale = $payment->credit->sale ?? null;
            if (!$sale)
                continue;

            $saleTotal = $sale->total_amount > 0 ? $sale->total_amount : 1;
            $paymentRatio = $payment->amount / $saleTotal;

            // Calculate Original Cost of that Sale
            $saleTotalCost = 0;
            if ($sale->saleItems) {
                foreach ($sale->saleItems as $sItem) {
                    $c = $sItem->cost > 0 ? $sItem->cost : ($sItem->product->cost ?? 0);
                    $saleTotalCost += ($c * $sItem->quantity);
                }
            }

            $recoveredCost = $saleTotalCost * $paymentRatio;
            $collectionProfit += ($payment->amount - $recoveredCost);
        }

        // C. Profit Lost on Returns (Deduct Profit that was previously recognized)
        // We only reverse profit for Cash/Digital returns here. 
        // Credit returns usually just cancel debt, not realized profit, unless we track "Realized Profit Reversal".
        // For simplicity, we use the same logic as the controller:
        $cashReturns = $returns->filter(fn($r) => $r->sale && $r->sale->payment_method !== 'credit');
        $cashRefundAmount = $cashReturns->sum('refund_amount');

        $cashReturnCost = 0;
        foreach ($cashReturns as $ret) {
            // Find original cost from sale item if possible, else current product cost
            $originalItem = null;
            if ($ret->sale && $ret->sale->saleItems) {
                $originalItem = $ret->sale->saleItems->where('product_id', $ret->product_id)->first();
            }
            $uCost = ($originalItem && $originalItem->cost > 0) ? $originalItem->cost : ($ret->product->cost ?? 0);
            $cashReturnCost += ($uCost * $ret->quantity);
        }
        $profitLost = $cashRefundAmount - $cashReturnCost;

        return $cashProfit + $collectionProfit - $profitLost;
    }

    public function getAnalytics($salesIds)
    {
        // Top Items, Customers, Slow Moving, Categories
        // This could be its own method or service, but fits in Accounting/Reporting

        // ... (Logic from Controller Lines 200-282 approx) ...
        // For brevity in this prompt, I will assume the Controller still handles this part OR I extract it now.
        // Let's extract it to keep Controller clean.

        return [
            'topItems' => $this->getTopItems($salesIds),
            'topCustomers' => $this->getTopCustomers($salesIds),
            'salesByCategory' => $this->getSalesByCategory($salesIds),
            'slowMovingItems' => $this->getSlowMovingItems($salesIds)
        ];
    }

    private function getTopItems($salesIds)
    {
        return SaleItem::select('product_id', DB::raw('SUM(quantity) as gross_qty'), DB::raw('SUM(price * quantity) as gross_rev'))
            ->whereIn('sale_id', $salesIds)
            ->groupBy('product_id')
            ->with('product')
            ->get()
            ->map(function ($item) use ($salesIds) {
                $returned = SalesReturn::whereIn('sale_id', $salesIds)
                    ->where('product_id', $item->product_id)
                    ->selectRaw('SUM(quantity) as qty, SUM(refund_amount) as amt')
                    ->first();
                return (object) [
                    'product_id' => $item->product_id,
                    'product' => $item->product,
                    'total_qty' => max(0, $item->gross_qty - ($returned->qty ?? 0)),
                    'total_revenue' => max(0, $item->gross_rev - ($returned->amt ?? 0))
                ];
            })
            ->filter(fn($item) => $item->total_qty > 0)
            ->sortByDesc('total_qty')
            ->take(10);
    }

    private function getTopCustomers($salesIds)
    {
        return Sale::select('customer_id', DB::raw('SUM(total_amount) as gross_spent'), DB::raw('count(*) as trans_count'))
            ->whereNotNull('customer_id')
            ->whereIn('id', $salesIds)
            ->groupBy('customer_id')
            ->with('customer')
            ->get()
            ->map(function ($c) use ($salesIds) {
                $returns = SalesReturn::whereHas('sale', fn($q) => $q->whereIn('id', $salesIds)->where('customer_id', $c->customer_id))->sum('refund_amount');
                return (object) [
                    'customer_id' => $c->customer_id,
                    'customer' => $c->customer,
                    'trans_count' => $c->trans_count,
                    'total_spent' => max(0, $c->gross_spent - $returns)
                ];
            })
            ->sortByDesc('total_spent')
            ->take(5);
    }

    private function getSalesByCategory($salesIds)
    {
        $raw = SaleItem::select('categories.name', DB::raw('SUM(sale_items.price * sale_items.quantity) as gross_revenue'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('sale_items.sale_id', $salesIds)
            ->groupBy('categories.name')
            ->get();

        return $raw->map(function ($cat) use ($salesIds) {
            $returns = SalesReturn::join('products', 'sales_returns.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->whereIn('sales_returns.sale_id', $salesIds)
                ->where('categories.name', $cat->name)
                ->sum('sales_returns.refund_amount');
            return (object) [
                'name' => $cat->name,
                'total_revenue' => max(0, $cat->gross_revenue - $returns)
            ];
        })->sortByDesc('total_revenue');
    }

    private function getSlowMovingItems($salesIds)
    {
        $soldProductIds = SaleItem::whereIn('sale_id', $salesIds)->pluck('product_id')->unique();
        return Product::whereNotIn('id', $soldProductIds)
            ->where('stock', '>', 0)
            ->take(10)
            ->get();
    }

    public function getVatSummary(int $storeId, Carbon $start, Carbon $end)
    {
        $salesData = Sale::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('SUM(total_amount) as gross_sales, SUM(vatable_sales) as vatable_sales, SUM(output_vat) as output_vat')
            ->first();

        $purchaseData = \App\Models\Purchase::where('store_id', $storeId)
            ->whereBetween('purchase_date', [$start, $end])
            ->selectRaw('SUM(total_cost) as total_purchases, SUM(input_vat) as input_vat')
            ->first();

        $outputVat = $salesData->output_vat ?? 0;
        $inputVat = $purchaseData->input_vat ?? 0;

        return [
            'salesData' => $salesData,
            'purchaseData' => $purchaseData,
            'netPayable' => $outputVat - $inputVat
        ];
    }
}
