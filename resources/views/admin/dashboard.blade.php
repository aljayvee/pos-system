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
                                    <td>{{ $item->name }}</td>
                                    <td class="fw-bold text-danger">{{ $item->stock }}</td>
                                    <td>
                                        <a href="{{ route('products.edit', $item->id) }}" class="btn btn-sm btn-outline-dark">Restock</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-success text-center m-3"><i class="fas fa-check-circle"></i> Inventory levels are healthy!</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    System Info
                </div>
                <div class="card-body">
                    <p>Welcome to the <strong>Sari-Sari Store Management System</strong>.</p>
                    <ul>
                        <li>Use <strong>Products</strong> to manage items and prices.</li>
                        <li>Use <strong>Inventory</strong> to check detailed stock levels.</li>
                        <li>Use <strong>Reports</strong> to view detailed daily/monthly sales breakdown.</li>
                    </ul>
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