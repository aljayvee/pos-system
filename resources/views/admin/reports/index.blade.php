@extends('admin.layout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h1><i class="fas fa-chart-pie text-primary"></i> Analytics Dashboard</h1>
        
        <div class="btn-group">
            <a href="{{ route('reports.index') }}" class="btn btn-primary active">Sales & Analytics</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-outline-primary">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-outline-primary">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-outline-primary">Forecast</a>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="card mb-4 bg-light border-0 shadow-sm">
        <div class="card-body py-3">
            <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-end">
                {{-- Store Filter (Multi-Store) --}}
                @if($isMultiStore == '1')
                <div class="col-md-3">
                    <label class="small fw-bold">Branch</label>
                    <select name="store_filter" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $targetStore == 'all' ? 'selected' : '' }}>-- Consolidated (All) --</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $targetStore == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2">
                    <label class="small fw-bold">Period</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="daily" {{ $type == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ $type == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Reference Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter"></i> Filter</button>
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ route('reports.export', ['report_type' => 'sales', 'start_date' => $startDate]) }}" class="btn btn-success w-100">
                        <i class="fas fa-file-csv"></i> Export
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- KEY METRICS --}}
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Revenue</div>
                            <h3 class="fw-bold">₱{{ number_format($total_sales, 2) }}</h3>
                        </div>
                        <i class="fas fa-coins fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Gross Profit</div>
                            <h3 class="fw-bold">₱{{ number_format($gross_profit, 2) }}</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-dark mb-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-dark-50">Transactions</div>
                            <h3 class="fw-bold">{{ $total_transactions }}</h3>
                        </div>
                        <i class="fas fa-receipt fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Tithes (10%)</div>
                            <h3 class="fw-bold">₱{{ number_format($tithesAmount, 2) }}</h3>
                        </div>
                        <i class="fas fa-hand-holding-heart fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ANALYTICS ROW 1: Charts & Top Items --}}
    <div class="row">
        {{-- Sales by Category (Chart) --}}
        <div class="col-xl-4">
            <div class="card mb-4 shadow-sm h-100">
                <div class="card-header fw-bold">
                    <i class="fas fa-chart-pie me-1"></i> Sales by Category
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    @if($salesByCategory->count() > 0)
                        <canvas id="categoryChart" style="max-height: 250px;"></canvas>
                    @else
                        <p class="text-muted">No sales data for this period.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Selling Products --}}
        <div class="col-xl-8">
            <div class="card mb-4 shadow-sm h-100">
                <div class="card-header fw-bold text-success">
                    <i class="fas fa-trophy me-1"></i> Top 10 Best Sellers
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th class="text-center">Qty Sold</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topItems as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Unknown Item' }}</td>
                                    <td class="text-center fw-bold">{{ $item->total_qty }}</td>
                                    <td class="text-end text-success">₱{{ number_format($item->total_revenue, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">No sales data found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ANALYTICS ROW 2: Customers & Slow Moving --}}
    <div class="row">
        {{-- Top Customers (FIXED UI) --}}
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm h-100">
                <div class="card-header fw-bold text-primary">
                    <i class="fas fa-users me-1"></i> Top 5 Customers
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>Customer Name</th>
                                    <th class="text-end">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $index => $cust)
                                <tr>
                                    {{-- Rank Number --}}
                                    <td class="text-center fw-bold text-secondary">{{ $index + 1 }}</td>
                                    
                                    {{-- Customer Details --}}
                                    <td>
                                        <div class="fw-bold text-dark">{{ $cust->customer->name ?? 'Walk-in' }}</div>
                                        <small class="text-muted">{{ $cust->trans_count }} Transactions</small>
                                    </td>
                                    
                                    {{-- Amount Badge --}}
                                    <td class="text-end">
                                        <span class="badge bg-primary rounded-pill px-3 py-2">
                                            ₱{{ number_format($cust->total_spent, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-user-slash fa-2x mb-2 opacity-25"></i><br>
                                        No customer data for this period.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Slow Moving Items --}}
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header fw-bold text-danger">
                    <i class="fas fa-hourglass-half me-1"></i> Slow Moving Items (Unsold in Period)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-end">Last Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($slowMovingItems as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td class="text-center">{{ $item->stock }}</td>
                                    <td class="text-end">₱{{ number_format($item->price, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-3 text-muted">All items are selling well!</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSACTION HISTORY --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <i class="fas fa-list me-1"></i> Transaction History
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Date & Time</th>
                            <th>Cashier</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th class="text-end">Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td class="fw-bold">#{{ $sale->id }}</td>
                            <td>{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                            <td><small>{{ $sale->user->name ?? 'System' }}</small></td>
                            <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                            <td>
                                @if($sale->payment_method == 'cash') <span class="badge bg-success">Cash</span>
                                @elseif($sale->payment_method == 'credit') <span class="badge bg-danger">Credit</span>
                                @else <span class="badge bg-info text-dark">Digital</span> @endif
                            </td>
                            <td class="text-end fw-bold">₱{{ number_format($sale->total_amount, 2) }}</td>
                            <td>
                                <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-sm btn-outline-dark">
                                    View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No transactions found for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- CHART JS SCRIPT --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('categoryChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($salesByCategory->pluck('name')) !!},
                    datasets: [{
                        data: {!! json_encode($salesByCategory->pluck('total_revenue')) !!},
                        backgroundColor: [
                            '#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6610f2'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    });
</script>
@endsection