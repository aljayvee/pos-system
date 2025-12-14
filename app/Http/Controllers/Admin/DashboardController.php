<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
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
        $storeId = ($multiStoreEnabled == '1') ? session('active_store_id', auth()->user()->store_id ?? 1) : 1;

        // 2. Filter Sales by Store ID
        $salesQuery = Sale::where('store_id', $storeId);
        
        $salesToday = (clone $salesQuery)->whereDate('created_at', $today)->sum('total_amount');
        $salesMonth = (clone $salesQuery)->where('created_at', '>=', $startOfMonth)->sum('total_amount');
        $transactionCountToday = (clone $salesQuery)->whereDate('created_at', $today)->count();
        
        // Credits are Global
        $totalCredits = CustomerCredit::where('is_paid', false)->sum('remaining_balance');

        // 3. Calculate Profit
        $soldItemsToday = SaleItem::whereHas('sale', function($q) use ($today, $storeId) {
            $q->where('store_id', $storeId)->whereDate('created_at', $today);
        })->with('product')->get();

        $totalCostToday = 0;
        foreach($soldItemsToday as $item) {
            $cost = $item->cost > 0 ? $item->cost : ($item->product->cost ?? 0);
            $totalCostToday += ($cost * $item->quantity);
        }
        $profitToday = $salesToday - $totalCostToday;

        // 4. Inventory Alerts (FIXED LOGIC)
        // We compare inventory stock against the PRODUCT'S reorder point, not the inventory table's copy.
        
        $lowStockItems = DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.store_id', $storeId)
            ->whereNull('products.deleted_at')
            // FIX: Use products.reorder_point for the threshold check
            ->where(function($query) {
                $query->whereColumn('inventories.stock', '<=', 'products.reorder_point')
                      ->where('inventories.stock', '>', 0); // Exclude out of stock
            })
            ->select(
                'products.id',
                'products.name',
                'products.reorder_point', // Ensure we get the master reorder point
                'inventories.stock as current_stock',
                'products.unit' // Optional: for display
            )
            ->take(10)
            ->get();
            
        $outOfStockItems = DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.store_id', $storeId)
            ->where('inventories.stock', '<=', 0) // Handle negatives as 0
            ->whereNull('products.deleted_at')
            ->count();

        // 5. Sales Chart
        $salesData = Sale::select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('SUM(total_amount) as total')
            )
            ->where('store_id', $storeId)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // 6. Expiring Items
        $expiringItems = DB::table('products')
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->where('inventories.stock', '>', 0)
            ->whereNotNull('products.expiration_date')
            ->where('products.expiration_date', '<=', Carbon::now()->addDays(7))
            ->select('products.*', 'inventories.stock as current_stock')
            ->orderBy('products.expiration_date', 'asc')
            ->take(5)
            ->get();

        // Chart Data Prep
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
            'lowStockItems', 'outOfStockItems', 'chartLabels', 'chartValues', 'profitToday',
            'expiringItems'
        ));
    }
}