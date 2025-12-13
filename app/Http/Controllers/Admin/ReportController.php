<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\CustomerCredit;
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
        $date = $startDate; // For View Compatibility

        // 2. Multi-Store Context Logic
        $isMultiStore = \App\Models\Setting::where('key', 'enable_multi_store')->value('value') ?? '0';
        $activeStoreId = $this->getActiveStoreId();
        
        // Determine Target Store: Default to active, but allow 'all' if selected
        $targetStore = $request->input('store_filter', $activeStoreId);

        // 3. Build Query
        $query = Sale::with('user', 'customer')->latest();

        // Apply Store Filter (If not 'all', filter by specific ID)
        if ($targetStore !== 'all') {
            $query->where('store_id', $targetStore);
        }

        // Apply Date Filter
        if ($type === 'daily') {
            $query->whereDate('created_at', $startDate);
        } elseif ($type === 'weekly') {
            $start = Carbon::parse($startDate)->startOfWeek();
            $end = Carbon::parse($startDate)->endOfWeek();
            $query->whereBetween('created_at', [$start, $end]);
        } elseif ($type === 'monthly') {
            $query->whereMonth('created_at', Carbon::parse($startDate)->month)
                  ->whereYear('created_at', Carbon::parse($startDate)->year);
        } elseif ($type === 'custom') {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(), 
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $sales = $query->get();
        $salesIds = $sales->pluck('id');

        // 4. Calculate Financials
        $total_sales = $sales->sum('total_amount');
        $total_transactions = $sales->count();
        $cash_sales = $sales->where('payment_method', 'cash')->sum('total_amount');
        $credit_sales = $sales->where('payment_method', 'credit')->sum('total_amount');
        $digital_sales = $sales->where('payment_method', 'digital')->sum('total_amount');

        // Tithes Calculation (10% if enabled)
        $tithesEnabled = \App\Models\Setting::where('key', 'enable_tithes')->value('value') ?? '1'; 
        $tithesAmount = ($tithesEnabled == '1') ? $total_sales * 0.10 : 0;

        // Gross Profit Calculation
        $soldItems = SaleItem::whereIn('sale_id', $salesIds)->with('product')->get();
        $total_cost = 0;
        foreach ($soldItems as $item) {
            // Use recorded cost if available, otherwise fallback to product master cost
            $itemCost = ($item->cost > 0) ? $item->cost : ($item->product->cost ?? 0);
            $total_cost += ($itemCost * $item->quantity);
        }
        $gross_profit = $total_sales - $total_cost;

        // 5. Analytics Data
        
        // Top Items
        $topItems = SaleItem::select('product_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(price * quantity) as total_revenue'))
            ->whereIn('sale_id', $salesIds)
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->take(10)
            ->get();

        // Top Customers
        $topCustomers = Sale::select('customer_id', DB::raw('sum(total_amount) as total_spent'), DB::raw('count(*) as trans_count'))
            ->whereNotNull('customer_id')
            ->whereIn('id', $salesIds)
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->with('customer')
            ->take(5)
            ->get();

        // Slow Moving Items
        $soldProductIds = \App\Models\SaleItem::whereIn('sale_id', $salesIds)->pluck('product_id')->unique();
        $slowMovingItems = \App\Models\Product::whereNotIn('id', $soldProductIds)
            ->where('stock', '>', 0)
            ->take(10)
            ->get();

        // Sales By Category
        $salesByCategory = SaleItem::select('categories.name', DB::raw('sum(sale_items.price * sale_items.quantity) as total_revenue'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('sale_items.sale_id', $salesIds)
            ->groupBy('categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Fetch Stores list for the dropdown
        $stores = \App\Models\Store::all();

        return view('admin.reports.index', compact(
            'sales', 'total_sales', 'total_transactions', 
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

    // 4. Forecast Report (FIXED: Logic Restored)
    public function forecast()
    {
        // 1. Get Sales Data for Last 30 Days
        $startDate = Carbon::now()->subDays(30);
        
        // Fetch products with their sales history
        $products = Product::with(['category', 'saleItems' => function($q) use ($startDate) {
            $q->whereHas('sale', function($sq) use ($startDate) {
                $sq->where('created_at', '>=', $startDate);
            });
        }])->get();

        $forecastData = [];

        foreach ($products as $product) {
            // Calculate Total Sold in last 30 days
            $totalSold = $product->saleItems->sum('quantity');
            
            // Average Daily Sales (ADS)
            $ads = $totalSold / 30;

            // Estimated Days Until Stockout
            $daysLeft = ($ads > 0) ? ($product->stock / $ads) : 999;

            // Reorder Recommendation (Target 14 days stock)
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

        // Sort by "Days Left" ascending (Most critical first)
        usort($forecastData, function ($a, $b) {
            return $a['days_left'] <=> $b['days_left'];
        });

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

    // 5. EXPORT FUNCTION
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
        fputcsv($file, ['Sale ID', 'Date', 'Customer', 'Items', 'Total', 'Payment', 'Profit']);
        
        $storeId = $this->getActiveStoreId();
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        
        $sales = Sale::with(['user', 'customer', 'saleItems.product'])
                     ->where('store_id', $storeId)
                     ->whereDate('created_at', $startDate)
                     ->get();

        foreach ($sales as $sale) {
            $cost = $sale->saleItems->sum(fn($item) => ($item->cost ?: $item->product->cost ?: 0) * $item->quantity);
            $profit = $sale->total_amount - $cost;
            $itemsList = $sale->saleItems->map(fn($i) => "{$i->product->name} ({$i->quantity})")->join(', ');

            fputcsv($file, [
                $sale->id,
                $sale->created_at->format('Y-m-d H:i'),
                $sale->customer->name ?? 'Walk-in',
                $itemsList,
                $sale->total_amount,
                ucfirst($sale->payment_method),
                number_format($profit, 2)
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