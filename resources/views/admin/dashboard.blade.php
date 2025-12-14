@extends('admin.layout')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@section('content')
<style>
    /* --- MOBILE REFINEMENTS --- */
    @media (max-width: 767px) {
        .page-header { margin-top: 1rem !important; margin-bottom: 1rem !important; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; }
        
        .card { margin-bottom: 1rem !important; border-radius: 12px !important; }
        .card-body { padding: 1rem !important; }
        
        .stat-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; font-weight: 600; }
        .stat-value { font-size: 1.3rem !important; margin-bottom: 0.2rem !important; }
        .stat-sub { font-size: 0.7rem; opacity: 0.9; }

        /* Chart Height for Mobile */
        .chart-container { height: 250px !important; } 
    }

    /* --- DESKTOP CHART HEIGHT --- */
    .chart-container {
        position: relative;
        width: 100%;
        height: 350px; /* Taller on Desktop for visibility */
    }
</style>

<div class="container-fluid px-3 px-md-4">
    <div class="page-header">
        <h1 class="mt-4">Store Overview</h1>
        <ol class="breadcrumb mb-4 bg-transparent p-0">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>

    {{-- STATS GRID (2 Cols on Mobile, 4 Cols on Desktop) --}}
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
                    <h5 class="card-title stat-label"><i class="fas fa-coins me-1"></i> Profit</h5>
                    <h2 class="fw-bold stat-value">₱{{ number_format($profitToday, 2) }}</h2>
                    <small class="stat-sub">Today's Gross Income</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-6 col-xl-3">
            <div class="card bg-info text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title stat-label">Monthly Sales</h5>
                    <h2 class="fw-bold stat-value">₱{{ number_format($salesMonth, 2) }}</h2>
                    <small class="stat-sub">Current Month Revenue</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-6 col-xl-3">
            <div class="card bg-warning text-dark h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title stat-label">Collectibles</h5>
                    <h2 class="fw-bold stat-value">₱{{ number_format($totalCredits, 2) }}</h2>
                    <small class="stat-sub">Unpaid Customer Debt</small>
                </div>
            </div>
        </div>
        
        {{-- Full Width Card on Mobile for Critical Alert --}}
        <div class="col-12 col-md-12 col-xl-12" style="{{ $outOfStockItems > 0 ? '' : 'display:none' }}">
            <div class="card bg-danger text-white shadow-sm border-0">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <h6 class="fw-bold text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.9;">Attention Needed</h6>
                        <h3 class="fw-bold m-0">{{ $outOfStockItems }} Items Out of Stock</h3>
                    </div>
                    <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- CHART SECTION --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-area me-2"></i>Sales Trend (Last 30 Days)</h6>
                </div>
                <div class="card-body p-3">
                    {{-- WRAPPER FOR RESPONSIVE HEIGHT --}}
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLES ROW --}}
    <div class="row g-4">
        {{-- LOW STOCK (Fixed for Mobile & Desktop) --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom fw-bold text-danger py-3">
                    <i class="fas fa-exclamation-triangle me-1"></i> Running Low
                </div>
                <div class="card-body p-0">
                    @if($lowStockItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="bg-light text-secondary small">
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
                                            <small class="text-muted">{{ $item->unit ?? 'Unit' }}</small>
                                        </td>
                                        <td style="min-width: 130px;">
                                            <div class="d-flex align-items-center">
                                                {{-- FIX: Use current_stock --}}
                                                <span class="fw-bold text-danger me-2">{{ $item->current_stock }}</span>
                                                
                                                <div class="progress flex-grow-1 bg-secondary-subtle" style="height: 6px; width: 60px;">
                                                    {{-- FIX: Calculation using current_stock and reorder_point --}}
                                                    <div class="progress-bar bg-danger" role="progressbar" 
                                                        style="width: {{ ($item->current_stock / ($item->reorder_point ?: 10)) * 100 }}%">
                                                    </div>
                                                </div>
                                                <span class="small text-muted ms-2" style="font-size: 0.75rem">/{{ $item->reorder_point }}</span>
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
                            <i class="fas fa-check-circle fa-3x mb-2 text-success opacity-50"></i>
                            <p class="mb-0 fw-medium">Inventory is healthy!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Expiring Items --}}
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold text-warning py-3">
                    <i class="fas fa-stopwatch me-1"></i> Expiring Soon
                </div>
                <div class="card-body p-0">
                    @if(count($expiringItems) > 0)
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead class="bg-light text-secondary small">
                                    <tr><th class="ps-3">Item</th><th class="text-center">Expiry</th><th class="text-end pe-3">Edit</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringItems as $item)
                                    <tr class="border-bottom">
                                        <td class="ps-3"><span class="fw-bold">{{ $item->name }}</span></td>
                                        <td class="text-center">
                                            @php $expiry = \Carbon\Carbon::parse($item->expiration_date); $isExpired = $expiry->isPast(); @endphp
                                            <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $expiry->format('M d') }}</span>
                                            <div class="small text-muted" style="font-size:0.7rem">{{ $isExpired?'Expired':$expiry->diffForHumans() }}</div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('products.edit', $item->id) }}" class="btn btn-sm btn-light text-primary"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-check fa-3x mb-2 text-success opacity-50"></i>
                            <p>No expiring items</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('salesChart');
        
        // Gradient Fill Logic
        let gradient = null;
        if (ctx) {
            const canvas = ctx.getContext('2d');
            gradient = canvas.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)'); // Start Color (Indigo)
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)'); // End Color (Transparent)

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
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2,
                        pointRadius: 4, // Visible points
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4 // Smooth lines
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // CRITICAL: Allows Chart to fill fixed height container
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(30, 30, 45, 0.9)',
                            padding: 10,
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function(context) { return 'Sales: ₱' + context.parsed.y.toLocaleString(); }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [4, 4], color: '#e5e7eb' },
                            ticks: {
                                callback: function(value) { return '₱' + value; },
                                font: { size: 11, weight: 'bold' },
                                color: '#6b7280'
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 11 },
                                color: '#6b7280',
                                maxTicksLimit: 7 // Prevent clutter on mobile
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection