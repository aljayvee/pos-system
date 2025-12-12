@extends('admin.layout')

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">{{ $customer->name }}</h2>
            <p class="text-muted mb-0">
                <i class="fas fa-phone-alt me-1"></i> {{ $customer->contact ?? 'No Contact' }} | 
                <i class="fas fa-map-marker-alt me-1"></i> {{ $customer->address ?? 'No Address' }}
            </p>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100 shadow-sm">
                <div class="card-body">
                    <small class="opacity-75">Lifetime Spend</small>
                    <h3 class="fw-bold mb-0">₱{{ number_format($totalSpent, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Total Transactions</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalVisits) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100 shadow-sm border-0">
                <div class="card-body">
                    <small class="text-dark">Loyalty Points</small>
                    <h3 class="fw-bold mb-0">{{ number_format($customer->points) }} <small class="fs-6">pts</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card {{ $currentDebt > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} h-100 shadow-sm">
                <div class="card-body">
                    <small class="opacity-75">Current Debt (Utang)</small>
                    <h3 class="fw-bold mb-0">₱{{ number_format($currentDebt, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Purchase History Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-history text-muted me-2"></i> Purchase History</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Reference / ID</th>
                        <th>Payment Method</th>
                        <th class="text-end">Total</th>
                        <th>Cashier</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->created_at->format('M d, Y - h:i A') }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">#{{ $sale->id }}</span>
                            @if($sale->reference_number)
                                <br><small class="text-muted">{{ $sale->reference_number }}</small>
                            @endif
                        </td>
                        <td>
                            @if($sale->payment_method == 'cash')
                                <span class="badge bg-success">Cash</span>
                            @elseif($sale->payment_method == 'credit')
                                <span class="badge bg-danger">Credit</span>
                            @else
                                <span class="badge bg-info">Digital</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold">₱{{ number_format($sale->total_amount, 2) }}</td>
                        <td class="small text-muted">{{ $sale->user->name ?? 'System' }}</td>
                        <td class="text-center">
                            {{-- Admin Print Route --}}
                            <a href="{{ route('transactions.print', $sale->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-receipt"></i> Receipt
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No purchase history found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $sales->links() }}
        </div>
    </div>
</div>
@endsection