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

        // 4. Calculate Financials (NET SALES LOGIC)
        $gross_sales = $sales->sum('total_amount');
        $total_returns = $returnQuery->sum('refund_amount');
        $total_sales = $gross_sales - $total_returns; // Net Sales

        $total_transactions = $sales->count();
        $cash_sales = $sales->where('payment_method', 'cash')->sum('total_amount');
        $credit_sales = $sales->where('payment_method', 'credit')->sum('total_amount');
        $digital_sales = $sales->where('payment_method', 'digital')->sum('total_amount');

        // Tithes (Based on Net)
        $tithesEnabled = \App\Models\Setting::where('key', 'enable_tithes')->value('value') ?? '1'; 
        $tithesAmount = ($tithesEnabled == '1') ? $total_sales * 0.10 : 0;

        // Gross Profit Calculation (Sales - Cost - Returns)
        $soldItems = SaleItem::whereIn('sale_id', $salesIds)->with('product')->get();
        $total_cost = 0;
        foreach ($soldItems as $item) {
            $itemCost = ($item->cost > 0) ? $item->cost : ($item->product->cost ?? 0);
            $total_cost += ($itemCost * $item->quantity);
        }
        // Assuming returns are put back to stock or written off, simpler to deduct refund amount from profit for now
        $gross_profit = $total_sales - $total_cost; 

        // 5. Analytics Data
        $topItems = SaleItem::select('product_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(price * quantity) as total_revenue'))
            ->whereIn('sale_id', $salesIds)
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->take(10)
            ->get();

        $topCustomers = Sale::select('customer_id', DB::raw('sum(total_amount) as total_spent'), DB::raw('count(*) as trans_count'))
            ->whereNotNull('customer_id')
            ->whereIn('id', $salesIds)
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->with('customer')
            ->take(5)
            ->get();

        $soldProductIds = \App\Models\SaleItem::whereIn('sale_id', $salesIds)->pluck('product_id')->unique();
        $slowMovingItems = \App\Models\Product::whereNotIn('id', $soldProductIds)
            ->where('stock', '>', 0)
            ->take(10)
            ->get();

        $salesByCategory = SaleItem::select('categories.name', DB::raw('sum(sale_items.price * sale_items.quantity) as total_revenue'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('sale_items.sale_id', $salesIds)
            ->groupBy('categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        $stores = \App\Models\Store::all();

        return view('admin.reports.index', compact(
            'sales', 'total_sales', 'gross_sales', 'total_returns', 'total_transactions', 
            'cash_sales', 'credit_sales', 'digital_sales',
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

    // 4. Forecast Report
    public function forecast()
    {
        $startDate = Carbon::now()->subDays(30);
        $products = Product::with(['category', 'saleItems' => function($q) use ($startDate) {
            $q->whereHas('sale', function($sq) use ($startDate) {
                $sq->where('created_at', '>=', $startDate);
            });
        }])->get();

        $forecastData = [];

        foreach ($products as $product) {
            $totalSold = $product->saleItems->sum('quantity');
            $ads = $totalSold / 30;
            $daysLeft = ($ads > 0) ? ($product->stock / $ads) : 999;
            $targetStock = $ads * 14; 
            $reorderQty = ($product->stock < $targetStock) ? ($targetStock - $product->stock) : 0;

            if ($totalSold > 0 || $product->stock <= $product->reorder_point) {
                $forecastData[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name ?? '-',
                    'stock' => $product->stock,
                    'total_sold_30d' => $totalSold,
                    'ads' => number_format($ads, 2),
                    'days_left' => number_format($daysLeft, 1),
                    'status' => $this->getStockStatus($daysLeft, $product->stock, $product->reorder_point),
                    'reorder_qty' => ceil($reorderQty)
                ];
            }
        }

        usort($forecastData, function ($a, $b) { return $a['days_left'] <=> $b['days_left']; });

        return view('admin.reports.forecast', compact('forecastData'));
    }

    private function getStockStatus($daysLeft, $stock, $reorderPoint)
    {
        if ($stock == 0) return 'Out of Stock';
        if ($stock <= $reorderPoint) return 'Critical Level';
        if ($daysLeft <= 3) return 'Critical (Runs out < 3 days)';
        if ($daysLeft <= 7) return 'Low (Runs out < 1 week)';
        return 'Healthy';
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
}