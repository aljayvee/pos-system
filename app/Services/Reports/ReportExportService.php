<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Product;
use App\Models\CustomerCredit;
use App\Models\Setting;

class ReportExportService
{
    public function streamCsv(string $reportType, Request $request, int $storeId)
    {
        $filename = "{$reportType}_report_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache"
        ];

        $callback = function () use ($reportType, $request, $storeId) {
            $file = fopen('php://output', 'w');

            if ($reportType === 'sales') {
                $this->exportSales($file, $request, $storeId);
            } elseif ($reportType === 'inventory') {
                $this->exportInventory($file, $storeId);
            } elseif ($reportType === 'credits') {
                $this->exportCredits($file);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportSales($file, Request $request, int $storeId)
    {
        fputcsv($file, ['Invoice/Ref Number', 'Date', 'Customer', 'Gross Sales', 'Vatable Sales', 'VAT Amount', 'VAT Exempt', 'Net Sales']);

        $startDate = $request->input('start_date', Carbon::today()->toDateString());

        $taxRate = (float) (Setting::where('key', 'tax_rate')->value('value') ?? 12);
        $taxType = Setting::where('key', 'tax_type')->value('value') ?? 'inclusive';

        $sales = Sale::with(['user', 'customer'])
            ->where('store_id', $storeId)
            ->whereDate('created_at', $startDate)
            ->get();

        foreach ($sales as $sale) {
            $total = $sale->total_amount;
            $vatable = 0;
            $vatAmount = 0;
            $vatExempt = 0;

            if ($taxType === 'non_vat') {
                $vatExempt = $total;
            } else {
                $vatable = $total / (1 + ($taxRate / 100));
                $vatAmount = $total - $vatable;
            }

            fputcsv($file, [
                $sale->invoice_number ?? $sale->id,
                $sale->created_at->format('Y-m-d H:i'),
                $sale->customer->name ?? 'Walk-in',
                number_format($total, 2, '.', ''),
                number_format($vatable, 2, '.', ''),
                number_format($vatAmount, 2, '.', ''),
                number_format($vatExempt, 2, '.', ''),
                number_format($total, 2, '.', '')
            ]);
        }
    }

    private function exportInventory($file, int $storeId)
    {
        fputcsv($file, ['Product', 'Barcode', 'Category', 'Stock', 'Cost', 'Price', 'Status']);

        $inventory = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->with('category')
            ->select('products.*', 'inventories.stock as current_stock', 'inventories.reorder_point')
            ->get();

        foreach ($inventory as $item) {
            $status = ($item->current_stock <= $item->reorder_point) ? 'Low Stock' : 'Good';
            if ($item->current_stock == 0)
                $status = 'Out of Stock';

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

    private function exportCredits($file)
    {
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
