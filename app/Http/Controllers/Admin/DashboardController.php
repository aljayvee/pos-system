<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem; // NEW: Import SaleItem
use App\Models\Product;
use App\Models\CustomerCredit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Existing Stats
        $salesToday = Sale::whereDate('created_at', $today)->sum('total_amount');
        $salesMonth = Sale::where('created_at', '>=', $startOfMonth)->sum('total_amount');
        $transactionCountToday = Sale::whereDate('created_at', $today)->count();
        $totalCredits = CustomerCredit::where('is_paid', false)->sum('remaining_balance');

        // --- NEW: Calculate Today's Gross Profit ---
        // Formula: Sales - Total Cost of Goods Sold (COGS)
        // We need to check items sold TODAY and their cost price
        $soldItemsToday = SaleItem::whereHas('sale', function($q) use ($today) {
            $q->whereDate('created_at', $today);
        })->get();

        $totalCostToday = 0;
        foreach($soldItemsToday as $item) {
            $cost = $item->cost ?? 0; // Get cost from product table
            $totalCostToday += ($cost * $item->quantity);
        }
        
        $profitToday = $salesToday - $totalCostToday;
        // -------------------------------------------

        // 2. Low Stock Alerts
        $lowStockItems = Product::whereColumn('stock', '<=', 'reorder_point')
                                ->where('stock', '>', 0)
                                ->orderBy('stock', 'asc')
                                ->take(10)
                                ->get();
        
        $outOfStockItems = Product::where('stock', 0)->count();

        // 3. Sales Trend Data (Last 30 Days)
        $salesData = Sale::select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('SUM(total_amount) as total')
            )
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
            'salesToday', 
            'salesMonth', 
            'transactionCountToday', 
            'totalCredits', 
            'lowStockItems', 
            'outOfStockItems',
            'chartLabels',
            'chartValues',
            'profitToday' // Pass this new variable
        ));
    }
}