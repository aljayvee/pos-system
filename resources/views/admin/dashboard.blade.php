@extends('admin.layout')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Store Overview</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Today's Sales</h5>
                    <h2 class="fw-bold">₱{{ number_format($salesToday, 2) }}</h2>
                    <small>{{ $transactionCountToday }} Transactions</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-coins me-1"></i> Today's Profit</h5>
                    <h2 class="fw-bold">₱{{ number_format($profitToday, 2) }}</h2>
                    <small>Gross Income (Sales - Cost)</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Monthly Sales</h5>
                    <h2 class="fw-bold">₱{{ number_format($salesMonth, 2) }}</h2>
                    <small>This Month Revenue</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-dark mb-4">
                <div class="card-body">
                    <h5 class="card-title">Collectibles</h5>
                    <h2 class="fw-bold">₱{{ number_format($totalCredits, 2) }}</h2>
                    <small>Unpaid Customer Debt</small>
                </div>
            </div>
        </div>
    </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Out of Stock</h5>
                    <h2 class="fw-bold">{{ $outOfStockItems }} Items</h2>
                    <small>Need Restocking</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Critical Low Stock (Running Low)
                </div>
                <div class="card-body">
                    @if($lowStockItems->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Remaining Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockItems as $item)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $item->name }}</span>
                                        <br>
                                        <small class="text-muted">{{ $item->category->name ?? 'Uncategorized' }}</small>
                                    </td>
                                    <td>
                                        {{-- Visual Progress Bar --}}
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold text-danger me-2">{{ $item->stock }}</span>
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-danger" role="progressbar" 
                                                    style="width: {{ ($item->stock / ($item->reorder_point ?: 10)) * 100 }}%">
                                                </div>
                                            </div>
                                            <span class="small text-muted ms-2">/ {{ $item->reorder_point ?? 10 }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('inventory.adjust') }}?product_id={{ $item->id }}" class="btn btn-sm btn-outline-dark">
                                            <i class="fas fa-plus"></i> Restock
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                        <p class="mb-0">Inventory levels are healthy!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

<div class="col-xl-6">
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <i class="fas fa-stopwatch me-1"></i>
            Expiring Soon (Next 7 Days)
        </div>
        <div class="card-body">
            @if(count($expiringItems) > 0)
                <table class="table table-sm table-borderless">
                    <thead>
                        <tr class="text-muted small">
                            <th>Product</th>
                            <th class="text-center">Expiry Date</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiringItems as $item)
                        <tr class="align-middle border-bottom">
                            <td>
                                <div class="fw-bold">{{ $item->name }}</div>
                                <small class="text-muted">Stock: {{ $item->current_stock }}</small>
                            </td>
                            <td class="text-center">
                                @php
                                    $expiry = \Carbon\Carbon::parse($item->expiration_date);
                                    $isExpired = $expiry->isPast();
                                @endphp
                                <span class="fw-bold {{ $isExpired ? 'text-danger' : 'text-dark' }}">
                                    {{ $expiry->format('M d, Y') }}
                                </span>
                                <br>
                                <small class="{{ $isExpired ? 'text-danger' : 'text-muted' }}">
                                    {{ $isExpired ? 'Expired' : $expiry->diffForHumans() }}
                                </small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('products.edit', $item->id) }}" class="btn btn-sm btn-light text-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                    <p class="mb-0">No expiring items found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
    </div>

        </div> <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <i class="fas fa-chart-area me-1 text-primary"></i>
                    Sales Trend (Last 30 Days)
                </div>
                <div class="card-body">
                    <canvas id="salesChart" width="100%" height="30"></canvas>
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
                    labels: @json($chartLabels), // Data from Controller
                    datasets: [{
                        label: 'Daily Sales (₱)',
                        data: @json($chartValues), // Data from Controller
                        borderColor: '#0d6efd', // Bootstrap Primary Blue
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3 // Makes lines slightly curved
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value; // Add Peso sign
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>

@endsection