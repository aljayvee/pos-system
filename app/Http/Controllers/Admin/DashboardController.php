<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem; // NEW: Import SaleItem
use App\Models\Product;
use App\Models\CustomerCredit;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. DETERMINE ACTIVE STORE CONTEXT
        $multiStoreEnabled = Setting::where('key', 'enable_multi_store')->value('value') ?? '0';
        // Default to Store 1 if feature is off
        $storeId = ($multiStoreEnabled == '1') ? session('active_store_id', auth()->user()->store_id ?? 1) : 1;

        // 2. Filter Sales by Store ID
        $salesQuery = Sale::where('store_id', $storeId);
        
        // Clone the query builder for different metrics to avoid interference
        $salesToday = (clone $salesQuery)->whereDate('created_at', $today)->sum('total_amount');
        $salesMonth = (clone $salesQuery)->where('created_at', '>=', $startOfMonth)->sum('total_amount');
        $transactionCountToday = (clone $salesQuery)->whereDate('created_at', $today)->count();
        
        // NOTE: Credits are usually Global (User owes the "Company"), but if you want per-store credit, 
        // you would need to add store_id to customer_credits table. 
        // For now, we keep credits global or filter if you added the column.
        $totalCredits = CustomerCredit::where('is_paid', false)->sum('remaining_balance');

        // 3. Calculate Profit (Filtered by Store)
        $soldItemsToday = SaleItem::whereHas('sale', function($q) use ($today, $storeId) {
            $q->where('store_id', $storeId)->whereDate('created_at', $today);
        })->with('product')->get();

        $totalCostToday = 0;
        foreach($soldItemsToday as $item) {
            $cost = $item->cost > 0 ? $item->cost : ($item->product->cost ?? 0);
            $totalCostToday += ($cost * $item->quantity);
        }
        $profitToday = $salesToday - $totalCostToday;

        // 4. Inventory Alerts (Filtered by Store using the Inventory Table logic)
        // Since Product->stock is an accessor that already uses 'active_store_id' session,
        // we can iterate products, but for performance, querying the Inventory table is better.
        
        $lowStockItems = DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.store_id', $storeId)
            ->whereColumn('inventories.stock', '<=', 'inventories.reorder_point')
            ->where('inventories.stock', '>', 0)
            ->whereNull('products.deleted_at')
            ->select('products.*', 'inventories.stock as current_stock')
            ->take(10)
            ->get();
            
        $outOfStockItems = DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.store_id', $storeId)
            ->where('inventories.stock', 0)
            ->whereNull('products.deleted_at')
            ->count();

        // 5. Sales Chart (Filtered)
        $salesData = Sale::select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('SUM(total_amount) as total')
            )
            ->where('store_id', $storeId) // <--- FILTER HERE
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $chartLabels = [];
        $chartValues = [];
        $period = \Carbon\CarbonPeriod::create(Carbon::now()->subDays(29), Carbon::now());
        
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M d');
            $sale = $salesData->firstWhere('date', $formattedDate);
            $chartValues[] = $sale ? $sale->total : 0;
        }

        return view('admin.dashboard', compact(
            'salesToday', 'salesMonth', 'transactionCountToday', 'totalCredits', 
            'lowStockItems', 'outOfStockItems', 'chartLabels', 'chartValues', 'profitToday'
        ));
    }
}