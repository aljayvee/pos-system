<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\CustomerCredit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Don't forget this!

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
        $totalCredits = CustomerCredit::where('is_paid', false)->sum('remaining_balance'); // Only count unpaid

        // 2. Low Stock Alerts (Dynamic Threshold)
        // We compare stock column directly against reorder_point column
        $lowStockItems = Product::whereColumn('stock', '<=', 'reorder_point')
                                ->where('stock', '>', 0)
                                ->orderBy('stock', 'asc')
                                ->take(10)
                                ->get();
        
        $outOfStockItems = Product::where('stock', 0)->count();

        // --- NEW: SALES TREND DATA (Last 30 Days) ---
        // Get sales for the last 30 days, grouped by date
        $salesData = Sale::select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('SUM(total_amount) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Format data for Chart.js (Labels and Values)
        $chartLabels = [];
        $chartValues = [];
        
        // Fill in missing days with 0 (Optional but good for charts)
        $period = \Carbon\CarbonPeriod::create(Carbon::now()->subDays(29), Carbon::now());
        
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M d'); // Label: "Dec 12"
            
            // Find the sale for this date, or 0 if none
            $sale = $salesData->firstWhere('date', $formattedDate);
            $chartValues[] = $sale ? $sale->total : 0;
        }
        // ---------------------------------------------

        

        return view('admin.dashboard', compact(
            'salesToday', 
            'salesMonth', 
            'transactionCountToday', 
            'totalCredits', 
            'lowStockItems', 
            'outOfStockItems',
            'chartLabels', // Pass these to view
            'chartValues'
        ));
    }
}