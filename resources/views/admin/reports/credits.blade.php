@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mt-4 mb-4 gap-3">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-file-invoice-dollar text-danger me-2"></i>Credit Report</h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-primary shadow-sm flex-fill flex-xl-grow-0">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Forecast</a>
        </div>
    </div>

    {{-- TOTAL DEBT CARD --}}
    <div class="card bg-danger text-white border-0 shadow-sm mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <small class="text-white-50 text-uppercase fw-bold">Total Outstanding (Collectibles)</small>
                <h2 class="fw-bold mb-0">₱{{ number_format($totalReceivables, 2) }}</h2>
            </div>
            <i class="fas fa-hand-holding-usd fa-3x opacity-25"></i>
        </div>
    </div>

    {{-- CREDIT LIST --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary">Outstanding Accounts</h5>
            <a href="{{ route('reports.export', ['report_type' => 'credits']) }}" class="btn btn-sm btn-success shadow-sm">
                <i class="fas fa-download me-1"></i> CSV
            </a>
        </div>

        {{-- Desktop Table --}}
        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-secondary">
                        <tr>
                            <th class="ps-4">Customer</th>
                            <th>Sale Ref</th>
                            <th>Date Incurred</th>
                            <th>Due Date</th>
                            <th class="text-end pe-4">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($credits as $credit)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $credit->customer->name ?? 'Unknown' }}</td>
                            <td><span class="badge bg-light text-dark border">#{{ $credit->sale_id }}</span></td>
                            <td class="text-muted">{{ $credit->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($credit->due_date && \Carbon\Carbon::parse($credit->due_date)->isPast())
                                    <span class="badge bg-danger-subtle text-danger">{{ $credit->due_date }}</span>
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

        {{-- Mobile Cards --}}
        <div class="d-md-none">
            <div class="list-group list-group-flush">
                @foreach($credits as $credit)
                <div class="list-group-item p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold">{{ $credit->customer->name ?? 'Unknown' }}</div>
                        <span class="badge bg-danger-subtle text-danger border border-danger">₱{{ number_format($credit->remaining_balance, 2) }}</span>
                    </div>
                    <div class="small text-muted d-flex justify-content-between">
                        <span>Sale #{{ $credit->sale_id }}</span>
                        @if($credit->due_date && \Carbon\Carbon::parse($credit->due_date)->isPast())
                            <span class="text-danger fw-bold">Due: {{ $credit->due_date }}</span>
                        @else
                            <span>Due: {{ $credit->due_date ?? 'N/A' }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection