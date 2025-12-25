@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3 mb-3" style="top: 0;">
        <div style="width: 40px;"></div>
        <h6 class="m-0 fw-bold text-dark">Credits</h6>
        <div class="d-flex gap-3">
             <a href="{{ route('credits.logs') }}" class="text-dark"><i class="fas fa-history"></i></a>
             <a href="{{ route('credits.export') }}" class="text-success"><i class="fas fa-file-export"></i></a>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-file-invoice-dollar text-danger me-2"></i>Outstanding Credits
            </h4>
            <p class="text-muted small mb-0">Manage unpaid balances and collection history.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('credits.logs') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold flex-fill flex-md-grow-0">
                <i class="fas fa-history me-1 text-secondary"></i> Logs
            </a>
            <a href="{{ route('credits.export') }}" class="btn btn-success shadow-sm rounded-pill fw-bold px-4 flex-fill flex-md-grow-0">
                <i class="fas fa-file-export me-1"></i> Export Data
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }} 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TOTAL CARD --}}
    <div class="card border-0 mb-4 rounded-4 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color: white;">
        <div class="card-body d-flex justify-content-between align-items-center p-4">
            <div>
                <p class="text-white-50 text-uppercase fw-bold small mb-1">Total Collectibles</p>
                <h2 class="fw-bold mb-0">₱{{ number_format($totalReceivables, 2) }}</h2>
                <small class="text-white-50">Across all outstanding accounts</small>
            </div>
            <div class="bg-white bg-opacity-10 rounded-circle p-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-hand-holding-usd fa-2x"></i>
            </div>
        </div>
    </div>

    {{-- SEARCH --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body p-3">
            <form action="{{ route('credits.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 py-2" 
                               placeholder="Search customer name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="sort" class="form-select bg-light border-0 py-2 text-secondary fw-bold" style="cursor: pointer;">
                        <option value="newest">Newest First</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="filter" class="form-select bg-light border-0 py-2 text-secondary fw-bold" style="cursor: pointer;">
                        <option value="all">All Unpaid</option>
                    </select>
                </div>
                <div class="col-12 col-md-1">
                    <button class="btn btn-dark w-100 rounded-pill fw-bold py-2"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    {{-- DESKTOP VIEW --}}
    <div class="card shadow-sm border-0 d-none d-lg-block mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Accounts List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Customer Info</th>
                            <th class="py-3">Created Date</th>
                            <th class="text-end py-3">Total Credit</th>
                            <th class="text-end py-3">Amount Paid</th>
                            <th class="text-end py-3">Balance Due</th>
                            <th class="ps-4 py-3">Due Status</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credits as $credit)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $credit->customer->name ?? 'Unknown' }}</span>
                                <small class="text-muted d-block">ID: #{{ $credit->customer_id }}</small>
                            </td>
                            <td class="text-muted">{{ $credit->created_at->format('M d, Y') }}</td>
                            <td class="text-end text-muted">₱{{ number_format($credit->total_amount, 2) }}</td>
                            <td class="text-end text-success">₱{{ number_format($credit->amount_paid, 2) }}</td>
                            <td class="text-end text-danger fw-bold fs-6">₱{{ number_format($credit->remaining_balance, 2) }}</td>
                            <td class="ps-4">
                                @if($credit->due_date)
                                    @if(\Carbon\Carbon::parse($credit->due_date)->isPast())
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill"><i class="fas fa-exclamation-circle me-1"></i>Overdue</span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-pill">{{ \Carbon\Carbon::parse($credit->due_date)->format('M d') }}</span>
                                    @endif
                                @else 
                                    <span class="text-muted small">-</span> 
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded-pill">
                                    @if(auth()->user()->role !== 'auditor')
                                    <button class="btn btn-sm btn-success fw-bold px-3" data-bs-toggle="modal" data-bs-target="#payCreditModal-{{ $credit->credit_id }}">
                                        <i class="fas fa-wallet me-1"></i> Pay
                                    </button>
                                    @endif
                                    <a href="{{ route('credits.history', $credit->credit_id) }}" class="btn btn-sm btn-light border-start text-secondary px-3" title="View History">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @include('admin.credits.partials.pay-modal', ['credit' => $credit])
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No outstanding credits found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($credits->hasPages()) 
        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
            {{ $credits->links() }}
        </div> 
        @endif
    </div>

    {{-- === MOBILE NATIVE VIEW (List) === --}}
    <div class="d-lg-none card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
        <ul class="list-group list-group-flush">
            @forelse($credits as $credit)
            <li class="list-group-item p-3 border-bottom-0 hover-bg-light" data-bs-toggle="modal" data-bs-target="#creditActionSheet-{{ $credit->credit_id }}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-user text-secondary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">{{ $credit->customer->name ?? 'Unknown' }}</h6>
                             @if($credit->due_date && \Carbon\Carbon::parse($credit->due_date)->isPast())
                                <span class="badge bg-danger rounded-pill shadow-sm" style="font-size: 0.65rem;">Overdue</span>
                            @else
                                <small class="text-muted" style="font-size: 0.75rem;">Due: {{ \Carbon\Carbon::parse($credit->due_date)->format('M d') }}</small>
                            @endif
                        </div>
                    </div>
                     <div class="text-end">
                         <h6 class="fw-bold text-danger mb-0">₱{{ number_format($credit->remaining_balance, 2) }}</h6>
                         <small class="text-success" style="font-size: 0.75rem;">Paid: ₱{{ number_format($credit->amount_paid, 2) }}</small>
                     </div>
                </div>
            </li>

            {{-- Mobile Action Sheet for this Item --}}
            <div class="modal fade" id="creditActionSheet-{{ $credit->credit_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable fixed-bottom m-0" style="max-width: 100%;">
                    <div class="modal-content rounded-top-4 border-0 shadow-lg">
                        <div class="modal-header border-bottom-0 pb-0 justify-content-center">
                            <div class="bg-secondary bg-opacity-25 rounded-pill" style="width: 40px; height: 5px;"></div>
                        </div>
                        <div class="modal-body pt-4 pb-4">
                            <h5 class="fw-bold text-center mb-2">{{ $credit->customer->name ?? 'Unknown' }}</h5>
                            <p class="text-center text-muted mb-4 small">Total Balance: <span class="text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</span></p>
                            
                            <div class="d-grid gap-3">
                                @if(auth()->user()->role !== 'auditor')
                                <button class="btn btn-success p-3 rounded-4 d-flex align-items-center justify-content-center gap-2 fw-bold" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#payCreditModal-{{ $credit->credit_id }}">
                                    <i class="fas fa-wallet fa-lg"></i> Record Payment
                                </button>
                                @endif
                                <a href="{{ route('credits.history', $credit->credit_id) }}" class="btn btn-light shadow-sm p-3 rounded-4 d-flex align-items-center justify-content-center gap-2 fw-bold text-dark">
                                    <i class="fas fa-history fa-lg"></i> View History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Keep the pay modal working for both desktop and mobile flow --}}
            @include('admin.credits.partials.pay-modal', ['credit' => $credit])

            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-check-circle fa-3x mb-3 text-light-gray opacity-25"></i>
                <p>No outstanding credits found.</p>
            </div>
            @endforelse
        </ul>
         @if($credits->hasPages()) 
        <div class="p-3 border-top d-flex justify-content-center">
            {{ $credits->links() }}
        </div> 
        @endif
    </div>
</div>
@endsection