@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 mb-4 gap-3">
        <h1 class="h2 mb-0 text-gray-800">
            <i class="fas fa-file-invoice-dollar text-danger me-2"></i>Outstanding Credits
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('credits.logs') }}" class="btn btn-outline-dark shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-history me-1"></i> Logs
            </a>
            <a href="{{ route('credits.export') }}" class="btn btn-success shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-download me-1"></i> Export
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TOTAL CARD --}}
    <div class="card bg-danger text-white shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body d-flex justify-content-between align-items-center p-4">
            <div>
                <p class="text-white-50 text-uppercase fw-bold small mb-1">Total Collectibles</p>
                <h2 class="fw-bold mb-0">₱{{ number_format($totalReceivables, 2) }}</h2>
            </div>
            <i class="fas fa-hand-holding-usd fa-3x opacity-25"></i>
        </div>
    </div>

    {{-- SEARCH --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('credits.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Search customer..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="sort" class="form-select"><option value="newest">Newest First</option></select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="filter" class="form-select"><option value="all">All Unpaid</option></select>
                </div>
                <div class="col-12 col-md-1"><button class="btn btn-dark w-100"><i class="fas fa-filter"></i></button></div>
            </form>
        </div>
    </div>

    {{-- DESKTOP VIEW --}}
    <div class="card shadow-sm border-0 d-none d-lg-block mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Customer</th>
                            <th class="py-3">Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th class="ps-4">Due</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credits as $credit)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $credit->customer->name ?? 'Unknown' }}</td>
                            <td class="text-muted">{{ $credit->created_at->format('M d, Y') }}</td>
                            <td class="text-end text-muted">₱{{ number_format($credit->total_amount, 2) }}</td>
                            <td class="text-end text-success">₱{{ number_format($credit->amount_paid, 2) }}</td>
                            <td class="text-end text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</td>
                            <td class="ps-4">
                                @if($credit->due_date)
                                    <span class="badge {{ \Carbon\Carbon::parse($credit->due_date)->isPast() ? 'bg-danger-subtle text-danger' : 'bg-light text-dark border' }}">
                                        {{ \Carbon\Carbon::parse($credit->due_date)->format('M d') }}
                                    </span>
                                @else <span class="text-muted small">-</span> @endif
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#payCreditModal-{{ $credit->credit_id }}">Pay</button>
                                <a href="{{ route('credits.history', $credit->credit_id) }}" class="btn btn-sm btn-outline-secondary shadow-sm"><i class="fas fa-list"></i></a>
                            </td>
                        </tr>
                        @include('admin.credits.partials.pay-modal', ['credit' => $credit])
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No credits found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($credits->hasPages()) <div class="card-footer bg-white border-top-0 py-3">{{ $credits->links() }}</div> @endif
    </div>

    {{-- === MOBILE NATIVE VIEW === --}}
    <div class="d-lg-none">
        @forelse($credits as $credit)
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold text-dark mb-0">{{ $credit->customer->name ?? 'Unknown' }}</h5>
                    @if($credit->due_date && \Carbon\Carbon::parse($credit->due_date)->isPast())
                        <span class="badge bg-danger rounded-pill">Overdue</span>
                    @else
                        <span class="badge bg-light text-secondary border rounded-pill">{{ $credit->created_at->format('M d') }}</span>
                    @endif
                </div>

                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-uppercase text-muted small fw-bold">Balance Due</span>
                        <span class="fs-4 fw-bold text-danger">₱{{ number_format($credit->remaining_balance, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small text-muted">
                        <span>Total: ₱{{ number_format($credit->total_amount, 2) }}</span>
                        <span>Paid: ₱{{ number_format($credit->amount_paid, 2) }}</span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-success flex-fill fw-bold py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#payCreditModal-{{ $credit->credit_id }}">
                        <i class="fas fa-wallet me-1"></i> Pay
                    </button>
                    <a href="{{ route('credits.history', $credit->credit_id) }}" class="btn btn-outline-secondary flex-fill fw-bold py-2 rounded-3">
                        <i class="fas fa-history me-1"></i> History
                    </a>
                </div>
            </div>
        </div>
        @include('admin.credits.partials.pay-modal', ['credit' => $credit])
        @empty
        <div class="text-center py-5 text-muted">No credits found.</div>
        @endforelse
        <div class="mt-4">{{ $credits->links() }}</div>
    </div>
</div>
@endsection