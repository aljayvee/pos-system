@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h1><i class="fas fa-boxes text-warning"></i> Inventory Report</h1>
        <div class="btn-group">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">Sales & Analytics</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-primary active">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-outline-primary">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-outline-primary">Forecast</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card bg-dark text-white mb-4">
                <div class="card-body">
                    <h5>Total Stock Value (Selling Price)</h5>
                    <h2>₱{{ number_format($totalValue, 2) }}</h2>
                    <small>Potential Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-secondary text-white mb-4">
                <div class="card-body">
                    <h5>Total Stock Cost (Capital)</h5>
                    <h2>₱{{ number_format($totalCost, 2) }}</h2>
                    <small>Investment</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-1"></i> Current Stock Levels</span>
            <a href="{{ route('reports.export', ['report_type' => 'inventory']) }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-csv"></i> Export Inventory
            </a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-center">Stock</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Price</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventory as $item)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $item->name }}</div>
                            <small class="text-muted">{{ $item->sku }}</small>
                        </td>
                        <td>{{ $item->category->name ?? '-' }}</td>
                        <td class="text-center fw-bold">{{ $item->current_stock }}</td>
                        <td class="text-end">₱{{ number_format($item->cost, 2) }}</td>
                        <td class="text-end">₱{{ number_format($item->price, 2) }}</td>
                        <td class="text-center">
                            @if($item->current_stock == 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($item->current_stock <= $item->reorder_point)
                                <span class="badge bg-warning text-dark">Low</span>
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