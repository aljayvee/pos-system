@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 mb-4 gap-3">
        <div>
            <h1 class="h2 mb-0 text-gray-800">{{ $customer->name }}</h1>
            <p class="text-muted mb-0 small">
                <i class="fas fa-phone-alt me-1"></i> {{ $customer->contact ?? 'No Contact' }} 
                <span class="mx-2">|</span> 
                <i class="fas fa-map-marker-alt me-1"></i> {{ $customer->address ?? 'No Address' }}
            </p>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm align-self-start align-self-md-center">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    {{-- STATS CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-primary text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <p class="text-white-50 small text-uppercase fw-bold mb-1">Lifetime Spend</p>
                    <h3 class="fw-bold mb-0">₱{{ number_format($totalSpent, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white h-100 shadow-sm border-0 border-start border-4 border-dark">
                <div class="card-body">
                    <p class="text-muted small text-uppercase fw-bold mb-1">Total Visits</p>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalVisits) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-warning text-dark h-100 shadow-sm border-0">
                <div class="card-body">
                    <p class="text-dark small text-uppercase fw-bold mb-1">Loyalty Points</p>
                    <h3 class="fw-bold mb-0">{{ number_format($customer->points) }} <small class="fs-6">pts</small></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card {{ $currentDebt > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} h-100 shadow-sm border-0">
                <div class="card-body">
                    <p class="text-white-50 small text-uppercase fw-bold mb-1">Current Debt</p>
                    <h3 class="fw-bold mb-0">₱{{ number_format($currentDebt, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSACTION HISTORY --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-secondary"><i class="fas fa-history me-2"></i>Purchase History</h5>
        </div>
        
        {{-- Desktop Table --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Date</th>
                            <th class="py-3">Reference / ID</th>
                            <th class="py-3">Method</th>
                            <th class="text-end py-3">Total</th>
                            <th class="py-3">Cashier</th>
                            <th class="text-center pe-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td class="ps-4 text-muted">{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">#{{ $sale->id }}</span>
                                @if($sale->reference_number)
                                    <small class="text-muted ms-1">{{ $sale->reference_number }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badge = match($sale->payment_method) {
                                        'cash' => 'bg-success-subtle text-success',
                                        'credit' => 'bg-danger-subtle text-danger',
                                        default => 'bg-info-subtle text-info',
                                    };
                                @endphp
                                <span class="badge {{ $badge }} border border-opacity-10 text-uppercase">{{ $sale->payment_method }}</span>
                            </td>
                            <td class="text-end fw-bold">₱{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="small text-muted">{{ $sale->user->name ?? 'System' }}</td>
                            <td class="text-center pe-4">
                                <a href="{{ route('transactions.print', $sale->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-receipt"></i>
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
        </div>

        {{-- Mobile List View --}}
        <div class="d-lg-none">
            <ul class="list-group list-group-flush">
                @forelse($sales as $sale)
                <li class="list-group-item p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-light text-dark border">#{{ $sale->id }}</span>
                            <span class="text-muted small ms-2">{{ $sale->created_at->format('M d, Y') }}</span>
                        </div>
                        <span class="fw-bold fs-5">₱{{ number_format($sale->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge {{ $sale->payment_method == 'credit' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} text-uppercase">
                            {{ $sale->payment_method }}
                        </span>
                        <a href="{{ route('transactions.print', $sale->id) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-receipt me-1"></i> Receipt
                        </a>
                    </div>
                </li>
                @empty
                <li class="list-group-item text-center py-4 text-muted">No history found.</li>
                @endforelse
            </ul>
        </div>
        
        @if($sales->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection