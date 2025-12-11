@extends('admin.layout')

@section('content')
<h2 class="mb-4">Admin Dashboard</h2>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase mb-1">Today's Sales</h6>
                    <h2 class="display-6 fw-bold">₱{{ number_format($todays_sales, 2) }}</h2>
                </div>
                <i class="fas fa-coins fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card {{ $low_stock_count > 0 ? 'bg-danger' : 'bg-success' }} text-white h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase mb-1">Low Stock Items</h6>
                    <h2 class="display-6 fw-bold">{{ $low_stock_count }}</h2>
                </div>
                <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card bg-dark text-white h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase mb-1">Date</h6>
                    <h4 class="fw-light">{{ now()->format('F d, Y') }}</h4>
                </div>
                <i class="far fa-calendar-alt fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-dark"><i class="fas fa-rocket text-primary me-2"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('products.create') }}" class="btn btn-outline-primary w-100 py-3 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <span>Add Product</span>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-success w-100 py-3 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-tags fa-2x mb-2"></i>
                            <span>Manage Categories</span>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-info w-100 py-3 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span>Add Customer</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-dark"><i class="fas fa-info-circle text-info me-2"></i> System Status</h5>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Database
                    <span class="badge bg-success rounded-pill">Connected</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    System Mode
                    <span class="badge bg-primary rounded-pill">Production</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Last Backup
                    <small class="text-muted">Never</small>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection