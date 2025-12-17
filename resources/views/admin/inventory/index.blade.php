@extends('admin.layout')

@section('content')
<div class="container-fluid px-14 py-14">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 mb-4 gap-3">
        <h1 class="h2 mb-0 text-gray-800">
            <i class="fas fa-warehouse text-primary me-2"></i>Inventory Management
        </h1>
        
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('purchases.create') }}" class="btn btn-primary shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-plus-circle me-1"></i> Restock
            </a>
            <a href="{{ route('inventory.adjust') }}" class="btn btn-warning shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-sliders-h me-1"></i> Adjust
            </a>
            <div class="btn-group shadow-sm flex-fill flex-md-grow-0">
                <a href="{{ route('inventory.history') }}" class="btn btn-secondary">
                    <i class="fas fa-history me-1"></i> History
                </a>
            </div>

            <div class="btn-group shadow-sm flex-fill flex-md-grow-0">
             <a href="{{ route('inventory.export') }}" class="btn btn-success">
                    <i class="fas fa-file-export me-1"></i> Export Data
                </a>
            </div>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase text-muted fw-bold small mb-1">Total Stock</p>
                            <h4 class="fw-bold mb-0 text-primary">{{ number_format($totalItems) }} <small class="fs-6 text-muted">units</small></h4>
                        </div>
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase text-muted fw-bold small mb-1">Total Capital</p>
                            <h4 class="fw-bold mb-0 text-danger">₱{{ number_format($totalCostValue, 2) }}</h4>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase text-muted fw-bold small mb-1">Sales Value</p>
                            <h4 class="fw-bold mb-0 text-success">₱{{ number_format($totalSalesValue, 2) }}</h4>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase text-muted fw-bold small mb-1">Potential Profit</p>
                            <h4 class="fw-bold mb-0 text-info">₱{{ number_format($potentialProfit, 2) }}</h4>
                        </div>
                        <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('inventory.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Search product name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-grid d-md-block">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MAIN CONTENT AREA --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-list me-2"></i>Current Stock Levels</h5>
            </div>
        </div>
        <div class="card-body p-0">
            
            {{-- 1. DESKTOP VIEW TABLE (Visible on Medium screens and up) --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover table-striped align-middle mb-0 text-nowrap">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Product Name</th>
                            <th>Category</th>
                            <th class="text-end">Cost</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $product->name }}</td>
                            <td><span class="badge bg-light text-secondary border">{{ $product->category->name ?? 'Uncategorized' }}</span></td>
                            <td class="text-end text-muted small">₱{{ number_format($product->cost ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">₱{{ number_format($product->price, 2) }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $product->stock <= 10 ? 'bg-danger' : 'bg-primary' }} px-3">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                @if($product->stock == 0)
                                    <span class="badge bg-danger-subtle text-danger border border-danger">Out of Stock</span>
                                @elseif($product->stock <= 10)
                                    <span class="badge bg-warning-subtle text-warning border border-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success">In Stock</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3"></i><br>
                                No products found matching your criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 2. MOBILE VIEW LIST (Visible on Small screens and down) --}}
            <div class="d-md-none">
                @forelse($products as $product)
                <div class="p-3 border-bottom position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $product->name }}</h6>
                            <span class="badge bg-light text-secondary border text-xs">{{ $product->category->name ?? 'Uncategorized' }}</span>
                        </div>
                        <div class="text-end">
                             @if($product->stock == 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger">Out</span>
                            @elseif($product->stock <= 10)
                                <span class="badge bg-warning-subtle text-warning border border-warning">Low</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success">In</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row g-0 mt-3 bg-light rounded p-2">
                        <div class="col-4 border-end text-center">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem;">Stock</small>
                            <span class="fw-bold {{ $product->stock <= 10 ? 'text-danger' : 'text-dark' }}">
                                {{ $product->stock }}
                            </span>
                        </div>
                        <div class="col-4 border-end text-center">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem;">Cost</small>
                            <span class="text-muted small">₱{{ number_format($product->cost ?? 0, 2) }}</span>
                        </div>
                        <div class="col-4 text-center">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem;">Price</small>
                            <span class="fw-bold text-primary">₱{{ number_format($product->price, 2) }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3 text-gray-300"></i><br>
                    No products found.
                </div>
                @endforelse
            </div>

        </div>
        @if($products->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center justify-content-md-end py-3">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection