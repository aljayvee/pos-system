<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $type = $request->input('type', 'daily'); // daily, monthly, yearly

        $query = Sale::with('user', 'customer')->latest();

        // Filter based on Type
        if ($type === 'daily') {
            $query->whereDate('created_at', $date);
        } elseif ($type === 'monthly') {
            $query->whereMonth('created_at', Carbon::parse($date)->month)
                  ->whereYear('created_at', Carbon::parse($date)->year);
        }

        $sales = $query->get();

        // Calculate Totals
        $total_sales = $sales->sum('total_amount');
        $total_transactions = $sales->count();
        $cash_sales = $sales->where('payment_method', 'cash')->sum('total_amount');
        $credit_sales = $sales->where('payment_method', 'credit')->sum('total_amount');

        return view('admin.reports.index', compact('sales', 'total_sales', 'total_transactions', 'cash_sales', 'credit_sales', 'date', 'type'));
    }
}