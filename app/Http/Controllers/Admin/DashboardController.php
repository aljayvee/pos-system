<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\CustomerCredit;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Date Helpers
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 2. Sales Stats
        $salesToday = Sale::whereDate('created_at', $today)->sum('total_amount');
        $salesMonth = Sale::where('created_at', '>=', $startOfMonth)->sum('total_amount');
        $transactionCountToday = Sale::whereDate('created_at', $today)->count();

        // 3. Credit Stats (Receivables)
        $totalCredits = CustomerCredit::sum('remaining_balance'); // Total money people owe the store

        // 4. Low Stock Alerts (Threshold: Less than 10 items)
        $lowStockItems = Product::where('stock', '<=', 10)
                                ->where('stock', '>', 0) // Exclude already out of stock if you want
                                ->orderBy('stock', 'asc')
                                ->take(5) // Show top 5 critical items
                                ->get();
        
        $outOfStockItems = Product::where('stock', 0)->count();

        return view('admin.dashboard', compact(
            'salesToday', 
            'salesMonth', 
            'transactionCountToday', 
            'totalCredits', 
            'lowStockItems',
            'outOfStockItems'
        ));
    }
}