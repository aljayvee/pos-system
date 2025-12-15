@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mt-4 mb-4 gap-3">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-boxes text-warning me-2"></i>Inventory Report</h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-primary shadow-sm flex-fill flex-xl-grow-0">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Forecast</a>
        </div>
    </div>

    {{-- METRICS --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card bg-dark text-white shadow-sm border-0">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50 text-uppercase fw-bold">Stock Value (SRP)</small>
                        <h2 class="fw-bold mb-0">₱{{ number_format($totalValue, 2) }}</h2>
                    </div>
                    <i class="fas fa-tags fa-2x opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card bg-secondary text-white shadow-sm border-0">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-white-50 text-uppercase fw-bold">Total Cost (Capital)</small>
                        <h2 class="fw-bold mb-0">₱{{ number_format($totalCost, 2) }}</h2>
                    </div>
                    <i class="fas fa-wallet fa-2x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- STOCK LEVEL TABLE --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary">Stock Levels</h5>
            <a href="{{ route('reports.export', ['report_type' => 'inventory']) }}" class="btn btn-sm btn-success shadow-sm">
                <i class="fas fa-download me-1"></i> CSV
            </a>
        </div>

        {{-- Desktop View --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-secondary">
                        <tr>
                            <th class="ps-4">Product</th>
                            <th>Category</th>
                            <th class="text-center">Stock</th>
                            <th class="text-end">Cost</th>
                            <th class="text-end">Price</th>
                            <th class="text-center pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventory as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $item->name }}</div>
                                <small class="text-muted">{{ $item->sku }}</small>
                            </td>
                            <td><span class="badge bg-light text-secondary border">{{ $item->category->name ?? '-' }}</span></td>
                            <td class="text-center fw-bold">{{ $item->current_stock }}</td>
                            <td class="text-end text-muted">₱{{ number_format($item->cost, 2) }}</td>
                            <td class="text-end fw-bold">₱{{ number_format($item->price, 2) }}</td>
                            <td class="text-center pe-4">
                                @if($item->current_stock == 0) <span class="badge bg-danger">Out of Stock</span>
                                @elseif($item->current_stock <= $item->reorder_point) <span class="badge bg-warning text-dark">Low</span>
                                @else <span class="badge bg-success">Good</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile View --}}
        <div class="d-lg-none">
            <div class="list-group list-group-flush">
                @foreach($inventory as $item)
                <div class="list-group-item p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold text-dark">{{ $item->name }}</div>
                        @if($item->current_stock == 0) <span class="badge bg-danger">Empty</span>
                        @elseif($item->current_stock <= $item->reorder_point) <span class="badge bg-warning text-dark">Low</span>
                        @else <span class="badge bg-success">Good</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-light rounded p-2">
                        <div>
                            <small class="text-uppercase text-muted" style="font-size:0.65rem">Stock</small>
                            <div class="fw-bold">{{ $item->current_stock }}</div>
                        </div>
                        <div class="text-end">
                            <small class="text-uppercase text-muted" style="font-size:0.65rem">Price</small>
                            <div class="fw-bold text-primary">₱{{ number_format($item->price, 2) }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection