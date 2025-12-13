@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h1><i class="fas fa-file-invoice-dollar text-danger"></i> Credit Report</h1>
        <div class="btn-group">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">Sales & Analytics</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-outline-primary">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-primary active">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-outline-primary">Forecast</a>
        </div>
    </div>

    <div class="alert alert-danger d-flex justify-content-between align-items-center">
        <span><strong>Total Collectibles (Utang):</strong></span>
        <h3 class="m-0 fw-bold">₱{{ number_format($totalReceivables, 2) }}</h3>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-users me-1"></i> Outstanding Balances</span>
            <a href="{{ route('reports.export', ['report_type' => 'credits']) }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-csv"></i> Export Credits
            </a>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Sale ID</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($credits as $credit)
                    <tr>
                        <td class="fw-bold">{{ $credit->customer->name ?? 'Unknown' }}</td>
                        <td>#{{ $credit->sale_id }}</td>
                        <td>{{ $credit->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($credit->due_date && \Carbon\Carbon::parse($credit->due_date)->isPast())
                                <span class="text-danger fw-bold">{{ $credit->due_date }} (Overdue)</span>
                            @else
                                {{ $credit->due_date ?? '-' }}
                            @endif
                        </td>
                        <td class="text-end text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection