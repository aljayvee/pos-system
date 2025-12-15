@extends('admin.layout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-4">
    {{-- HEADER & NAV --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mt-4 mb-4 gap-3">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-chart-pie text-primary me-2"></i>Analytics</h1>
        
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-primary shadow-sm flex-fill flex-xl-grow-0">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Forecast</a>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3">
            <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-end">
                @if($isMultiStore == '1')
                <div class="col-12 col-md-3">
                    <label class="small fw-bold text-muted">Branch</label>
                    <select name="store_filter" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $targetStore == 'all' ? 'selected' : '' }}>-- All Branches --</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $targetStore == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-6 col-md-2">
                    <label class="small fw-bold text-muted">Period</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="daily" {{ $type == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ $type == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="small fw-bold text-muted">Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('reports.export', ['report_type' => 'sales', 'start_date' => $startDate]) }}" class="btn btn-success w-100">
                        <i class="fas fa-download me-1"></i> Export
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- METRICS GRID --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card bg-primary text-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <small class="text-white-50 text-uppercase fw-bold">Revenue</small>
                    <h3 class="fw-bold mb-0">₱{{ number_format($total_sales, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card bg-success text-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <small class="text-white-50 text-uppercase fw-bold">Gross Profit</small>
                    <h3 class="fw-bold mb-0">₱{{ number_format($gross_profit, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card bg-white h-100 shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-bold">Transactions</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($total_transactions) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card bg-info text-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <small class="text-white-50 text-uppercase fw-bold">Tithes</small>
                    <h3 class="fw-bold mb-0">₱{{ number_format($tithesAmount, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 1: CHARTS & TOP ITEMS --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold py-3">Sales by Category</div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 300px;">
                    @if($salesByCategory->count() > 0)
                        <canvas id="categoryChart"></canvas>
                    @else
                        <p class="text-muted small">No data available.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold py-3 text-success">
                    <i class="fas fa-trophy me-2"></i>Top Sellers
                </div>
                
                {{-- Desktop Table --}}
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small text-secondary">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end pe-4">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topItems as $item)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $item->product->name ?? 'Unknown' }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $item->total_qty }}</span></td>
                                    <td class="text-end pe-4 text-success fw-bold">₱{{ number_format($item->total_revenue, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">No sales yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile List --}}
                <div class="d-md-none">
                    <ul class="list-group list-group-flush">
                        @forelse($topItems as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div>
                                <div class="fw-bold">{{ $item->product->name ?? 'Unknown' }}</div>
                                <small class="text-muted">{{ $item->total_qty }} units sold</small>
                            </div>
                            <span class="text-success fw-bold">₱{{ number_format($item->total_revenue, 2) }}</span>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted py-3">No sales yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSACTION HISTORY --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 fw-bold">
            <i class="fas fa-receipt me-2"></i>Recent Transactions
        </div>
        
        {{-- Desktop Table --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th class="text-end">Total</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td class="ps-4 text-muted">#{{ $sale->id }}</td>
                            <td>{{ $sale->created_at->format('M d, h:i A') }}</td>
                            <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                            <td>
                                <span class="badge {{ $sale->payment_method == 'credit' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} text-uppercase">
                                    {{ $sale->payment_method }}
                                </span>
                            </td>
                            <td class="text-end fw-bold">₱{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Cards --}}
        <div class="d-lg-none">
            <div class="list-group list-group-flush">
                @forelse($sales as $sale)
                <div class="list-group-item p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-light text-dark border">#{{ $sale->id }}</span>
                            <span class="text-muted small ms-2">{{ $sale->created_at->format('M d, h:i A') }}</span>
                        </div>
                        <span class="fw-bold fs-5">₱{{ number_format($sale->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">{{ $sale->customer->name ?? 'Walk-in' }}</small>
                        <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-sm btn-light border">Details</a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">No transactions found.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

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
                        backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6610f2'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
                }
            });
        }
    });
</script>
@endsection