@extends('admin.layout')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@section('content')
<style>
    /* MOBILE REFINEMENTS */
    @media (max-width: 767px) {
        .page-header { margin-top: 1rem !important; margin-bottom: 1rem !important; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; }
        
        /* Tighter Card Spacing */
        .card { margin-bottom: 1rem !important; border-radius: 12px !important; }
        .card-body { padding: 1rem !important; }
        
        /* Stats Typography */
        .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; }
        .stat-value { font-size: 1.25rem !important; margin-bottom: 0.25rem !important; }
        .stat-sub { font-size: 0.7rem; }

        /* Modern Mobile Tables */
        .mobile-table-card tr {
            display: flex;
            flex-direction: column;
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        .mobile-table-card td {
            display: block;
            width: 100%;
            padding: 2px 0 !important;
            border: none !important;
        }
        .mobile-table-card thead { display: none; } /* Hide headers on mobile */
    }
</style>

<div class="container-fluid px-3 px-md-4">
    <div class="page-header">
        <h1 class="mt-4">Store Overview</h1>
        <ol class="breadcrumb mb-4 bg-transparent p-0">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>

    {{-- STATS GRID --}}
    {{-- Changed to col-6 on mobile for 2-column "Phablet" layout --}}
    <div class="row g-3 mb-4">
        
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card bg-primary text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title stat-label">Today's Sales</h5>
                    <h2 class="fw-bold stat-value">₱{{ number_format($salesToday, 2) }}</h2>
                    <small class="stat-sub">{{ $transactionCountToday }} Transactions</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-6 col-xl-3">
            <div class="card bg-success text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title stat-label"><i class="fas fa-coins me-1"></i> Today's Profit</h5>
                    <h2 class="fw-bold stat-value">₱{{ number_format($profitToday, 2) }}</h2>
                    <small class="stat-sub">Gross Income</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-6 col-xl-3">
            <div class="card bg-info text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title stat-label">Monthly Sales</h5>
                    <h2 class="fw-bold stat-value">₱{{ number_format($salesMonth, 2) }}</h2>
                    <small class="stat-sub">This Month</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-6 col-xl-3">
            <div class="card bg-warning text-dark h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title stat-label">Collectibles</h5>
                    <h2 class="fw-bold stat-value">₱{{ number_format($totalCredits, 2) }}</h2>
                    <small class="stat-sub">Unpaid Debt</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card bg-danger text-white h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title stat-label">Out of Stock</h5>
                        <h2 class="fw-bold stat-value">{{ $outOfStockItems }} Items</h2>
                    </div>
                    <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLES ROW --}}
    <div class="row g-4">
        
        {{-- LOW STOCK --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom fw-bold text-danger py-3">
                    <i class="fas fa-exclamation-triangle me-1"></i> Critical Low Stock
                </div>
                <div class="card-body p-0">
                    @if($lowStockItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Item Name</th>
                                        <th>Stock Level</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockItems as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                                            <small class="text-muted">{{ $item->category->name ?? 'Uncategorized' }}</small>
                                        </td>
                                        <td style="min-width: 120px;">
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold text-danger me-2">{{ $item->stock }}</span>
                                                <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" 
                                                        style="width: {{ ($item->stock / ($item->reorder_point ?: 10)) * 100 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('inventory.adjust') }}?product_id={{ $item->id }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                                Restock
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                            <p class="mb-0 fw-medium">Inventory is healthy!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- EXPIRING SOON --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom fw-bold text-warning py-3">
                    <i class="fas fa-stopwatch me-1"></i> Expiring Soon (7 Days)
                </div>
                <div class="card-body p-0">
                    @if(count($expiringItems) > 0)
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr class="text-muted small text-uppercase">
                                        <th class="ps-3">Product</th>
                                        <th class="text-center">Expiry</th>
                                        <th class="text-end pe-3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringItems as $item)
                                    <tr class="border-bottom">
                                        <td class="ps-3">
                                            <div class="fw-bold">{{ $item->name }}</div>
                                            <small class="text-muted">Stock: {{ $item->current_stock }}</small>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $expiry = \Carbon\Carbon::parse($item->expiration_date);
                                                $isExpired = $expiry->isPast();
                                            @endphp
                                            <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                                                {{ $expiry->format('M d') }}
                                            </span>
                                            <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                                {{ $isExpired ? 'Expired' : $expiry->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('products.edit', $item->id) }}" class="btn btn-sm btn-light text-primary rounded-circle">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-check fa-3x mb-3 text-success opacity-50"></i>
                            <p class="mb-0 fw-medium">No expiring items found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- SALES CHART --}}
    <div class="row my-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Sales Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" width="100%" height="35"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Sales (₱)',
                        data: @json($chartValues),
                        borderColor: '#4f46e5', // Modern Indigo
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Vital for Mobile Chart sizing
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [2, 4], color: '#f0f0f0' },
                            ticks: { callback: function(value) { return '₱' + value; }, font: { size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 7 }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection