@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="mb-0"><i class="fas fa-file-invoice-dollar text-danger"></i> Outstanding Credits</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('credits.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-download me-1"></i> Export List
            </a>
            <a href="{{ route('credits.logs') }}" class="btn btn-outline-dark btn-sm ms-1">
                <i class="fas fa-history me-1"></i> Payment Logs
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SUMMARY CARD --}}
    <div class="card bg-danger text-white mb-4 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 text-white-50">Total Collectibles (Utang)</h6>
                <h2 class="fw-bold mb-0">₱{{ number_format($totalReceivables, 2) }}</h2>
            </div>
            <i class="fas fa-hand-holding-usd fa-3x opacity-25"></i>
        </div>
    </div>

    {{-- SEARCH & FILTER TOOLBAR --}}
    <div class="card bg-light border-0 mb-3">
        <div class="card-body py-2">
            <form action="{{ route('credits.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search customer name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="filter" class="form-select">
                        <option value="all">All Unpaid</option>
                        <option value="overdue" {{ request('filter') == 'overdue' ? 'selected' : '' }}>Overdue Only</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-dark w-100">Go</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CREDITS TABLE --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Customer</th>
                            <th>Date Incurred</th>
                            <th>Total Amount</th>
                            <th>Paid So Far</th>
                            <th>Balance</th>
                            <th>Due Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credits as $credit)
                        <tr>
                            <td class="fw-bold">{{ $credit->customer->name ?? 'Unknown' }}</td>
                            <td>{{ $credit->created_at->format('M d, Y') }}</td>
                            <td>₱{{ number_format($credit->total_amount, 2) }}</td>
                            <td class="text-success">₱{{ number_format($credit->amount_paid, 2) }}</td>
                            <td class="text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</td>
                            <td>
                                @if($credit->due_date)
                                    <span class="{{ \Carbon\Carbon::parse($credit->due_date)->isPast() ? 'text-danger fw-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($credit->due_date)->format('M d') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- 1. PAY BUTTON --}}
                                @if(!$credit->is_paid)
                                    <button class="btn btn-sm btn-success text-white" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#payCreditModal-{{ $credit->credit_id }}">
                                        <i class="fas fa-hand-holding-usd me-1"></i> Pay
                                    </button>
                                @else
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Paid</span>
                                @endif

                                {{-- 2. HISTORY BUTTON --}}
                                <a href="{{ route('credits.history', $credit->credit_id) }}" class="btn btn-sm btn-secondary text-white ms-1" title="View History">
                                    <i class="fas fa-history"></i>
                                </a>

                                {{-- 3. PAYMENT MODAL --}}
                                <div class="modal fade" id="payCreditModal-{{ $credit->credit_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form action="{{ route('credits.pay', $credit->credit_id) }}" method="POST">
                                            @csrf
                                            <div class="modal-content text-start">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">Record Payment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-light border mb-3">
                                                        <small class="text-muted d-block">Customer:</small>
                                                        <strong>{{ $credit->customer->name ?? 'Unknown' }}</strong>
                                                        <hr class="my-2">
                                                        <div class="d-flex justify-content-between">
                                                            <span>Balance Due:</span>
                                                            <span class="text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Payment Amount</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">₱</span>
                                                            <input type="number" name="amount" class="form-control" 
                                                                   max="{{ $credit->remaining_balance }}" step="0.01" required 
                                                                   placeholder="0.00">
                                                        </div>
                                                        <div class="form-text">Cannot exceed remaining balance.</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Notes (Optional)</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid via Gcash"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success fw-bold">Confirm Payment</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                {{-- END MODAL --}}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i><br>
                                No outstanding credits found. Good job!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $credits->links() }}
        </div>
    </div>
</div>
@endsection