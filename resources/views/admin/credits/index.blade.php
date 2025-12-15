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
                <i class="fas fa-history me-1"></i> Payment Logs
            </a>
            <a href="{{ route('credits.export') }}" class="btn btn-success shadow-sm flex-fill flex-md-grow-0">
                <i class="fas fa-download me-1"></i> Export
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
             <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SUMMARY CARD --}}
    <div class="row mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card bg-danger text-white shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="text-white-50 text-uppercase fw-bold small mb-1">Total Collectibles</p>
                        <h2 class="fw-bold mb-0">₱{{ number_format($totalReceivables, 2) }}</h2>
                    </div>
                    <i class="fas fa-hand-holding-usd fa-3x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('credits.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Search customer name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="sort" class="form-select text-secondary">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="filter" class="form-select text-secondary">
                        <option value="all">All Unpaid</option>
                        <option value="overdue" {{ request('filter') == 'overdue' ? 'selected' : '' }}>Overdue Only</option>
                    </select>
                </div>
                <div class="col-12 col-md-1">
                    <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    {{-- DESKTOP VIEW: TABLE --}}
    <div class="card shadow-sm border-0 d-none d-lg-block mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Customer</th>
                            <th class="py-3">Date Incurred</th>
                            <th class="text-end py-3">Total Amount</th>
                            <th class="text-end py-3">Paid</th>
                            <th class="text-end py-3">Balance</th>
                            <th class="py-3 ps-4">Due Date</th>
                            <th class="text-end pe-4 py-3">Action</th>
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
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                {{-- CORRECTED: Using credit_id --}}
                                <button class="btn btn-sm btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#payCreditModal-{{ $credit->credit_id }}">
                                    <i class="fas fa-money-bill-wave me-1"></i> Pay
                                </button>
                                <a href="{{ route('credits.history', $credit->credit_id) }}" class="btn btn-sm btn-outline-secondary shadow-sm ms-1" title="History">
                                    <i class="fas fa-list"></i>
                                </a>
                            </td>
                        </tr>
                        {{-- INCLUDE MODAL --}}
                        @include('admin.credits.partials.pay-modal', ['credit' => $credit])
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-25"></i>
                                <p class="mb-0">No outstanding credits found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($credits->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $credits->links() }}
        </div>
        @endif
    </div>

    {{-- MOBILE VIEW: CARDS --}}
    <div class="d-lg-none">
        <div class="row g-3">
            @forelse($credits as $credit)
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 border-start border-4 border-danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-dark mb-0">{{ $credit->customer->name ?? 'Unknown' }}</h5>
                            @if($credit->due_date && \Carbon\Carbon::parse($credit->due_date)->isPast())
                                <span class="badge bg-danger">Overdue</span>
                            @endif
                        </div>
                        <small class="text-muted d-block mb-3">
                            <i class="far fa-calendar-alt me-1"></i> {{ $credit->created_at->format('M d, Y') }}
                        </small>

                        <div class="d-flex justify-content-between align-items-center bg-light rounded p-2 mb-3">
                            <div class="text-center px-2">
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Total</small>
                                <div class="fw-bold text-dark">₱{{ number_format($credit->total_amount, 2) }}</div>
                            </div>
                            <div class="text-center px-2 border-start border-end">
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Paid</small>
                                <div class="fw-bold text-success">₱{{ number_format($credit->amount_paid, 2) }}</div>
                            </div>
                            <div class="text-center px-2">
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Balance</small>
                                <div class="fw-bold text-danger">₱{{ number_format($credit->remaining_balance, 2) }}</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-flex">
                            {{-- CORRECTED: Using credit_id --}}
                            <button class="btn btn-success flex-fill shadow-sm" data-bs-toggle="modal" data-bs-target="#payCreditModal-{{ $credit->credit_id }}">
                                <i class="fas fa-hand-holding-usd me-1"></i> Pay
                            </button>
                            <a href="{{ route('credits.history', $credit->credit_id) }}" class="btn btn-outline-secondary flex-fill shadow-sm">
                                <i class="fas fa-history"></i> Log
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Ensure modal is included for mobile loop too --}}
            @include('admin.credits.partials.pay-modal', ['credit' => $credit])
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-25"></i>
                <p>No credits found.</p>
            </div>
            @endforelse
        </div>
        <div class="mt-4">
             {{ $credits->links() }}
        </div>
    </div>
</div>
@endsection