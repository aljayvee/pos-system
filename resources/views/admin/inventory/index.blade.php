@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h1>Inventory Management</h1>
        <div >
            <a href="{{ route('purchases.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Restock (Purchase)</a>
            <a href="{{ route('inventory.adjust') }}" class="btn btn-danger"><i class="fas fa-minus"></i> Record Wastage</a>
            <a href="{{ route('inventory.history') }}" class="btn btn-secondary"><i class="fas fa-history"></i> History</a>
            {{-- NEW BUTTON: RECORD WASTAGE --}}
            <a href="{{ route('inventory.adjust') }}" class="btn btn-warning me-1">
                <i class="fas fa-exclamation-triangle"></i> Record Wastage
            </a>
            <a href="{{ route('inventory.export') }}" class="btn btn-success me-1">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            

        </div>


    </div>

    {{-- INVENTORY SUMMARY CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <small class="text-white-50">Total Stock Count</small>
                    <h3 class="fw-bold mb-0">{{ number_format($totalItems) }} <small class="fs-6">units</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <small class="text-white-50">Total Inventory Cost (Capital)</small>
                    <h3 class="fw-bold mb-0">₱{{ number_format($totalCostValue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <small class="text-white-50">Total Sales Value</small>
                    <h3 class="fw-bold mb-0">₱{{ number_format($totalSalesValue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <small class="text-white-50">Potential Profit</small>
                    <h3 class="fw-bold mb-0">₱{{ number_format($potentialProfit, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-boxes me-1"></i>
            Current Stock Levels
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td>₱{{ number_format($product->price, 2) }}</td>
                        <td class="fw-bold {{ $product->stock <= 10 ? 'text-danger' : 'text-success' }}">
                            {{ $product->stock }}
                        </td>
                        <td>
                            @if($product->stock == 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->stock <= 10)
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @else
                                <span class="badge bg-success">Good</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection