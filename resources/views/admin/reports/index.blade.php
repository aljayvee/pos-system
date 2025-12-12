@extends('admin.layout')

{{-- Import Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@section('styles')
<style>
    /* HIDE PRINT HEADER BY DEFAULT */
    #print-header { display: none; }

    @media print {
        /* 1. RESET LAYOUT */
        @page { margin: 1cm; size: auto; }
        body { background-color: white !important; -webkit-print-color-adjust: exact; }
        
        /* 2. HIDE UI ELEMENTS */
        #sidebar-wrapper, nav.navbar, .btn, form, .breadcrumb, .badge, .card-header i {
            display: none !important;
        }

        /* 3. EXPAND CONTENT TO FULL PAGE */
        #page-content-wrapper { margin: 0 !important; width: 100% !important; padding: 0 !important; }
        .container-fluid { padding: 0 !important; max-width: 100% !important; }
        
        /* 4. TRANSFORM STATS CARDS (Ink Saver) */
        /* Turn colored cards into white boxes with black text for printing */
        .card {
            border: 1px solid #ddd !important;
            background-color: white !important;
            color: black !important;
            box-shadow: none !important;
            margin-bottom: 20px !important;
            break-inside: avoid; /* Prevent cutting card in half */
        }
        .card-body h3 { font-size: 18pt !important; font-weight: bold !important; color: black !important; }
        .card-body small { color: #555 !important; font-size: 10pt !important; }
        .card-header {
            background-color: #f8f9fa !important;
            color: black !important;
            border-bottom: 2px solid #000 !important;
            font-weight: bold;
        }

        /* 5. TABLE STYLING */
        .table { width: 100% !important; border-collapse: collapse !important; }
        .table th, .table td {
            border: 1px solid #000 !important;
            padding: 8px !important;
            font-size: 10pt !important;
            color: black !important;
        }
        .table thead th { background-color: #eee !important; color: black !important; }

        /* 6. SHOW PRINT HEADER */
        #print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid black;
        }
        
        /* 7. CHARTS (Resize for Paper) */
        canvas {
            max-width: 100% !important;
            max-height: 300px !important;
            margin: 0 auto !important;
            display: block;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">

{{-- NEW: Print Only Header --}}
    <div id="print-header">
        <h1>Sari-Sari Store Sales Report</h1>
        <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</p>
        <p>Report Period: 
            <strong>
                @if($type == 'daily') {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }}
                @elseif($type == 'monthly') {{ \Carbon\Carbon::parse($startDate)->format('F Y') }}
                @else {{ $startDate }} to {{ $endDate }}
                @endif
            </strong>
        </p>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Sales & Analytics</h2>
        <span class="badge bg-light text-dark border p-2">
            Date: {{ \Carbon\Carbon::now()->format('F d, Y') }}
        </span>
    </div>

    {{-- 1. FILTER TOOLBAR --}}
    <div class="card bg-white border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('reports.index') }}" method="GET" class="row g-3 align-items-end">
                
                {{-- Report Type --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Report Type</label>
                    <select name="type" id="report-type" class="form-select" onchange="toggleDateInputs()">
                        <option value="daily" {{ $type == 'daily' ? 'selected' : '' }}>Daily Report</option>
                        <option value="weekly" {{ $type == 'weekly' ? 'selected' : '' }}>Weekly Report</option>
                        <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>Monthly Report</option>
                        <option value="custom" {{ $type == 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                    </select>
                </div>

                {{-- Start Date / Main Date --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted" id="start-label">Select Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                </div>

                {{-- End Date (Only for Custom) --}}
                <div class="col-md-3" id="end-date-wrapper" style="display: none;">
                    <label class="form-label fw-bold small text-muted">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>

                {{-- Buttons --}}
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    
                    {{-- Export Button (Maintains filters) --}}
                    <a href="{{ route('reports.export', request()->all()) }}" class="btn btn-success w-100 fw-bold">
                        <i class="fas fa-file-csv me-1"></i> Export
                    </a>
                    {{-- Add this button next to the Export button --}}
                        <button type="button" onclick="window.print()" class="btn btn-secondary w-100 fw-bold">
                            <i class="fas fa-print me-1"></i> Print / PDF
                        </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DYNAMIC TITLE --}}
    <div class="mb-3">
        <h5 class="text-muted">
            Results for: 
            <span class="fw-bold text-dark">
                @if($type == 'daily') {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }}
                @elseif($type == 'monthly') {{ \Carbon\Carbon::parse($startDate)->format('F Y') }}
                @elseif($type == 'weekly') Week of {{ \Carbon\Carbon::parse($startDate)->startOfWeek()->format('M d') }} - {{ \Carbon\Carbon::parse($startDate)->endOfWeek()->format('M d, Y') }}
                @else {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                @endif
            </span>
        </h5>
    </div>

    {{-- 2. STATISTICS CARDS ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Total Revenue</small>
                            <h3 class="fw-bold mb-0">₱{{ number_format($total_sales, 2) }}</h3>
                        </div>
                        <i class="fas fa-coins fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Gross Profit (Sales - Cost)</small>
                            <h3 class="fw-bold mb-0">₱{{ number_format($gross_profit, 2) }}</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        @if($tithesEnabled == '1')
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Tithes (10%)</small>
                            <h3 class="fw-bold mb-0">₱{{ number_format($tithesAmount, 2) }}</h3>
                        </div>
                        <i class="fas fa-hand-holding-heart fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="col-xl-3 col-md-6">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Total Transactions</small>
                            <h3 class="fw-bold mb-0">{{ $total_transactions }}</h3>
                        </div>
                        <i class="fas fa-receipt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Credit Sales (Utang)</small>
                            <h3 class="fw-bold mb-0">₱{{ number_format($credit_sales, 2) }}</h3>
                        </div>
                        <i class="fas fa-user-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. CHARTS ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-chart-pie text-primary me-1"></i> Sales by Category
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-wallet text-success me-1"></i> Payment Method Distribution
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. ITEM PERFORMANCE ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-trophy me-1"></i> Top Selling Items (Fast Moving)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty Sold</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topItems as $item)
                            <tr>
                                <td class="fw-bold text-dark">{{ $item->product->name ?? 'Unknown Item' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success rounded-pill">{{ $item->total_qty }}</span>
                                </td>
                                <td class="text-end fw-bold">₱{{ number_format($item->total_revenue, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No sales data found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-hourglass-half me-1"></i> Slow Moving Items (No Sales)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-center">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slowMovingItems as $item)
                            <tr>
                                <td class="fw-bold">{{ $item->name }}</td>
                                <td class="small text-muted">{{ $item->category->name ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ $item->stock }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Everything is selling well!</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. TOP CUSTOMERS ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-users me-1"></i> Top Customers
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer Name</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-end">Total Spent</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $cust)
                            <tr>
                                <td class="fw-bold">{{ $cust->customer->name ?? 'Walk-in / Unknown' }}</td>
                                <td class="text-center">{{ $cust->trans_count }}</td>
                                <td class="text-end fw-bold text-primary">₱{{ number_format($cust->total_spent, 2) }}</td>
                                <td class="text-center">
                                    @if($cust->customer_id)
                                    <a href="{{ route('customers.show', $cust->customer_id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No customer data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 6. TRANSACTION HISTORY TABLE --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Transaction History</h5>
            <span class="badge bg-light text-dark">{{ $total_transactions }} Records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sale ID</th>
                            <th>Time</th>
                            <th>Cashier</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td class="fw-bold">#{{ $sale->id }}</td>
                            <td>{{ $sale->created_at->format('h:i A') }}</td>
                            <td><small>{{ $sale->user->name }}</small></td>
                            <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                            <td>
                                @if($sale->payment_method == 'cash') <span class="badge bg-success">Cash</span>
                                @elseif($sale->payment_method == 'credit') <span class="badge bg-danger">Credit</span>
                                @else <span class="badge bg-info text-dark">Digital</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">₱{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-sm btn-light border">
                                    <i class="fas fa-receipt text-secondary"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><br>
                                No transactions found for this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- 1. Category Chart ---
        const catData = @json($salesByCategory);
        const catLabels = catData.map(item => item.name);
        const catValues = catData.map(item => item.total_revenue);

        if (catLabels.length > 0) {
            new Chart(document.getElementById('categoryChart'), {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{
                        data: catValues,
                        backgroundColor: [
                            '#0d6efd', '#198754', '#ffc107', '#dc3545', 
                            '#6610f2', '#fd7e14', '#20c997', '#6c757d'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right' } }
                }
            });
        } else {
            document.getElementById('categoryChart').parentNode.innerHTML = "<div class='text-center py-5 text-muted'><i class='fas fa-chart-pie mb-2 fa-2x opacity-25'></i><br>No data available</div>";
        }

        // --- 2. Payment Method Chart ---
        const cash = {{ $cash_sales }};
        const digital = {{ $digital_sales }};
        const credit = {{ $credit_sales }};

        if ((cash + digital + credit) > 0) {
            new Chart(document.getElementById('paymentChart'), {
                type: 'pie',
                data: {
                    labels: ['Cash', 'Digital', 'Credit'],
                    datasets: [{
                        data: [cash, digital, credit],
                        backgroundColor: ['#198754', '#0dcaf0', '#dc3545'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right' } }
                }
            });
        } else {
            document.getElementById('paymentChart').parentNode.innerHTML = "<div class='text-center py-5 text-muted'><i class='fas fa-wallet mb-2 fa-2x opacity-25'></i><br>No sales data</div>";
        }
    });

    // Toggle Date Inputs based on Type
    function toggleDateInputs() {
        const type = document.getElementById('report-type').value;
        const endWrapper = document.getElementById('end-date-wrapper');
        const startLabel = document.getElementById('start-label');

        if (type === 'custom') {
            endWrapper.style.display = 'block';
            startLabel.innerText = 'Start Date';
        } else {
            endWrapper.style.display = 'none';
            if (type === 'monthly') startLabel.innerText = 'Select Month';
            else if (type === 'weekly') startLabel.innerText = 'Select Week';
            else startLabel.innerText = 'Select Date';
        }
    }
    window.onload = toggleDateInputs;
</script>
@endsection