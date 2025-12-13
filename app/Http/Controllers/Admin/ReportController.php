<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\CustomerCredit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // 1. Sales Report (Main Dashboard)
    public function index(Request $request)
    {
        $type = $request->input('type', 'daily');
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        $storeId = $this->getActiveStoreId();

        // Query Sales
        $query = Sale::with('user', 'customer')
                     ->where('store_id', $storeId)
                     ->latest();

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

        // Calculate Totals
        $total_sales = $sales->sum('total_amount');
        $total_transactions = $sales->count();
        $cash_sales = $sales->where('payment_method', 'cash')->sum('total_amount');
        $credit_sales = $sales->where('payment_method', 'credit')->sum('total_amount');
        $digital_sales = $sales->where('payment_method', 'digital')->sum('total_amount');

        // Calculate Gross Profit
        $soldItems = SaleItem::whereIn('sale_id', $salesIds)->with('product')->get();
        $total_cost = 0;
        foreach ($soldItems as $item) {
            $itemCost = ($item->cost > 0) ? $item->cost : ($item->product->cost ?? 0);
            $total_cost += ($itemCost * $item->quantity);
        }
        $gross_profit = $total_sales - $total_cost;

        // Top Selling Items
        $topItems = SaleItem::select('product_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(price * quantity) as total_revenue'))
            ->whereIn('sale_id', $salesIds)
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->take(10)
            ->get();

        return view('admin.reports.index', compact(
            'sales', 'total_sales', 'total_transactions', 
            'cash_sales', 'credit_sales', 'digital_sales',
            'type', 'startDate', 'endDate', 
            'topItems', 'gross_profit'
        ));
    }

    // 2. Inventory Report
    public function inventoryReport()
    {
        $storeId = $this->getActiveStoreId();
        
        $inventory = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->select('products.*', 'inventories.stock as current_stock', 'inventories.reorder_point')
            ->get();

        $totalValue = $inventory->sum(fn($p) => $p->price * $p->current_stock);
        $totalCost = $inventory->sum(fn($p) => ($p->cost ?? 0) * $p->current_stock);

        return view('admin.reports.inventory', compact('inventory', 'totalValue', 'totalCost'));
    }

    // 3. Credit Report
    public function creditReport()
    {
        $credits = CustomerCredit::with('customer')->where('is_paid', false)->get();
        $totalReceivables = $credits->sum('remaining_balance');

        return view('admin.reports.credits', compact('credits', 'totalReceivables'));
    }

    // 4. Forecast Report (Optional)
    public function forecast()
    {
        // ... (Keep existing forecast logic if you have it, or leave empty for now) ...
        return view('admin.reports.forecast'); // Ensure this view exists or redirect
    }

    // 5. EXPORT FUNCTION
    public function export(Request $request)
    {
        $reportType = $request->input('report_type', 'sales');
        $filename = "{$reportType}_report_" . date('Y-m-d') . ".csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache"
        ];

        $callback = function() use ($reportType, $request) {
            $file = fopen('php://output', 'w');

            if ($reportType === 'sales') {
                $this->exportSalesLogic($file, $request);
            } elseif ($reportType === 'inventory') {
                $this->exportInventoryLogic($file);
            } elseif ($reportType === 'credits') {
                $this->exportCreditsLogic($file);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // --- HELPER FUNCTIONS FOR EXPORT ---

    private function exportSalesLogic($file, $request) {
        fputcsv($file, ['Sale ID', 'Date', 'Customer', 'Items', 'Total', 'Payment', 'Profit']);
        
        $storeId = $this->getActiveStoreId();
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        // For simplicity in export, we just grab "Daily" sales of the selected start_date
        // You can expand this to respect the 'type' filter if needed.
        
        $sales = Sale::with(['user', 'customer', 'saleItems.product'])
                     ->where('store_id', $storeId)
                     ->whereDate('created_at', $startDate)
                     ->get();

        foreach ($sales as $sale) {
            $cost = $sale->saleItems->sum(fn($item) => ($item->cost ?: $item->product->cost ?: 0) * $item->quantity);
            $profit = $sale->total_amount - $cost;
            $itemsList = $sale->saleItems->map(fn($i) => "{$i->product->name} ({$i->quantity})")->join(', ');

            fputcsv($file, [
                $sale->id,
                $sale->created_at->format('Y-m-d H:i'),
                $sale->customer->name ?? 'Walk-in',
                $itemsList,
                $sale->total_amount,
                ucfirst($sale->payment_method),
                number_format($profit, 2)
            ]);
        }
    }

    private function exportInventoryLogic($file) {
        fputcsv($file, ['Product', 'Barcode', 'Category', 'Stock', 'Cost', 'Price', 'Status']);
        
        $storeId = $this->getActiveStoreId();
        $inventory = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->with('category')
            ->select('products.*', 'inventories.stock as current_stock', 'inventories.reorder_point')
            ->get();

        foreach ($inventory as $item) {
            $status = ($item->current_stock <= $item->reorder_point) ? 'Low Stock' : 'Good';
            if ($item->current_stock == 0) $status = 'Out of Stock';

            fputcsv($file, [
                $item->name,
                $item->sku,
                $item->category->name ?? 'None',
                $item->current_stock,
                $item->cost,
                $item->price,
                $status
            ]);
        }
    }

    private function exportCreditsLogic($file) {
        fputcsv($file, ['Customer', 'Sale ID', 'Date', 'Due Date', 'Total Debt', 'Paid', 'Balance']);
        $credits = CustomerCredit::with('customer')->where('is_paid', false)->get();

        foreach ($credits as $credit) {
            fputcsv($file, [
                $credit->customer->name ?? 'Unknown',
                $credit->sale_id,
                $credit->created_at->format('Y-m-d'),
                $credit->due_date,
                $credit->total_amount,
                $credit->amount_paid,
                $credit->remaining_balance
            ]);
        }
    }
}