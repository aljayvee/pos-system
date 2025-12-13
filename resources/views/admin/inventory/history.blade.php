@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Stock Adjustment History</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">History</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Adjustment Logs
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Product</th>
                            <th>Adjusted By</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adj)
                        <tr>
                            <td>{{ $adj->created_at->format('M d, Y h:i A') }}</td>
                            <td class="fw-bold">{{ $adj->product->name ?? 'Unknown Item' }}</td>
                            <td>{{ $adj->user->name ?? 'System' }}</td>
                            <td>
                                <span class="badge bg-{{ $adj->type == 'add' ? 'success' : ($adj->type == 'subtract' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($adj->type) }}
                                </span>
                            </td>
                            <td class="{{ $adj->quantity > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }}
                            </td>
                            <td class="text-muted small">{{ $adj->remarks ?? 'No remarks' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No history found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection