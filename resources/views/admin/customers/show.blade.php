@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3 mb-3" style="top: 0;">
        <a href="{{ route('customers.index') }}" class="text-dark"><i class="fas fa-arrow-left fa-lg"></i></a>
        <h6 class="m-0 fw-bold text-dark">Customer Profile</h6>
        @if(auth()->user()->role !== 'auditor')
        <a href="#" data-bs-toggle="modal" data-bs-target="#editCustomerModal-{{ $customer->id }}" class="text-warning">
            <i class="fas fa-edit fa-lg"></i>
        </a>
        @else
        <div style="width: 24px;"></div>
        @endif
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <a href="{{ route('customers.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold mb-3 d-inline-block d-md-none">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 d-none d-md-flex" 
                     style="width: 60px; height: 60px; font-weight: bold; font-size: 1.5rem;">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="h3 fw-bold text-dark mb-1">{{ $customer->name }}</h2>
                    <p class="text-muted mb-0 small">
                        @if($customer->contact)
                            <i class="fas fa-phone-alt me-1 text-secondary opacity-75"></i> {{ $customer->contact }} 
                            <span class="mx-2 text-light-gray">|</span> 
                        @endif
                        <i class="fas fa-map-marker-alt me-1 text-secondary opacity-75"></i> {{ $customer->address ?? 'No Address Provided' }}
                    </p>
                </div>
            </div>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold d-none d-md-inline-block">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    {{-- MOBILE PROFILE SUMMARY --}}
    <div class="d-lg-none mb-4">
        <div class="text-center mb-4">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                 style="width: 70px; height: 70px; font-weight: bold; font-size: 2rem;">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <h4 class="fw-bold mb-0">{{ $customer->name }}</h4>
            <div class="text-muted small mt-1">
                {{ $customer->contact ?? 'No contact info' }}
            </div>
            @if($customer->address)
            <div class="text-muted small">
                <i class="fas fa-map-marker-alt me-1 opacity-50"></i> {{ $customer->address }}
            </div>
            @endif
        </div>

        <div class="row g-2 mb-2">
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                    <div class="card-body p-3 text-center">
                        <small class="text-secondary text-uppercase fw-bold" style="font-size: 0.65rem;">Current Debt</small>
                        <h5 class="{{ $currentDebt > 0 ? 'text-danger' : 'text-success' }} fw-bold mb-0 mt-1">
                            ₱{{ number_format($currentDebt, 2) }}
                        </h5>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                    <div class="card-body p-3 text-center">
                        <small class="text-secondary text-uppercase fw-bold" style="font-size: 0.65rem;">Points</small>
                        <h5 class="text-warning text-dark-emphasis fw-bold mb-0 mt-1">
                            {{ number_format($customer->points) }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-6">
                 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-body p-3 text-center">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Lifetime Spend</small>
                        <div class="fw-bold text-dark mt-1">₱{{ number_format($totalSpent, 0) }}</div>
                    </div>
                </div>
            </div>
             <div class="col-6">
                 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-body p-3 text-center">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Total Visits</small>
                        <div class="fw-bold text-dark mt-1">{{ number_format($totalVisits) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DESKTOP STATS CARDS --}}
    <div class="d-none d-lg-flex row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-4" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                <div class="card-body p-3 p-md-4">
                    <p class="text-white-50 small text-uppercase fw-bold mb-1">Lifetime Spend</p>
                    <h3 class="fw-bold mb-0">₱{{ number_format($totalSpent, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 bg-white">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small text-uppercase fw-bold mb-1">Total Visits</p>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalVisits) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                <div class="card-body p-3 p-md-4">
                    <p class="text-white-50 small text-uppercase fw-bold mb-1">Loyalty Points</p>
                    <h3 class="fw-bold mb-0">{{ number_format($customer->points) }} <small class="fs-6 opacity-75">pts</small></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 {{ $currentDebt > 0 ? 'bg-danger text-white' : 'bg-success text-white' }}" 
                 style="{{ $currentDebt > 0 ? 'background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);' : 'background: linear-gradient(135deg, #10b981 0%, #059669 100%);' }}">
                <div class="card-body p-3 p-md-4">
                    <p class="text-white-50 small text-uppercase fw-bold mb-1">Current Debt</p>
                    <h3 class="fw-bold mb-0">₱{{ number_format($currentDebt, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSACTION HISTORY --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-secondary"></i>Purchase History</h5>
        </div>
        
        {{-- Desktop Table --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Date & Time</th>
                            <th class="py-3">Reference / ID</th>
                            <th class="py-3">Payment Method</th>
                            <th class="text-end py-3">Total Amount</th>
                            <th class="py-3">Cashier</th>
                            <th class="text-center pe-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td class="ps-4 text-muted">
                                <div class="fw-bold text-dark">{{ $sale->created_at->format('M d, Y') }}</div>
                                <small>{{ $sale->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-3">#{{ $sale->id }}</span>
                                @if($sale->reference_number)
                                    <small class="text-muted ms-1 d-block mt-1">{{ $sale->reference_number }}</small>
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
                                <span class="badge {{ $badge }} border border-opacity-10 text-uppercase px-3 py-2 rounded-pill">{{ $sale->payment_method }}</span>
                            </td>
                            <td class="text-end fw-bold text-dark fs-6">₱{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="small text-muted">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width:24px; height:24px;">
                                        <i class="fas fa-user text-secondary" style="font-size: 0.7rem;"></i>
                                    </div>
                                    {{ $sale->user->name ?? 'System' }}
                                </div>
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('transactions.print', $sale->id) }}" target="_blank" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 fw-bold text-primary">
                                    <i class="fas fa-receipt me-1"></i> Receipt
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
                            <span class="badge bg-light text-dark border rounded-pill mb-1">Receipt #{{ $sale->id }}</span>
                            <div class="text-muted small">{{ $sale->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="text-end">
                            <h6 class="fw-bold text-dark mb-0">₱{{ number_format($sale->total_amount, 2) }}</h6>
                             <span class="badge {{ $sale->payment_method == 'credit' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} text-uppercase rounded-pill px-2" style="font-size: 0.65rem;">
                                {{ $sale->payment_method }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('transactions.print', $sale->id) }}" target="_blank" class="btn btn-light btn-sm w-100 rounded-pill fw-bold border text-secondary">
                        <i class="fas fa-receipt me-1"></i> View Receipt
                    </a>
                </li>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-receipt fa-3x mb-3 text-light-gray opacity-25"></i>
                    <p>No transactions found.</p>
                </div>
                @endforelse
            </ul>
        </div>
        
        @if($sales->hasPages())
        <div class="card-footer bg-white py-3 border-top-0 d-flex justify-content-center">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
    
    @include('admin.customers.partials.edit-modal', ['customer' => $customer])
</div>
@endsection