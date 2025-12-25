@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    {{-- MOBILE HEADER & NAV --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-file-invoice-dollar text-danger me-2"></i>Credits</h6>
        </div>
        
        {{-- Horizontal Scrollable Nav --}}
        <div class="d-flex overflow-auto px-3 pb-3 gap-2 no-scrollbar">
            <a href="{{ route('reports.index') }}" class="btn btn-light border rounded-pill px-4 text-secondary flex-shrink-0">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-light border rounded-pill px-4 text-secondary flex-shrink-0">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-primary rounded-pill shadow-sm fw-bold px-4 flex-shrink-0">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-light border rounded-pill px-4 text-secondary flex-shrink-0">Forecast</a>
        </div>
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
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

    <div class="px-3 px-md-0 pt-3 pt-md-0">
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
            <div class="d-md-none p-3">
                <div class="list-group list-group-flush gap-2">
                    @foreach($credits as $credit)
                    <div class="list-group-item p-3 border-0 bg-light rounded-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-white text-dark shadow-sm rounded-pill">#{{ $credit->sale_id }}</span>
                            @if($credit->due_date && \Carbon\Carbon::parse($credit->due_date)->isPast())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">OVERDUE</span>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">{{ $credit->customer->name ?? 'Unknown' }}</h6>
                                <small class="text-muted">Due: {{ $credit->due_date ?? 'N/A' }}</small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Balance</small>
                                <div class="fw-bold text-danger fs-5">₱{{ number_format($credit->remaining_balance, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .hover-primary:hover { background-color: #0d6efd !important; color: white !important; border-color: #0d6efd !important; }
</style>
@endsection