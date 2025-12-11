<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse; // Import this!

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // ... (Keep your existing index code exactly as it is) ...
        $date = $request->input('date', Carbon::today()->toDateString());
        $type = $request->input('type', 'daily');

        $query = Sale::with('user', 'customer')->latest();

        if ($type === 'daily') {
            $query->whereDate('created_at', $date);
        } elseif ($type === 'monthly') {
            $query->whereMonth('created_at', Carbon::parse($date)->month)
                  ->whereYear('created_at', Carbon::parse($date)->year);
        }

        $sales = $query->get();

        $total_sales = $sales->sum('total_amount');
        $total_transactions = $sales->count();
        $cash_sales = $sales->where('payment_method', 'cash')->sum('total_amount');
        $credit_sales = $sales->where('payment_method', 'credit')->sum('total_amount');

        // 3. NEW: Top Selling Items Logic
        // We aggregate the SaleItems, sum the quantity, and order by highest sum
        $topItems = SaleItem::select('product_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(price * quantity) as total_revenue'))
            ->whereHas('sale', function($q) use ($date, $type) {
                // Apply the same date filters to the items
                if ($type === 'daily') {
                    $q->whereDate('created_at', $date);
                } elseif ($type === 'monthly') {
                    $q->whereMonth('created_at', Carbon::parse($date)->month)
                      ->whereYear('created_at', Carbon::parse($date)->year);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product') // Eager load product name
            ->take(10) // Get top 10
            ->get();

        return view('admin.reports.index', compact(
            'sales', 'total_sales', 'total_transactions', 
            'cash_sales', 'credit_sales', 'date', 'type',
            'topItems' // Pass the new variable
        ));
    }

    // --- ADD THIS NEW FUNCTION BELOW ---
    public function export(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $type = $request->input('type', 'daily');

        // 1. Fetch Data (Same logic as index to ensure what they see is what they export)
        $query = Sale::with('user', 'customer')->latest();

        if ($type === 'daily') {
            $query->whereDate('created_at', $date);
        } elseif ($type === 'monthly') {
            $query->whereMonth('created_at', Carbon::parse($date)->month)
                  ->whereYear('created_at', Carbon::parse($date)->year);
        }

        $sales = $query->get();

        // 2. Prepare CSV Download
        $filename = "sales_report_{$type}_{$date}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // 3. Create Stream Callback
        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');

            // Header Row
            fputcsv($file, ['Sale ID', 'Date/Time', 'Cashier', 'Customer', 'Payment Method', 'Total Amount', 'Amount Paid']);

            // Data Rows
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