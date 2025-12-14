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
        $today = \Carbon\Carbon::today();
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();

        // 1. DETERMINE ACTIVE STORE
        $multiStoreEnabled = \App\Models\Setting::where('key', 'enable_multi_store')->value('value') ?? '0';
        $storeId = ($multiStoreEnabled == '1') ? session('active_store_id', auth()->user()->store_id ?? 1) : 1;

        // 2. SALES METRICS
        $salesQuery = \App\Models\Sale::where('store_id', $storeId);
        $salesToday = (clone $salesQuery)->whereDate('created_at', $today)->sum('total_amount');
        $salesMonth = (clone $salesQuery)->where('created_at', '>=', $startOfMonth)->sum('total_amount');
        $transactionCountToday = (clone $salesQuery)->whereDate('created_at', $today)->count();
        $totalCredits = \App\Models\CustomerCredit::where('is_paid', false)->sum('remaining_balance');

        // 3. PROFIT CALCULATION
        $soldItemsToday = \App\Models\SaleItem::whereHas('sale', function($q) use ($today, $storeId) {
            $q->where('store_id', $storeId)->whereDate('created_at', $today);
        })->with('product')->get();

        $totalCostToday = 0;
        foreach($soldItemsToday as $item) {
            $cost = $item->cost > 0 ? $item->cost : ($item->product->cost ?? 0);
            $totalCostToday += ($cost * $item->quantity);
        }
        $profitToday = $salesToday - $totalCostToday;

        // 4. LOW STOCK LOGIC (FIXED)
        $lowStockItems = \Illuminate\Support\Facades\DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.store_id', $storeId)
            ->whereNull('products.deleted_at')
            // FIX: Compare against Product's reorder point (User Input), not just Inventory default
            ->where(function($q) {
                $q->whereColumn('inventories.stock', '<=', 'products.reorder_point')
                  ->where('inventories.stock', '>', 0); // Strictly low, not zero
            })
            ->select(
                'products.id',
                'products.name',
                'products.unit', // Added Unit
                'products.reorder_point', // The threshold
                'inventories.stock as current_stock' // The real stock
            )
            ->take(10)
            ->get();
            
        $outOfStockItems = \Illuminate\Support\Facades\DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.store_id', $storeId)
            ->where('inventories.stock', '<=', 0)
            ->whereNull('products.deleted_at')
            ->count();

        // 5. SALES CHART
        $salesData = \App\Models\Sale::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->where('store_id', $storeId)
            ->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // 6. EXPIRING ITEMS
        $expiringItems = \Illuminate\Support\Facades\DB::table('products')
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->where('inventories.stock', '>', 0)
            ->whereNotNull('products.expiration_date')
            ->where('products.expiration_date', '<=', \Carbon\Carbon::now()->addDays(7))
            ->select('products.*', 'inventories.stock as current_stock')
            ->orderBy('products.expiration_date', 'asc')
            ->take(5)
            ->get();

        // Chart Preparation
        $chartLabels = [];
        $chartValues = [];
        $period = \Carbon\CarbonPeriod::create(\Carbon\Carbon::now()->subDays(29), \Carbon\Carbon::now());
        
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