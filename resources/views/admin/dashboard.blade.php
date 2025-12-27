@extends('admin.layout')

@section('content')
<div class="container-fluid px-3 px-md-4 ">
    <div class="d-flex align-items-center justify-content-between mb-4 mt-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 tracking-tight">Store Overview</h4>
            <small class="text-secondary fw-medium">Welcome back <b class="text-primary">{{ Auth::user()->name }}</b>, here's what's happening today.</small>
        </div>
    </div>

    {{-- 0. MOBILE QUICK ACTIONS (NATIVE APP FEEL) --}}
    <div class="d-md-none mb-4">
        <h6 class="fw-bold text-secondary text-uppercase small mb-3 ls-tight opacity-75">Quick Actions</h6>
        <div class="d-flex justify-content-between px-2">
            
            <a href="/cashier/pos" class="text-decoration-none text-center">
                <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center shadow-lg mb-2 hover-scale transition-all" 
                     style="width: 60px; height: 60px; font-size: 1.5rem;">
                     <i class="fa-solid fa-cash-register" style="color: #ffffff;"></i>
                </div>
                <span class="d-block small fw-bold text-dark" style="font-size: 0.75rem;">POS</span>
            </a>

            <a href="{{ route('products.create') }}" class="text-decoration-none text-center">
                <div class="rounded-circle bg-white text-success border border-success border-opacity-25 d-flex align-items-center justify-content-center shadow-sm mb-2 hover-scale transition-all" 
                     style="width: 60px; height: 60px; font-size: 1.4rem;">
                     <i class="fa-solid fa-plus text-success"></i>
                </div>
                <span class="d-block small fw-bold text-dark" style="font-size: 0.75rem;">Add Item</span>
            </a>

            <a href="{{ route('purchases.create') }}" class="text-decoration-none text-center">
                <div class="rounded-circle bg-white text-info border border-info border-opacity-25 d-flex align-items-center justify-content-center shadow-sm mb-2 hover-scale transition-all" 
                     style="width: 60px; height: 60px; font-size: 1.4rem;">
                     <i class="fa-solid fa-truck-loading text-info"></i>
                </div>
                <span class="d-block small fw-bold text-dark" style="font-size: 0.75rem;">Stock In</span>
            </a>

            <a href="{{ route('transactions.index') }}" class="text-decoration-none text-center">
                <div class="rounded-circle bg-white text-secondary border border-secondary border-opacity-25 d-flex align-items-center justify-content-center shadow-sm mb-2 hover-scale transition-all" 
                     style="width: 60px; height: 60px; font-size: 1.4rem;">
                     <i class="fa-solid fa-history text-secondary"></i>
                </div>
                <span class="d-block small fw-bold text-dark" style="font-size: 0.75rem;">History</span>
            </a>

        </div>
    </div>

    {{-- 1. STATS SECTION --}}
    
    {{-- ALERT (Mobile & Desktop) --}}
    @if($outOfStockItems > 0)
    <div class="mb-4">
        <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 rounded-4 p-4 glass-panel position-relative overflow-hidden" role="alert">
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-danger opacity-10 pe-none"></div>
            <div class="rounded-circle bg-white text-danger p-3 me-3 shadow-sm position-relative z-1">
                <i class="fas fa-exclamation-triangle fa-lg"></i>
            </div>
            <div class="position-relative z-1">
                <h5 class="alert-heading fw-bold mb-1">Attention Needed</h5>
                <p class="mb-0">
                    You have <span class="fw-bold text-decoration-underline">{{ $outOfStockItems }} items</span> out of stock.
                    @if(auth()->user()->role !== 'auditor')
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-light text-danger fw-bold ms-2 rounded-pill px-3 shadow-sm hover-scale">Restock Now</a>
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- DESKTOP GRID (Hidden on Mobile) --}}
    <div class="row g-4 mb-5 d-none d-md-flex">
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
                subtitle="Expected Cash on Hand"
                icon="fas fa-wallet"
                color="success" 
            ></stats-card>
        </div>

        {{-- ROW 2: PERFORMANCE & STATUS --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <stats-card 
                title="Profit (Today)" 
                value="₱{{ number_format($profitToday, 2) }}" 
                subtitle="Net Income"
                icon="fas fa-chart-line"
                color="success"
            ></stats-card>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <stats-card 
                title="Monthly Sales (Accrual)" 
                value="₱{{ number_format($salesMonth, 2) }}" 
                subtitle="Includes Unpaid Credits"
                icon="fas fa-calendar-check"
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
    </div>

    {{-- MOBILE ACCORDION (Visible on Mobile Only) --}}
    <div class="d-md-none mb-5">
        <div class="accordion accordion-flush bg-transparent gap-3 d-flex flex-column" id="mobileStatsAccordion">
            
            {{-- DRAWER 1: CASH FLOW --}}
            <div class="accordion-item border-0 rounded-4 shadow-sm overflow-hidden mb-0 glass-panel">
                <h2 class="accordion-header" id="headingCash">
                    <button class="accordion-button collapsed bg-transparent py-4 px-4 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCash" aria-expanded="false" aria-controls="collapseCash">
                        <div class="d-flex align-items-center w-100 me-3">
                             <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="fas fa-wallet text-primary" style="font-size: 1.2rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-0">Cash Position</h6>
                                <small class="text-muted">In Drawer: <span class="fw-bold text-dark">₱{{ number_format($estCashInDrawer, 2) }}</span></small>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapseCash" class="accordion-collapse collapse" aria-labelledby="headingCash" data-bs-parent="#mobileStatsAccordion">
                    <div class="accordion-body bg-light bg-opacity-50 p-3 pt-0">
                        <div class="d-flex flex-column gap-3 pt-3">
                            <stats-card 
                                title="Realized Revenue" 
                                value="₱{{ number_format($realizedSalesToday, 2) }}" 
                                subtitle="Cash + Digital + Collections"
                                icon="fas fa-coins"
                                color="primary"
                            ></stats-card>
                            <stats-card 
                                title="Debt Collections" 
                                value="₱{{ number_format($debtCollectionsToday, 2) }}" 
                                subtitle="Collected Today"
                                icon="fas fa-hand-holding-usd"
                                color="info"
                            ></stats-card>
                             <stats-card 
                                title="Cash in Drawer" 
                                value="₱{{ number_format($estCashInDrawer, 2) }}" 
                                subtitle="Expected Cash"
                                icon="fas fa-wallet"
                                color="success" 
                            ></stats-card>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DRAWER 2: PERFORMANCE --}}
            <div class="accordion-item border-0 rounded-4 shadow-sm overflow-hidden glass-panel">
                <h2 class="accordion-header" id="headingPerf">
                    <button class="accordion-button collapsed bg-transparent py-4 px-4 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePerf" aria-expanded="false" aria-controls="collapsePerf">
                        <div class="d-flex align-items-center w-100 me-3">
                             <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="fas fa-chart-line text-success" style="font-size: 1.2rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-0">Performance</h6>
                                <small class="text-muted">Net Profit: <span class="fw-bold text-success">₱{{ number_format($profitToday, 2) }}</span></small>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapsePerf" class="accordion-collapse collapse" aria-labelledby="headingPerf" data-bs-parent="#mobileStatsAccordion">
                    <div class="accordion-body bg-light bg-opacity-50 p-3 pt-0">
                        <div class="d-flex flex-column gap-3 pt-3">
                             <stats-card 
                                title="Profit (Today)" 
                                value="₱{{ number_format($profitToday, 2) }}" 
                                subtitle="Net Income"
                                icon="fas fa-coins"
                                color="success"
                            ></stats-card>
                            <stats-card 
                                title="Monthly Sales" 
                                value="₱{{ number_format($salesMonth, 2) }}" 
                                subtitle="Accrual Basis"
                                icon="fas fa-chart-line"
                                color="primary"
                            ></stats-card>
                            <stats-card 
                                title="Total Collectibles" 
                                value="₱{{ number_format($totalCredits, 2) }}" 
                                subtitle="Unpaid Customer Debts"
                                icon="fas fa-file-invoice-dollar"
                                color="warning"
                            ></stats-card>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- 2. CHART SECTION (Existing) --}}
    <div class="row mb-5">
        <div class="col-12 px-0 px-md-3">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden glass-panel">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="m-0 fw-bold text-dark fs-5 tracking-tight">Sales Trend</h6>
                        <small class="text-muted">Revenue performance over the last 30 days</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill d-none d-sm-inline-block border border-primary border-opacity-10">Last 30 Days</span>
                </div>
                <div class="card-body px-2 px-md-4 pb-4">
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
            <div class="card shadow-lg border-0 h-100 rounded-4 overflow-hidden glass-panel">
                <div class="card-header bg-transparent border-bottom-0 py-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-battery-quarter text-danger fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 fs-5 ls-tight">Low Stock</h6>
                            <small class="text-muted opacity-75">Items below reorder point</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($lowStockItems->count() > 0)
                        
                        {{-- DESKTOP TABLE --}}
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-50 text-secondary small text-uppercase fw-bold">
                                    <tr>
                                        <th class="ps-4 py-3 border-0 rounded-start-pill">Item</th>
                                        <th class="py-3 border-0">Stock Level</th>
                                        <th class="text-end pe-4 py-3 border-0 rounded-end-pill">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockItems as $item)
                                    <tr>
                                        <td class="ps-4 border-bottom border-light">
                                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                                            <small class="text-muted">{{ $item->unit ?? 'Unit' }}</small>
                                        </td>
                                        <td class="border-bottom border-light">
                                            <div class="d-flex align-items-center" style="max-width: 150px;">
                                                <span class="fw-bold text-danger me-3" style="width: 25px;">{{ $item->current_stock }}</span>
                                                <div class="progress flex-grow-1 bg-secondary bg-opacity-10 rounded-pill" style="height: 6px;">
                                                    <div class="progress-bar bg-danger rounded-pill shadow-sm" role="progressbar" 
                                                        style="width: {{ ($item->current_stock / ($item->reorder_point ?: 10)) * 100 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4 border-bottom border-light">
                                            @if(auth()->user()->role !== 'auditor')
                                            <a href="{{ route('inventory.adjust') }}?product_id={{ $item->id }}" class="btn btn-sm btn-light text-primary rounded-pill px-3 fw-bold shadow-sm hover-scale border-0">
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
                                <div class="list-group-item p-3 border-light bg-transparent">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                                            <small class="text-muted">{{ $item->unit ?? 'Unit' }}</small>
                                        </div>
                                        @if(auth()->user()->role !== 'auditor')
                                        <a href="{{ route('inventory.adjust') }}?product_id={{ $item->id }}" class="btn btn-xs btn-light text-primary rounded-pill px-3 shadow-sm border-0">
                                            Restock
                                        </a>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center mt-1">
                                         <span class="fw-bold text-danger me-2 small">{{ $item->current_stock }} left</span>
                                         <div class="progress flex-grow-1 bg-secondary bg-opacity-10 rounded-pill" style="height: 4px;">
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
            <div class="card shadow-lg border-0 h-100 rounded-4 overflow-hidden glass-panel">
                <div class="card-header bg-transparent border-bottom-0 py-4 px-4">
                     <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-hourglass-half text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 fs-5 ls-tight">Expiring Soon</h6>
                            <small class="text-muted opacity-75">Items expiring within 7 days</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($expiringItems) > 0)
                        
                        {{-- DESKTOP TABLE --}}
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-50 text-secondary small text-uppercase fw-bold">
                                    <tr><th class="ps-4 py-3 border-0 rounded-start-pill">Item</th><th class="text-center py-3 border-0">Expiry</th><th class="text-end pe-4 py-3 border-0 rounded-end-pill">Manage</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringItems as $item)
                                    <tr>
                                        <td class="ps-4 border-bottom border-light"><span class="fw-semibold text-dark">{{ $item->name }}</span></td>
                                        <td class="text-center border-bottom border-light">
                                            @php $expiry = \Carbon\Carbon::parse($item->expiration_date); $isExpired = $expiry->isPast(); @endphp
                                            <span class="badge {{ $isExpired ? 'bg-danger text-white' : 'bg-warning bg-opacity-10 text-warning' }} px-3 py-2 rounded-pill border-0 shadow-sm">
                                                {{ $expiry->format('M d, Y') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4 border-bottom border-light">
                                            <a href="{{ route('products.edit', $item->id) }}" class="btn btn-sm btn-light text-dark rounded-circle hover-scale shadow-sm border-0" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"><i class="fas fa-arrow-right"></i></a>
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
                                <a href="{{ route('products.edit', $item->id) }}" class="list-group-item list-group-item-action p-3 border-light bg-transparent">
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

@push('styles')
<style>
    /* Accordion Premium Tweaks */
    .accordion-button:not(.collapsed) {
        background-color: transparent !important;
        box-shadow: none !important;
        color: #0d6efd;
    }
    .accordion-button:focus {
        box-shadow: none !important;
    }
    
    /* Custom Gradient Text */
    .bg-gradient-primary { background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            // Gradient Fill
            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Sales Revenue',
                        data: @json($chartValues),
                        borderColor: '#4f46e5',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#4f46e5',
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#1f2937',
                            bodyColor: '#4b5563',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return ' ₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [4, 4], color: '#f3f4f6', drawBorder: false },
                            ticks: { 
                                callback: function(value) { return '₱' + value; }, 
                                font: { size: 11, family: "'Inter', sans-serif" },
                                color: '#9ca3af'
                            } 
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { 
                                font: { size: 11, family: "'Inter', sans-serif" },
                                color: '#9ca3af'
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                }
            });
        }
    });
</script>
@endpush