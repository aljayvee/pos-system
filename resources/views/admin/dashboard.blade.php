@extends('admin.layout')

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
                    <h5 class="card-title">Monthly Sales</h5>
                    <h2 class="fw-bold">₱{{ number_format($salesMonth, 2) }}</h2>
                    <small>This Month</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-dark mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Receivables</h5>
                    <h2 class="fw-bold">₱{{ number_format($totalCredits, 2) }}</h2>
                    <small>Unpaid Credits</small>
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
</div>
@endsection