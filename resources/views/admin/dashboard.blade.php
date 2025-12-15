@extends('admin.layout')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex align-items-center justify-content-between mb-4 mt-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Store Overview</h4>
            <small class="text-muted">Welcome back, here's what's happening today.</small>
        </div>
        </div>

    {{-- 1. MODERN VUE STATS GRID --}}
    <div class="row g-3 mb-4">
        
        <div class="col-12 col-sm-6 col-xl-3">
            <stats-card 
                title="Today's Sales" 
                value="₱{{ number_format($salesToday, 2) }}" 
                subtitle="{{ $transactionCountToday }} Transactions"
                icon="fas fa-cash-register"
                color="primary"
            ></stats-card>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <stats-card 
                title="Profit" 
                value="₱{{ number_format($profitToday, 2) }}" 
                subtitle="Net Income Today"
                icon="fas fa-coins"
                color="success"
            ></stats-card>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <stats-card 
                title="Monthly Revenue" 
                value="₱{{ number_format($salesMonth, 2) }}" 
                subtitle="Current Month Total"
                icon="fas fa-chart-line"
                color="info"
            ></stats-card>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <stats-card 
                title="Collectibles" 
                value="₱{{ number_format($totalCredits, 2) }}" 
                subtitle="Unpaid Customer Debts"
                icon="fas fa-file-invoice-dollar"
                color="warning"
            ></stats-card>
        </div>

        {{-- Critical Alert (Only shows if > 0) --}}
        @if($outOfStockItems > 0)
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 rounded-3" role="alert">
                <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
                <div>
                    <strong>Attention Needed:</strong> 
                    You have <span class="fw-bold text-decoration-underline">{{ $outOfStockItems }} items</span> totally out of stock.
                    <a href="{{ route('inventory.index') }}" class="alert-link ms-2">View Inventory</a>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- 2. CHART SECTION (Existing) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-area me-2 text-primary"></i>Sales Trend</h6>
                    <span class="badge bg-light text-secondary">Last 30 Days</span>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. DATA TABLES (Existing content styled) --}}
    <div class="row g-4">
        {{-- LOW STOCK --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-battery-quarter me-2 text-danger"></i>Low Stock Items</h6>
                </div>
                <div class="card-body p-0">
                    @if($lowStockItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Item</th>
                                        <th>Level</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockItems as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                                            <small class="text-muted">{{ $item->unit ?? 'Unit' }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold text-danger me-2">{{ $item->current_stock }}</span>
                                                <div class="progress flex-grow-1 bg-light" style="height: 6px; width: 50px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" 
                                                        style="width: {{ ($item->current_stock / ($item->reorder_point ?: 10)) * 100 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('inventory.adjust') }}?product_id={{ $item->id }}" class="btn btn-sm btn-light text-primary">
                                                <i class="fas fa-plus"></i> Restock
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50"></i>
                            <p class="mb-0 small">Inventory levels are healthy.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- EXPIRING ITEMS --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-hourglass-half me-2 text-warning"></i>Expiring Soon</h6>
                </div>
                <div class="card-body p-0">
                    @if(count($expiringItems) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small text-uppercase">
                                    <tr><th class="ps-4">Item</th><th class="text-center">Status</th><th class="text-end pe-4">Manage</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringItems as $item)
                                    <tr>
                                        <td class="ps-4"><span class="fw-semibold">{{ $item->name }}</span></td>
                                        <td class="text-center">
                                            @php $expiry = \Carbon\Carbon::parse($item->expiration_date); $isExpired = $expiry->isPast(); @endphp
                                            <span class="badge {{ $isExpired ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning' }} border {{ $isExpired ? 'border-danger' : 'border-warning' }}">
                                                {{ $expiry->format('M d') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('products.edit', $item->id) }}" class="btn btn-sm btn-light text-dark"><i class="fas fa-arrow-right"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-shield-alt fa-2x mb-2 text-success opacity-50"></i>
                            <p class="mb-0 small">No expiring items found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Sales Revenue',
                        data: @json($chartValues),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0d6efd',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [2, 4], color: '#f0f0f0' },
                            ticks: { callback: function(value) { return '₱' + value; }, font: { size: 11 } } 
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush