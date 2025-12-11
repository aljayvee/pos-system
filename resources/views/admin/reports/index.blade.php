@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-chart-line text-primary"></i> Sales Report</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('reports.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="type" class="form-select">
                        <option value="daily" {{ $type == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4 text-center">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3>₱{{ number_format($total_sales, 2) }}</h3>
                    <small>Total Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3>{{ $total_transactions }}</h3>
                    <small>Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h3>₱{{ number_format($credit_sales, 2) }}</h3>
                    <small>Credit Sales (Utang)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Transaction History</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Time</th>
                        <th>Cashier</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>{{ $sale->created_at->format('h:i A') }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td>
                            <span class="badge {{ $sale->payment_method == 'credit' ? 'bg-danger' : 'bg-success' }}">
                                {{ ucfirst($sale->payment_method) }}
                            </span>
                        </td>
                        <td class="fw-bold">₱{{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No transactions found for this date.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection