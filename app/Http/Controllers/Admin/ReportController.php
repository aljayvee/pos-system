<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\CustomerCredit;
use App\Models\SalesReturn; // Import SalesReturn
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // 1. Sales Report (Main Dashboard)
    public function index(Request $request)
    {
        // 1. Filter Parameters
        $type = $request->input('type', 'daily');
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        $date = $startDate;

        // 2. Multi-Store Context Logic
        $isMultiStore = \App\Models\Setting::where('key', 'enable_multi_store')->value('value') ?? '0';
        $activeStoreId = $this->getActiveStoreId();
        $targetStore = $request->input('store_filter', $activeStoreId);

        // 3. Build Query
        $query = Sale::with('user', 'customer')->latest();
        $returnQuery = SalesReturn::with('sale'); // For calculating returns

        // Apply Store Filter
        if ($targetStore !== 'all') {
            $query->where('store_id', $targetStore);
            $returnQuery->whereHas('sale', function($q) use ($targetStore) {
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

        // Fetch sold items for cost calculation
        $soldItems = SaleItem::with(['sale', 'product'])
            ->whereIn('sale_id', $salesIds)
            ->get();

        // 4. Calculate Financials (NET SALES LOGIC)
        $gross_sales = $sales->sum('total_amount');
        
        // Fetch returns with necessary relations for cost calculation
        $returns = $returnQuery->with(['sale.saleItems', 'product'])->get();
        $total_returns = $returns->sum('refund_amount');
        $total_sales = $gross_sales - $total_returns; // Net Sales

        $total_transactions = $sales->count();
        $cash_sales = $sales->where('payment_method', 'cash')->sum('total_amount');
        $credit_sales = $sales->where('payment_method', 'credit')->sum('total_amount');
        $digital_sales = $sales->where('payment_method', 'digital')->sum('total_amount');

        // [FIX] Realized Revenue (Cash Basis) for Tithes
        // 1. Calculate Credit Collections for the period
        $collectionQuery = \App\Models\CreditPayment::query();
        if ($targetStore !== 'all') {
             $collectionQuery->whereHas('credit.sale', function($q) use ($targetStore) {
                $q->where('store_id', $targetStore);
             });
        }
        
        // Match Date Filter
        if ($type === 'daily') {
            $collectionQuery->whereDate('payment_date', $startDate);
        } elseif ($type === 'weekly') {
            $startW = Carbon::parse($startDate)->startOfWeek();
            $endW = Carbon::parse($startDate)->endOfWeek();
            $collectionQuery->whereBetween('payment_date', [$startW, $endW]);
        } elseif ($type === 'monthly') {
             $collectionQuery->whereMonth('payment_date', Carbon::parse($startDate)->month)
                             ->whereYear('payment_date', Carbon::parse($startDate)->year);
        }
        $collected_amount = $collectionQuery->sum('amount');

        // 2. Calculate Cash Refunds only
        $cash_refunds = $returns->filter(function($ret) {
            return $ret->sale && $ret->sale->payment_method !== 'credit';
        })->sum('refund_amount');

        // 3. Realized Revenue = (Cash + Digital + Collections) - Cash Refunds
        $realized_revenue = ($cash_sales + $digital_sales + $collected_amount) - $cash_refunds;

        // Tithes (Based on Realized / Cash Basis)
        $tithesEnabled = \App\Models\Setting::where('key', 'enable_tithes')->value('value') ?? '1'; 
        $tithesAmount = ($tithesEnabled == '1') ? max(0, $realized_revenue * 0.10) : 0;

        // Gross Profit Calculation (Sales - Cost - Returns)
        // Gross Profit Calculation (Realized / Cash Basis)
        
        // A. Profit from Cash Sales
        // Filter soldItems to only include those from Cash/Digital sales
        $cashSoldItems = $soldItems->filter(function($item) {
             return $item->sale && in_array($item->sale->payment_method, ['cash', 'digital']);
        });

        $cashSalesCost = 0;
        foreach ($cashSoldItems as $item) {
            $itemCost = ($item->cost > 0) ? $item->cost : ($item->product->cost ?? 0);
            $cashSalesCost += ($itemCost * $item->quantity);
        }
        $cashSalesRev = $sales->whereIn('payment_method', ['cash', 'digital'])->sum('total_amount');
        $cashProfit = $cashSalesRev - $cashSalesCost;

        // B. Profit from Collections 
        // We need to fetch the actual collection records, not just the sum
        // Reuse the query logic from earlier but get models
        $collectionQueryModels = \App\Models\CreditPayment::query();
        if ($targetStore !== 'all') {
             $collectionQueryModels->whereHas('credit.sale', function($q) use ($targetStore) {
                $q->where('store_id', $targetStore);
             });
        }
        if ($type === 'daily') {
            $collectionQueryModels->whereDate('payment_date', $startDate);
        } elseif ($type === 'weekly') {
            $collectionQueryModels->whereBetween('payment_date', [$startW, $endW]);
        } elseif ($type === 'monthly') {
             $collectionQueryModels->whereMonth('payment_date', Carbon::parse($startDate)->month)
                                   ->whereYear('payment_date', Carbon::parse($startDate)->year);
        }
        
        $collectionsParam = $collectionQueryModels->with(['credit.sale.saleItems'])->get();
        
        $collectionProfit = 0;
        foreach ($collectionsParam as $payment) {
            $sale = $payment->credit->sale ?? null;
            if (!$sale) continue;
            
            $saleTotal = $sale->total_amount > 0 ? $sale->total_amount : 1;
            $paymentRatio = $payment->amount / $saleTotal;

            // Calculate Original Cost
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

        // C. Profit Lost on Cash Returns
        // We only deduct profit if we refunded pure cash (since Credit Refunds don't affect realized profit yet)
        $cashReturns = $returns->filter(fn($r) => $r->sale && $r->sale->payment_method !== 'credit');
        $cashRefundAmount = $cashReturns->sum('refund_amount');
        
        $cashReturnCost = 0;
        foreach ($cashReturns as $ret) {
             $originalItem = null;
             if ($ret->sale && $ret->sale->saleItems) {
                 $originalItem = $ret->sale->saleItems->where('product_id', $ret->product_id)->first();
             }
             $uCost = ($originalItem && $originalItem->cost > 0) ? $originalItem->cost : ($ret->product->cost ?? 0);
             $cashReturnCost += ($uCost * $ret->quantity);
        }
        $profitLost = $cashRefundAmount - $cashReturnCost;

        // FINAL GROSS PROFIT (Realized)
        $gross_profit = $cashProfit + $collectionProfit - $profitLost;
        
        // Maintain legacy variables for view if needed, or just overwrite
        // We set $total_cost and $net_cost to simplified values or 0 as they are less relevant in Hybrid Cash Basis
        // But let's calculate them for the Cash portion so "Margin" makes sense if calculated.
        $net_cost = $cashSalesCost + ($collectionProfit > 0 ? ($collected_amount - $collectionProfit) : 0); // Approximation 

        // 5. Analytics Data
        // 5. Analytics Data (NET Basis)

        // Top Items (Net Quantity & Net Revenue)
        $topItems = SaleItem::select(
                'product_id', 
                DB::raw('SUM(quantity) as gross_qty'),
                DB::raw('SUM(price * quantity) as gross_rev')
            )
            ->whereIn('sale_id', $salesIds)
            ->groupBy('product_id')
            ->with('product')
            ->get()
            ->map(function ($item) use ($salesIds) {
                // Calculate Returns for this product within the filtered sales
                $returned = SalesReturn::whereIn('sale_id', $salesIds)
                    ->where('product_id', $item->product_id)
                    ->selectRaw('SUM(quantity) as qty, SUM(refund_amount) as amt')
                    ->first();

                $netQty = $item->gross_qty - ($returned->qty ?? 0);
                $netRev = $item->gross_rev - ($returned->amt ?? 0);

                return (object) [
                    'product_id' => $item->product_id,
                    'product' => $item->product,
                    'total_qty' => max(0, $netQty),
                    'total_revenue' => max(0, $netRev)
                ];
            })
            ->filter(fn($item) => $item->total_qty > 0) // Filter out items with 0 net sales
            ->sortByDesc('total_qty') // Re-sort by NET quantity
            ->take(10);


        // Top Customers (Net Spent)
        $topCustomers = Sale::select('customer_id', DB::raw('SUM(total_amount) as gross_spent'), DB::raw('count(*) as trans_count'))
            ->whereNotNull('customer_id')
            ->whereIn('id', $salesIds)
            ->groupBy('customer_id')
            ->with('customer')
            ->get()
            ->map(function ($c) use ($salesIds) {
                // Calculate Returns for this customer
                $returns = SalesReturn::whereHas('sale', function($q) use ($c, $salesIds) {
                    $q->whereIn('id', $salesIds)->where('customer_id', $c->customer_id);
                })->sum('refund_amount');

                return (object) [
                    'customer_id' => $c->customer_id,
                    'customer' => $c->customer,
                    'trans_count' => $c->trans_count,
                    'total_spent' => max(0, $c->gross_spent - $returns) // Net Spent
                ];
            })
            ->sortByDesc('total_spent')
            ->take(5);


        $soldProductIds = \App\Models\SaleItem::whereIn('sale_id', $salesIds)->pluck('product_id')->unique();
        $slowMovingItems = \App\Models\Product::whereNotIn('id', $soldProductIds)
            ->where('stock', '>', 0)
            ->take(10)
            ->get();

        // Sales By Category (Net Revenue)
        $categorySalesRaw = SaleItem::select('categories.name', DB::raw('SUM(sale_items.price * sale_items.quantity) as gross_revenue'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('sale_items.sale_id', $salesIds)
            ->groupBy('categories.name')
            ->get();

        $salesByCategory = $categorySalesRaw->map(function ($cat) use ($salesIds) {
             // Calculate Returns for this category
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

        $stores = \App\Models\Store::all();

        return view('admin.reports.index', compact(
            'sales', 'total_sales', 'gross_sales', 'total_returns', 'total_transactions', 
            'cash_sales', 'credit_sales', 'digital_sales', 'collected_amount', 'realized_revenue',
            'type', 'startDate', 'endDate', 'date',
            'topItems', 'topCustomers', 'salesByCategory', 'slowMovingItems',
            'tithesAmount', 'tithesEnabled', 'gross_profit',
            'stores', 'targetStore', 'isMultiStore'
        ));
    }

    // 2. Inventory Report
    public function inventoryReport()
    {
        $storeId = $this->getActiveStoreId();
        
        $inventory = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->select('products.*', 'inventories.stock as current_stock', 'inventories.reorder_point')
            ->get();

        $totalValue = $inventory->sum(fn($p) => $p->price * $p->current_stock);
        $totalCost = $inventory->sum(fn($p) => ($p->cost ?? 0) * $p->current_stock);

        return view('admin.reports.inventory', compact('inventory', 'totalValue', 'totalCost'));
    }

    // 3. Credit Report
    public function creditReport()
    {
        $credits = CustomerCredit::with('customer')->where('is_paid', false)->get();
        $totalReceivables = $credits->sum('remaining_balance');

        return view('admin.reports.credits', compact('credits', 'totalReceivables'));
    }

    // 4. Forecast Report (ENTERPRISE GRADE)
    public function forecast(Request $request)
    {
        $storeId = $this->getActiveStoreId();
        $daysToAnalyze = 30; // Standard 30-day velocity window
        $startDate = Carbon::now()->subDays($daysToAnalyze);

        // 1. Fetch Products with Store-Specific Inventory & Sales Data
        // We use leftJoin for sales to include items with 0 sales (Non-Moving)
        $products = Product::select('products.*', 'inventories.stock as current_stock', 'inventories.reorder_point')
            ->join('inventories', function($join) use ($storeId) {
                $join->on('products.id', '=', 'inventories.product_id')
                     ->where('inventories.store_id', '=', $storeId);
            })
            ->with(['category'])
            ->withSum(['saleItems as total_qty_sold' => function($q) use ($startDate, $storeId) {
                // IMPORTANT: Filter sales by STORE and DATE
                $q->whereHas('sale', function($sq) use ($startDate, $storeId) {
                    $sq->where('created_at', '>=', $startDate)
                       ->where('store_id', $storeId);
                });
            }], 'quantity')
            ->withSum(['saleItems as total_revenue_generated' => function($q) use ($startDate, $storeId) {
                // Calculate Revenue for ABC Analysis
                $q->select(DB::raw('SUM(price * quantity)'));
                $q->whereHas('sale', function($sq) use ($startDate, $storeId) {
                    $sq->where('created_at', '>=', $startDate)
                       ->where('store_id', $storeId);
                });
            }], 'quantity') // Note: withSum requires a column, but we overrode the select. 
            // Alternative: Fetch simple sum and calc in PHP to be safer against nulls.
            ->get();

        // 2. Prepare Data Structure
        $forecastData = [];
        $totalRevenue = 0;

        foreach ($products as $p) {
            $qtySold = $p->total_qty_sold ?? 0;
            // Fallback revenue calc if withSum is tricky
            $revenue = $qtySold * $p->price; 
            
            $p->real_revenue = $revenue; // Attach for sorting
            $totalRevenue += $revenue;

            // Velocity (Items per day)
            $velocity = $qtySold / $daysToAnalyze;

            // Days of Inventory (DOI)
            // Avoid division by zero. If velocity is 0, DOI is infinite (999)
            $doi = ($velocity > 0) ? ($p->current_stock / $velocity) : 999;

            $forecastData[] = [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category->name ?? 'Uncategorized',
                'stock' => $p->current_stock,
                'reorder_point' => $p->reorder_point,
                'velocity' => $velocity, // Float
                'doi' => $doi,           // Float (Days)
                'revenue' => $revenue,
                'status' => 'Healthy',   // Placeholder
                'class' => 'C',          // Placeholder
                'movement' => 'Non-Moving' // Placeholder
            ];
        }

        // 3. ABC Analysis (Sort by Revenue DESC)
        usort($forecastData, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        $cumulativeRevenue = 0;
        foreach ($forecastData as &$item) {
            $cumulativeRevenue += $item['revenue'];
            $percentage = ($totalRevenue > 0) ? ($cumulativeRevenue / $totalRevenue) : 0;

            // Standard Pareto Principle (80/15/5)
            if ($percentage <= 0.80) {
                $item['class'] = 'A'; // High Value
            } elseif ($percentage <= 0.95) {
                $item['class'] = 'B'; // Medium Value
            } else {
                $item['class'] = 'C'; // Low Value
            }
        }
        unset($item); // Break reference

        // 4. Movement Classification (Sort by Velocity DESC to find Fast Movers)
        // We'll define "Fast" as the top 20% of items associated with sales activity
        // Or simpler: Based on absolute velocity? Let's use relative percentile for "Enterprise" feel.
        
        // Let's stick to concrete logic for now:
        // Fast: > 1 item/day
        // Average: > 0.1 item/day
        // Slow: > 0
        // Non-Moving: 0
        
        foreach ($forecastData as &$item) {
            $v = $item['velocity'];
            
            if ($v >= 1.0) $item['movement'] = 'Fast Moving';
            elseif ($v > 0.1) $item['movement'] = 'Average';
            elseif ($v > 0) $item['movement'] = 'Slow Moving';
            else $item['movement'] = 'Non-Moving';

            // Stock Health Status
            if ($item['stock'] == 0) {
                $item['status'] = 'Out of Stock';
            } elseif ($item['stock'] <= $item['reorder_point']) {
                $item['status'] = 'Critical';
            } elseif ($item['doi'] <= 7) {
                $item['status'] = 'Low';
            } else {
                $item['status'] = 'Healthy';
            }

            // Suggested Reorder
            // Target: 14 Days of Supply (Safety Stock)
            $targetStock = $item['velocity'] * 14; 
            if ($item['stock'] < $targetStock) {
                $item['reorder_qty'] = ceil($targetStock - $item['stock']);
            } else {
                $item['reorder_qty'] = 0;
            }
        }
        unset($item);

        // 5. Final Sort: Prioritize "Out of Stock" and "Critical" items for the user
        usort($forecastData, function ($a, $b) {
            // Priority 1: Status Importance
            $statusOrder = ['Out of Stock' => 1, 'Critical' => 2, 'Low' => 3, 'Healthy' => 4];
            $statusCompare = $statusOrder[$a['status']] <=> $statusOrder[$b['status']];
            if ($statusCompare !== 0) return $statusCompare;

            // Priority 2: ABC Class (A items first)
            $classCompare = strcmp($a['class'], $b['class']);
            if ($classCompare !== 0) return $classCompare;

            return 0;
        });

        // 6. Summary Metrics
        $totalStockValue = array_sum(array_column($forecastData, 'revenue')); // Reuse revenue for simplicity or fetch cost
        $outOfStockCount = count(array_filter($forecastData, fn($i) => $i['status'] === 'Out of Stock'));
        $criticalCount = count(array_filter($forecastData, fn($i) => $i['status'] === 'Critical'));

        return view('admin.reports.forecast', compact('forecastData', 'outOfStockCount', 'criticalCount'));
    }

    // 5. EXPORT FUNCTION (Updated for BIR Tax Columns)
    public function export(Request $request)
    {
        $reportType = $request->input('report_type', 'sales');
        $filename = "{$reportType}_report_" . date('Y-m-d') . ".csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache"
        ];

        $callback = function() use ($reportType, $request) {
            $file = fopen('php://output', 'w');

            if ($reportType === 'sales') {
                $this->exportSalesLogic($file, $request);
            } elseif ($reportType === 'inventory') {
                $this->exportInventoryLogic($file);
            } elseif ($reportType === 'credits') {
                $this->exportCreditsLogic($file);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // --- HELPER FUNCTIONS FOR EXPORT ---

    private function exportSalesLogic($file, $request) {
        // BIR Sales Book Style Columns
        fputcsv($file, ['OR Number', 'Date', 'Customer', 'Gross Sales', 'Vatable Sales', 'VAT Amount', 'VAT Exempt', 'Net Sales']);
        
        $storeId = $this->getActiveStoreId();
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        
        // Fetch Settings for Tax Rate
        $taxRate = (float) (\App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 12);
        $taxType = \App\Models\Setting::where('key', 'tax_type')->value('value') ?? 'inclusive'; 

        $sales = Sale::with(['user', 'customer'])
                     ->where('store_id', $storeId)
                     ->whereDate('created_at', $startDate)
                     ->get();

        foreach ($sales as $sale) {
            $total = $sale->total_amount;
            
            // Tax Logic
            $vatable = 0;
            $vatAmount = 0;
            $vatExempt = 0;

            if ($taxType === 'non_vat') {
                $vatExempt = $total;
            } else {
                // Back-compute VAT (Inclusive)
                $vatable = $total / (1 + ($taxRate / 100));
                $vatAmount = $total - $vatable;
            }

            fputcsv($file, [
                $sale->id,
                $sale->created_at->format('Y-m-d H:i'),
                $sale->customer->name ?? 'Walk-in',
                number_format($total, 2, '.', ''), // Gross
                number_format($vatable, 2, '.', ''), // Vatable
                number_format($vatAmount, 2, '.', ''), // VAT
                number_format($vatExempt, 2, '.', ''), // Exempt
                number_format($total, 2, '.', '') // Net (assuming no returns on individual line for now)
            ]);
        }
    }

    private function exportInventoryLogic($file) {
        fputcsv($file, ['Product', 'Barcode', 'Category', 'Stock', 'Cost', 'Price', 'Status']);
        $storeId = $this->getActiveStoreId();
        $inventory = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->with('category')
            ->select('products.*', 'inventories.stock as current_stock', 'inventories.reorder_point')
            ->get();

        foreach ($inventory as $item) {
            $status = ($item->current_stock <= $item->reorder_point) ? 'Low Stock' : 'Good';
            if ($item->current_stock == 0) $status = 'Out of Stock';

            fputcsv($file, [
                $item->name,
                $item->sku,
                $item->category->name ?? 'None',
                $item->current_stock,
                $item->cost,
                $item->price,
                $status
            ]);
        }
    }

    private function exportCreditsLogic($file) {
        fputcsv($file, ['Customer', 'Sale ID', 'Date', 'Due Date', 'Total Debt', 'Paid', 'Balance']);
        $credits = CustomerCredit::with('customer')->where('is_paid', false)->get();

        foreach ($credits as $credit) {
            fputcsv($file, [
                $credit->customer->name ?? 'Unknown',
                $credit->sale_id,
                $credit->created_at->format('Y-m-d'),
                $credit->due_date,
                $credit->total_amount,
                $credit->amount_paid,
                $credit->remaining_balance
            ]);
        }
    }

    public function vatReport(Request $request)
    {
        $storeId = $this->getActiveStoreId();
        $start = $request->input('start_date', Carbon::now()->startOfMonth());
        $end = $request->input('end_date', Carbon::now()->endOfMonth());

        // 1. Calculate OUTPUT VAT (Sales)
        $salesData = Sale::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                SUM(total_amount) as gross_sales,
                SUM(vatable_sales) as vatable_sales,
                SUM(output_vat) as output_vat
            ')->first();

        // 2. Calculate INPUT VAT (Purchases)
        $purchaseData = \App\Models\Purchase::where('store_id', $storeId)
            ->whereBetween('purchase_date', [$start, $end])
            ->selectRaw('
                SUM(total_cost) as total_purchases,
                SUM(input_vat) as input_vat
            ')->first();

        // 3. Compute Payable
        $outputVat = $salesData->output_vat ?? 0;
        $inputVat = $purchaseData->input_vat ?? 0;
        $netPayable = $outputVat - $inputVat;

        return view('admin.reports.vat', compact(
            'salesData', 'purchaseData', 'netPayable', 'start', 'end'
        ));
    }
}