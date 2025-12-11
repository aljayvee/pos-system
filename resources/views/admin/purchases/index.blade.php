@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck-loading text-primary"></i> Stock In / Purchase History</h2>
        <a href="{{ route('purchases.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> New Stock In
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Items Count</th>
                        <th>Total Cost</th>
                        <th>Recorded At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr>
                        <td>#{{ $purchase->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}</td>
                        <td class="fw-bold">{{ $purchase->supplier->name ?? 'Unknown' }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $purchase->items->count() }} items</span>
                        </td>
                        <td class="text-success fw-bold">₱{{ number_format($purchase->total_cost, 2) }}</td>
                        <td class="text-muted small">{{ $purchase->created_at->format('M d, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No purchase records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection