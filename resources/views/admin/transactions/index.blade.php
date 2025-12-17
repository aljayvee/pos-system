@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-receipt text-primary me-2"></i>Transactions</h1>
        
        <form action="{{ route('transactions.index') }}" method="GET" class="d-flex gap-2 flex-fill flex-sm-grow-0" style="max-width: 400px;">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search ID or Ref..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
            @if(request('search'))
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        
        {{-- DESKTOP TABLE --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-4">ID / Ref</th>
                            <th>Date</th>
                            <th>Cashier</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th class="text-end">Total</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $sale)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark">#{{ $sale->id }}</span>
                                @if($sale->reference_number) <div class="small text-muted">{{ $sale->reference_number }}</div> @endif
                            </td>
                            <td class="text-muted">{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $sale->user->name }}</td>
                            <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
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
                            <td class="text-end pe-4">
                                <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-sm btn-outline-primary shadow-sm"><i class="fas fa-eye"></i> View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- === MOBILE NATIVE VIEW === --}}
        <div class="d-lg-none bg-light pt-2 pb-2">
            @forelse($transactions as $sale)
            <a href="{{ route('transactions.show', $sale->id) }}" class="text-decoration-none text-dark">
                <div class="card border-0 shadow-sm mx-3 mb-3" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">#{{ $sale->id }}</span>
                            <small class="text-muted">{{ $sale->created_at->format('M d, h:i A') }}</small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-primary">₱{{ number_format($sale->total_amount, 2) }}</h5>
                            @php
                                $badgeClass = match($sale->payment_method) {
                                    'cash' => 'bg-success-subtle text-success',
                                    'credit' => 'bg-danger-subtle text-danger',
                                    default => 'bg-info-subtle text-info',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill text-uppercase" style="font-size: 0.7rem;">
                                {{ $sale->payment_method }}
                            </span>
                        </div>
                        
                        <div class="d-flex align-items-center text-muted small border-top pt-2">
                            <i class="fas fa-user me-2 opacity-50"></i> {{ $sale->customer->name ?? 'Walk-in' }}
                            <span class="mx-2">•</span>
                            <i class="fas fa-user-tag me-2 opacity-50"></i> {{ $sale->user->name }}
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-5 text-muted">No transactions found.</div>
            @endforelse
        </div>

        @if($transactions->hasPages()) <div class="card-footer bg-white border-top-0 py-3">{{ $transactions->links() }}</div> @endif
    </div>
</div>
@endsection