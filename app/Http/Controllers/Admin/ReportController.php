<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product; // Still used for simple inventory report
use App\Models\CustomerCredit; // Still used for simple credit report
use Carbon\Carbon;
use App\Services\Finance\AccountingService;
use App\Services\Analytics\ForecastingService;
use App\Services\Reports\ReportExportService;

class ReportController extends Controller
{
    protected $accountingService;
    protected $forecastingService;
    protected $exportService;

    public function __construct(
        AccountingService $accountingService,
        ForecastingService $forecastingService,
        ReportExportService $exportService
    ) {
        $this->accountingService = $accountingService;
        $this->forecastingService = $forecastingService;
        $this->exportService = $exportService;
    }

    // 1. Sales Report (Main Dashboard)
    public function index(Request $request)
    {
        $type = $request->input('type', 'daily');
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        // Multi-Store Logic (Controller Concern)
        $isMultiStore = \App\Models\Setting::where('key', 'enable_multi_store')->value('value') ?? '0';
        $activeStoreId = $this->getActiveStoreId();
        $targetStore = $request->input('store_filter', $activeStoreId);

        // Delegate to Service
        $data = $this->accountingService->getFinancialSummary(
            $activeStoreId,
            $type,
            $startDate,
            $endDate,
            $targetStore
        );

        // Add Controller-Specific Data needed for View
        $data['type'] = $type;
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        $data['date'] = $startDate;
        $data['stores'] = \App\Models\Store::all();
        $data['targetStore'] = $targetStore;
        $data['isMultiStore'] = $isMultiStore;

        return view('admin.reports.index', $data);
    }

    // 2. Inventory Report
    public function inventoryReport()
    {
        $storeId = $this->getActiveStoreId();

        // Simple enough to keep here for now
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

    // 4. Forecast Report (ENTERPRISE GRADE)
    public function forecast(Request $request)
    {
        $storeId = $this->getActiveStoreId();

        $data = $this->forecastingService->generateForecast($storeId);

        return view('admin.reports.forecast', $data);
    }

    // 5. EXPORT FUNCTION
    public function export(Request $request)
    {
        $reportType = $request->input('report_type', 'sales');
        $storeId = $this->getActiveStoreId();

        return $this->exportService->streamCsv($reportType, $request, $storeId);
    }

    public function vatReport(Request $request)
    {
        $storeId = $this->getActiveStoreId();
        $start = Carbon::parse($request->input('start_date', Carbon::now()->startOfMonth()));
        $end = Carbon::parse($request->input('end_date', Carbon::now()->endOfMonth()));

        $data = $this->accountingService->getVatSummary($storeId, $start, $end);

        return view('admin.reports.vat', array_merge($data, [
            'start' => $start,
            'end' => $end
        ]));
    }
}