@extends('admin.layout')

@section('content')
<div class="container-fluid px-3 px-md-4 ">
    <div class="d-flex align-items-center justify-content-between mb-4 mt-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Store Overview</h4>
            <small class="text-muted">Welcome back <b>{{ Auth::user()->name }}</b>, here's what's happening today.</small>
        </div>
        </div>

    {{-- 1. MODERN VUE STATS GRID --}}
    <div class="row g-4 mb-5">
        
        {{-- ROW 1: CASH FLOW METRICS (TODAY) --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <stats-card 
                title="Realized Revenue" 
                value="₱{{ number_format($realizedSalesToday, 2) }}" 
                subtitle="Cash + Digital + Collections"
                icon="fas fa-coins"
                color="primary"
            ></stats-card>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <stats-card 
                title="Debt Collections" 
                value="₱{{ number_format($debtCollectionsToday, 2) }}" 
                subtitle="Collected Today"
                icon="fas fa-hand-holding-usd"
                color="info"
            ></stats-card>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <stats-card 
                title="Cash in Drawer" 
                value="₱{{ number_format($estCashInDrawer, 2) }}" 
                subtitle="Sales + Collections - Refunds"
                icon="fas fa-wallet"
                color="success text-white" 
                :gradient="true"
            ></stats-card>
        </div>

        {{-- ROW 2: PERFORMANCE & STATUS --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <stats-card 
                title="Profit (Today)" 
                value="₱{{ number_format($profitToday, 2) }}" 
                subtitle="Net Income"
                icon="fas fa-coins"
                color="success"
            ></stats-card>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <stats-card 
                title="Monthly Sales (Accrual)" 
                value="₱{{ number_format($salesMonth, 2) }}" 
                subtitle="Includes Unpaid Credits"
                icon="fas fa-chart-line"
                color="primary"
            ></stats-card>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <stats-card 
                title="Total Collectibles" 
                value="₱{{ number_format($totalCredits, 2) }}" 
                subtitle="Unpaid Customer Debts"
                icon="fas fa-file-invoice-dollar"
                color="warning"
            ></stats-card>
        </div>

        {{-- Critical Alert (Only shows if > 0) --}}
        @if($outOfStockItems > 0)
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 rounded-4 p-4" role="alert">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                    <i class="fas fa-exclamation-triangle fa-lg text-danger"></i>
                </div>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Attention Needed</h5>
                    <p class="mb-0">
                        You have <span class="fw-bold text-decoration-underline">{{ $outOfStockItems }} items</span> totally out of stock.
                        @if(auth()->user()->role !== 'auditor')
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-light text-danger fw-bold ms-2 rounded-pill px-3">Restock Now</a>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- 2. CHART SECTION (Existing) --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="m-0 fw-bold text-dark fs-5">Sales Trend</h6>
                        <small class="text-muted">Revenue performance over the last 30 days</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">Last 30 Days</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="chart-container" style="position: relative; height: 350px; width: 100%;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. PREMIUM DATA TABLES / MOBILE LISTS --}}
    <div class="row g-4">
        
        {{-- LOW STOCK --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 py-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-battery-quarter text-danger"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 fs-5 ls-tight">Low Stock</h6>
                            <small class="text-muted">Items below reorder point</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($lowStockItems->count() > 0)
                        
                        {{-- DESKTOP TABLE --}}
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small text-uppercase fw-bold">
                                    <tr>
                                        <th class="ps-4 py-3 border-0">Item</th>
                                        <th class="py-3 border-0">Stock Level</th>
                                        <th class="text-end pe-4 py-3 border-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockItems as $item)
                                    <tr>
                                        <td class="ps-4 border-bottom-0">
                                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                                            <small class="text-muted">{{ $item->unit ?? 'Unit' }}</small>
                                        </td>
                                        <td class="border-bottom-0">
                                            <div class="d-flex align-items-center" style="max-width: 150px;">
                                                <span class="fw-bold text-danger me-3" style="width: 25px;">{{ $item->current_stock }}</span>
                                                <div class="progress flex-grow-1 bg-light rounded-pill" style="height: 6px;">
                                                    <div class="progress-bar bg-danger rounded-pill shadow-sm" role="progressbar" 
                                                        style="width: {{ ($item->current_stock / ($item->reorder_point ?: 10)) * 100 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4 border-bottom-0">
                                            @if(auth()->user()->role !== 'auditor')
                                            <a href="{{ route('inventory.adjust') }}?product_id={{ $item->id }}" class="btn btn-sm btn-light text-primary rounded-pill px-3 fw-bold">
                                                Restock
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- MOBILE LIST VIEW --}}
                        <div class="d-block d-md-none">
                            <div class="list-group list-group-flush">
                                @foreach($lowStockItems as $item)
                                <div class="list-group-item p-3 border-light">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                                            <small class="text-muted">{{ $item->unit ?? 'Unit' }}</small>
                                        </div>
                                        @if(auth()->user()->role !== 'auditor')
                                        <a href="{{ route('inventory.adjust') }}?product_id={{ $item->id }}" class="btn btn-xs btn-light text-primary rounded-pill px-3">
                                            Restock
                                        </a>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center mt-1">
                                         <span class="fw-bold text-danger me-2 small">{{ $item->current_stock }} left</span>
                                         <div class="progress flex-grow-1 bg-light rounded-pill" style="height: 4px;">
                                            <div class="progress-bar bg-danger rounded-pill" role="progressbar" 
                                                style="width: {{ ($item->current_stock / ($item->reorder_point ?: 10)) * 100 }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                    @else
                        <div class="text-center py-5 text-muted">
                            <div class="mb-3">
                                <i class="fas fa-check-circle fa-3x text-success opacity-25"></i>
                            </div>
                            <p class="mb-0 fw-medium">Healthy Inventory</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- EXPIRING ITEMS --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 py-4 px-4">
                     <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-hourglass-half text-warning"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 fs-5 ls-tight">Expiring Soon</h6>
                            <small class="text-muted">Items expiring within 7 days</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($expiringItems) > 0)
                        
                        {{-- DESKTOP TABLE --}}
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small text-uppercase fw-bold">
                                    <tr><th class="ps-4 py-3 border-0">Item</th><th class="text-center py-3 border-0">Expiry</th><th class="text-end pe-4 py-3 border-0">Manage</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringItems as $item)
                                    <tr>
                                        <td class="ps-4 border-bottom-0"><span class="fw-semibold text-dark">{{ $item->name }}</span></td>
                                        <td class="text-center border-bottom-0">
                                            @php $expiry = \Carbon\Carbon::parse($item->expiration_date); $isExpired = $expiry->isPast(); @endphp
                                            <span class="badge {{ $isExpired ? 'bg-danger text-white' : 'bg-warning bg-opacity-10 text-warning' }} px-3 py-2 rounded-pill border-0 shadow-sm">
                                                {{ $expiry->format('M d, Y') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4 border-bottom-0">
                                            <a href="{{ route('products.edit', $item->id) }}" class="btn btn-sm btn-light text-dark rounded-circle hover-scale" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"><i class="fas fa-arrow-right"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- MOBILE LIST VIEW --}}
                        <div class="d-block d-md-none">
                            <div class="list-group list-group-flush">
                                @foreach($expiringItems as $item)
                                @php $expiry = \Carbon\Carbon::parse($item->expiration_date); $isExpired = $expiry->isPast(); @endphp
                                <a href="{{ route('products.edit', $item->id) }}" class="list-group-item list-group-item-action p-3 border-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                                            <small class="{{ $isExpired ? 'text-danger fw-bold' : 'text-warning fw-bold' }}">
                                                {{ $isExpired ? 'Expired' : 'Expires' }} {{ $expiry->format('M d') }}
                                            </small>
                                        </div>
                                        <i class="fas fa-chevron-right text-muted small"></i>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>

                    @else
                        <div class="text-center py-5 text-muted">
                             <div class="mb-3">
                                <i class="fas fa-shield-alt fa-3x text-success opacity-25"></i>
                            </div>
                            <p class="mb-0 fw-medium">No expiring items</p>
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