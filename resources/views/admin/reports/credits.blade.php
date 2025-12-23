@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="fas fa-file-invoice-dollar text-danger me-2"></i>Credit Report</h4>
            <p class="text-muted small mb-0">Track outstanding receivables and overdue accounts.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-primary shadow-sm flex-fill flex-xl-grow-0 rounded-pill fw-bold px-4 single-click-link">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Forecast</a>
        </div>
    </div>

    {{-- TOTAL DEBT CARD --}}
    <div class="card text-white border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <small class="text-white-50 text-uppercase fw-bold">Total Outstanding Debt</small>
                <h2 class="fw-bold mb-0">₱{{ number_format($totalReceivables, 2) }}</h2>
                <span class="badge bg-white bg-opacity-25 text-white mt-2">To Collect</span>
            </div>
            <div class="bg-white bg-opacity-10 rounded-circle p-3 shadow-sm">
                <i class="fas fa-hand-holding-usd fa-3x text-white-50"></i>
            </div>
        </div>
    </div>

    {{-- CREDIT LIST --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom border-light">
            <h5 class="mb-0 fw-bold text-dark">Outstanding Accounts</h5>
            <a href="{{ route('reports.export', ['report_type' => 'credits']) }}" class="btn btn-sm btn-success shadow-sm rounded-pill px-3">
                <i class="fas fa-download me-1"></i> CSV
            </a>
        </div>

        {{-- Desktop Table --}}
        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-secondary fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Customer</th>
                            <th class="py-3">Sale Ref</th>
                            <th class="py-3">Date Incurred</th>
                            <th class="py-3">Due Date</th>
                            <th class="text-end pe-4 py-3">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($credits as $credit)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $credit->customer->name ?? 'Unknown' }}</td>
                            <td><span class="badge bg-light text-dark border rounded-pill px-3">#{{ $credit->sale_id }}</span></td>
                            <td class="text-muted">{{ $credit->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($credit->due_date && \Carbon\Carbon::parse($credit->due_date)->isPast())
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Overdue: {{ $credit->due_date }}</span>
                                @else
                                    <span class="text-muted">{{ $credit->due_date ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Native View --}}
        <div class="d-md-none bg-light p-3">
            @foreach($credits as $credit)
            <div class="card shadow-sm border-0 mb-3 rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold text-dark mb-0">{{ $credit->customer->name ?? 'Unknown' }}</h5>
                        <span class="badge bg-light text-secondary border rounded-pill">#{{ $credit->sale_id }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                         <div>
                            @if($credit->due_date && \Carbon\Carbon::parse($credit->due_date)->isPast())
                                <small class="text-danger fw-bold text-uppercase" style="font-size: 0.65rem;">Overdue</small>
                                <div class="text-danger small fw-bold">{{ $credit->due_date }}</div>
                            @else
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Due Date</small>
                                <div class="text-dark small">{{ $credit->due_date ?? 'N/A' }}</div>
                            @endif
                         </div>
                         <div class="text-end">
                             <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Balance</small>
                             <div class="fw-bold text-danger fs-5">₱{{ number_format($credit->remaining_balance, 2) }}</div>
                         </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
<style>
    .hover-primary:hover { background-color: #0d6efd !important; color: white !important; border-color: #0d6efd !important; }
</style>
@endsection