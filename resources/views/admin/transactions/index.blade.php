@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4">
    
    {{-- TITLE & SEARCH HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center py-4 px-3 px-md-0 gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Transactions</h1>
            <p class="text-muted small mb-0">History of sales and orders</p>
        </div>
        
        <form action="{{ route('transactions.index') }}" method="GET" class="position-relative" style="min-width: 300px;">
            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" name="search" class="form-control ps-5 rounded-pill border-0 shadow-sm" 
                   placeholder="Search ID, Customer..." value="{{ request('search') }}" style="background: #fff; font-size: 0.95rem;">
            @if(request('search'))
                <a href="{{ route('transactions.index') }}" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted">
                    <i class="fas fa-times-circle"></i>
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="px-3 px-md-0 mb-3">
            <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center">
                <i class="fas fa-check-circle me-2 fs-5"></i> {{ session('success') }} 
            </div>
        </div>
    @endif

    {{-- DESKTOP VIEW --}}
    <div class="d-none d-lg-block">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                    <thead class="bg-light">
                        <tr class="text-secondary small text-uppercase fw-bold" style="letter-spacing: 0.5px;">
                            <th class="ps-4 py-3">Reference</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Method</th>
                            <th class="text-end py-3">Amount</th>
                            <th class="text-end pe-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($transactions as $sale)
                        <tr style="cursor: pointer;" onclick="window.location='{{ route('transactions.show', $sale->id) }}'">
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">#{{ $sale->id }}</div>
                                @if($sale->reference_number) 
                                    <div class="small text-muted font-monospace">{{ $sale->reference_number }}</div> 
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ substr($sale->customer->name ?? 'W', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $sale->customer->name ?? 'Walk-in Customer' }}</div>
                                        <div class="small text-muted">{{ $sale->user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-secondary small">{{ $sale->created_at->format('M d, Y • h:i A') }}</td>
                            <td>
                                @php
                                    $badge = match($sale->payment_method) {
                                        'cash' => 'bg-success text-white',
                                        'credit' => 'bg-danger text-white',
                                        default => 'bg-info text-white',
                                    };
                                    $icon = match($sale->payment_method) {
                                        'cash' => 'fa-money-bill-wave',
                                        'credit' => 'fa-file-invoice-dollar',
                                        default => 'fa-credit-card',
                                    };
                                @endphp
                                <span class="badge {{ $badge }} fw-normal px-2 py-1 rounded-pill">
                                    <i class="fas {{ $icon }} me-1 small"></i> {{ ucfirst($sale->payment_method) }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark fs-6 font-monospace">₱{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-end pe-4">
                                <i class="fas fa-chevron-right text-muted small opacity-50"></i>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MOBILE VIEW --}}
    <div class="d-lg-none pb-5">
        @forelse($transactions as $sale)
        <a href="{{ route('transactions.show', $sale->id) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm mx-3 mb-3 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-3 p-2 me-3 text-center" style="min-width: 50px;">
                                <div class="fw-bold text-dark" style="font-size: 1.1rem; line-height: 1;">{{ $sale->created_at->format('d') }}</div>
                                <div class="small text-muted text-uppercase" style="font-size: 0.7rem;">{{ $sale->created_at->format('M') }}</div>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">{{ $sale->customer->name ?? 'Walk-in' }}</h6>
                                <div class="small text-muted">#{{ $sale->id }} • {{ $sale->created_at->format('h:i A') }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bolder text-dark" style="font-size: 1.1rem;">₱{{ number_format($sale->total_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light">
                        <span class="badge {{ $sale->payment_method == 'credit' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} rounded-pill px-3">
                            {{ ucfirst($sale->payment_method) }}
                        </span>
                        <small class="text-muted">By {{ $sale->user->name }} <i class="fas fa-chevron-right ms-1" style="font-size: 0.7rem;"></i></small>
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
            <p>No transactions found.</p>
        </div>
        @endforelse
    </div>

    @if($transactions->hasPages()) 
        <div class="px-3 pb-4">
            {{ $transactions->links() }}
        </div> 
    @endif
</div>
@endsection