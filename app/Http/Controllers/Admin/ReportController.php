<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Get Filters
        $type = $request->input('type', 'daily');
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        $date = $startDate; // Compatibility
        
        // COMPATIBILITY FIX: Define $date for backward compatibility with the View
        $date = $startDate; 

        // 2. Build Query
        $query = Sale::with('user', 'customer')->latest();

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

        // 3. Calculate Totals
        $total_sales = $sales->sum('total_amount');
        $total_transactions = $sales->count();
        $cash_sales = $sales->where('payment_method', 'cash')->sum('total_amount');
        $credit_sales = $sales->where('payment_method', 'credit')->sum('total_amount');
        $digital_sales = $sales->where('payment_method', 'digital')->sum('total_amount');

        // 4. Tithes & Profit
        $tithesEnabled = Setting::where('key', 'enable_tithes')->value('value') ?? '1'; 
        $tithesAmount = ($tithesEnabled == '1') ? $total_sales * 0.10 : 0;

        $soldItems = SaleItem::whereIn('sale_id', $salesIds)->with('product')->get();

        $total_cost = 0;
        foreach ($soldItems as $item) {
            $itemCost = ($item->cost > 0) ? $item->cost : ($item->product->cost ?? 0);
            $cost = $item->product->cost ?? 0;
            $total_cost += ($cost * $item->quantity);
        }
        $gross_profit = $total_sales - $total_cost;

        // 5. Top Items
        $topItems = SaleItem::select('product_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(price * quantity) as total_revenue'))
            ->whereIn('sale_id', $salesIds)
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->take(10)
            ->get();

        // 6. Top Customers
        $topCustomers = Sale::select('customer_id', DB::raw('sum(total_amount) as total_spent'), DB::raw('count(*) as trans_count'))
            ->whereNotNull('customer_id')
            ->whereIn('id', $salesIds)
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->with('customer')
            ->take(5)
            ->get();

        // 7. Slow Moving Items
        $soldProductIds = \App\Models\SaleItem::whereIn('sale_id', $salesIds)->pluck('product_id')->unique();
        
        $slowMovingItems = \App\Models\Product::whereNotIn('id', $soldProductIds)
            ->where('stock', '>', 0) // Only count items we actually have
            ->take(10) // Limit to 10
            ->get();


        // 8. NEW: Sales By Category (For Charts)
        // We join sale_items -> products -> categories to sum revenue per category
        $salesByCategory = SaleItem::select('categories.name', DB::raw('sum(sale_items.price * sale_items.quantity) as total_revenue'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('sale_items.sale_id', $salesIds) // Respect the Date Filters!
            ->groupBy('categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        return view('admin.reports.index', compact(
            'sales', 'total_sales', 'total_transactions', 
            'cash_sales', 'credit_sales', 'digital_sales',
            'type', 'startDate', 'endDate', 'date',
            'topItems', 'topCustomers', 'salesByCategory', 'slowMovingItems',
            'tithesAmount', 'tithesEnabled', 'gross_profit'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'daily');
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $query = Sale::with('user', 'customer')->latest();

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

        $filename = "sales_report_{$type}_{$startDate}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Sale ID', 'Date/Time', 'Cashier', 'Customer', 'Payment Method', 'Total Amount', 'Amount Paid']);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->id,
                    $sale->created_at->format('Y-m-d H:i:s'),
                    $sale->user->name,
                    $sale->customer->name ?? 'Walk-in',
                    ucfirst($sale->payment_method),
                    $sale->total_amount,
                    $sale->amount_paid
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}