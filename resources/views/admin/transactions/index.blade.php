@extends('admin.layout')

@section('content')
    <div class="container-fluid px-0 px-md-4 py-0 py-md-4">

        {{-- MOBILE HEADER --}}
        <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
            <div class="px-3 py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-receipt text-primary me-2"></i>Transactions</h6>
            </div>
        </div>

        {{-- DESKTOP HEADER --}}
        <div
            class="d-none d-lg-flex flex-column flex-md-row justify-content-between align-items-md-center py-4 px-3 px-md-0 gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1 text-dark">{{ request('archived') ? 'Archived Transactions' : 'Transactions' }}
                </h1>
                <p class="text-muted small mb-0">
                    {{ request('archived') ? 'History of voided/archived sales' : 'History of sales and orders' }}
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                @if(request('archived'))
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary rounded-pill fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Check Active
                    </a>
                @else
                    <a href="{{ route('transactions.index', ['archived' => 1]) }}"
                        class="btn btn-light text-muted border rounded-pill fw-bold">
                        <i class="fas fa-archive me-1"></i> Archived
                    </a>
                @endif

                <form action="{{ route('transactions.index') }}" method="GET" class="position-relative"
                    style="min-width: 300px;">
                    @if(request('archived'))
                        <input type="hidden" name="archived" value="1">
                    @endif
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" name="search" class="form-control ps-5 rounded-pill border-0 shadow-sm"
                        placeholder="Search..." value="{{ request('search') }}"
                        style="background: #fff; font-size: 0.95rem;">
                    @if(request('search'))
                        <a href="{{ route('transactions.index', ['archived' => request('archived')]) }}"
                            class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted">
                            <i class="fas fa-times-circle"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="px-3 px-md-0 pt-3 pt-md-0">
            {{-- MOBILE SEARCH (Visible only on mobile) --}}
            <div class="d-lg-none mb-3">
                <form action="{{ route('transactions.index') }}" method="GET" class="position-relative">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" name="search" class="form-control ps-5 rounded-4 border-0 shadow-sm py-3"
                        placeholder="Search transactions..." value="{{ request('search') }}">
                </form>
            </div>

            @if(session('success'))
                <div class="mb-3">
                    <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center">
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
                                    <th class="py-3">Status</th>
                                    <th class="text-end py-3">Amount</th>
                                    <th class="text-end pe-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($transactions as $sale)
                                    <tr style="cursor: pointer;"
                                        onclick="window.location='{{ route('transactions.show', $sale->id) }}'">
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">#{{ $sale->id }}</div>
                                            @if($sale->reference_number)
                                                <div class="small text-muted font-monospace">{{ $sale->reference_number }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold"
                                                    style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ substr($sale->customer->name ?? 'W', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">
                                                        {{ $sale->customer->name ?? 'Walk-in Customer' }}
                                                    </div>
                                                    <div class="small text-muted">{{ $sale->user->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-secondary small">{{ $sale->created_at->format('M d, Y • h:i A') }}
                                        </td>
                                        <td>
                                            @php
                                                $badge = match ($sale->payment_method) {
                                                    'cash' => 'bg-success text-white',
                                                    'credit' => 'bg-danger text-white',
                                                    default => 'bg-info text-white',
                                                };
                                                $icon = match ($sale->payment_method) {
                                                    'cash' => 'fa-money-bill-wave',
                                                    'credit' => 'fa-file-invoice-dollar',
                                                    default => 'fa-credit-card',
                                                };
                                            @endphp
                                            <i class="fas {{ $icon }} me-1 small"></i> {{ ucfirst($sale->payment_method) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($sale->salesReturns->where('condition', 'good')->isNotEmpty())
                                                <span
                                                    class="badge bg-white text-success border border-success px-2 py-1 rounded-pill">
                                                    <i class="fas fa-undo me-1 small"></i> Returned (Good)
                                                </span>
                                            @elseif($sale->salesReturns->where('condition', 'damaged')->isNotEmpty())
                                                <span
                                                    class="badge bg-white text-danger border border-danger px-2 py-1 rounded-pill">
                                                    <i class="fas fa-exclamation-circle me-1 small"></i> Returned (Damaged)
                                                </span>
                                            @elseif(request('archived'))
                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                                    <i class="fas fa-ban me-1 small"></i> Voided
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">
                                                    <i class="fas fa-check-circle me-1 small"></i> Completed
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-6 font-monospace">
                                            ₱{{ number_format($sale->total_amount, 2) }}</td>
                                        <td class="text-end pe-4">
                                            <i class="fas fa-chevron-right text-muted small opacity-50"></i>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- MOBILE VIEW --}}
            <div class="d-lg-none pb-5">
                <div class="list-group list-group-flush rounded-4 shadow-sm overflow-hidden">
                    @forelse($transactions as $sale)
                        <a href="{{ route('transactions.show', $sale->id) }}"
                            class="list-group-item list-group-item-action p-3 border-bottom-0 border-start-0 border-end-0 border-top hover-bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span
                                        class="badge {{ $sale->payment_method == 'credit' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} rounded-pill border {{ $sale->payment_method == 'credit' ? 'border-danger-subtle' : 'border-success-subtle' }}">
                                        {{ ucfirst($sale->payment_method) }}
                                    </span>

                                    @if($sale->salesReturns->where('condition', 'good')->isNotEmpty())
                                        <span class="badge bg-white text-success border border-success rounded-pill px-2">
                                            <i class="fas fa-undo"></i>
                                        </span>
                                    @endif
                                    @if($sale->salesReturns->where('condition', 'damaged')->isNotEmpty())
                                        <span class="badge bg-white text-danger border border-danger rounded-pill px-2">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </span>
                                    @endif

                                    <span class="text-muted small">#{{ $sale->id }}</span>
                                </div>
                                <small class="text-muted">{{ $sale->created_at->format('M d, h:i A') }}</small>
                            </div>

                            @if(request('archived'))
                                <div class="text-center bg-danger-subtle text-danger small py-1 rounded fw-bold mb-2">
                                    <i class="fas fa-ban me-1"></i> ARCHIVED (VOIDED)
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-end">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-light text-dark rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold border"
                                        style="width: 40px; height: 40px;">
                                        {{ substr($sale->customer->name ?? 'W', 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 line-clamp-1">
                                            {{ $sale->customer->name ?? 'Walk-in Customer' }}
                                        </h6>
                                        <div class="small text-muted">Cashier: {{ $sale->user->name }}</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h5 class="fw-bold text-dark mb-0">₱{{ number_format($sale->total_amount, 2) }}</h5>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-5 text-muted bg-white">
                            <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
                            <p>No transactions found.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($transactions->hasPages())
                <div class="pb-4 pt-3 d-flex justify-content-center w-100">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection