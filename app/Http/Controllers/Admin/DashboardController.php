<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Calculate Today's Sales
        $todays_sales = Sale::whereDate('created_at', Carbon::today())
                            ->sum('total_amount');

        // 2. Count Low Stock Items
        // (Items where stock is less than or equal to their specific alert level)
        $low_stock_count = Product::whereColumn('stock', '<=', 'alert_stock')->count();

        // 3. Return the view with data
        return view('admin.dashboard', compact('todays_sales', 'low_stock_count'));
    }
}