@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-chart-line text-primary"></i> Sales Report</h2>

   {{-- ... existing code ... --}}
   {{-- ... Statistics Cards are above here ... --}}

    <div class="row">
        <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-trophy me-1"></i> Top Selling Items ({{ ucfirst($type) }})
                </div>
                <div class="card-body p-0">
                   <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Item Name</th>
                                <th class="text-center">Qty Sold</th>
                                <th class="text-end pe-3">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topItems as $item)
                            <tr>
                                <td class="ps-3">{{ $item->product->name ?? 'Unknown Item' }}</td>
                                <td class="text-center fw-bold">{{ $item->total_qty }}</td>
                                <td class="text-end pe-3">₱{{ number_format($item->total_revenue, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center p-3">No sales data for this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-hourglass-half me-1"></i> Slow Moving (Last 30 Days)
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Item Name</th>
                                <th class="text-center">Stock</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slowMovingItems as $item)
                            <tr>
                                <td class="ps-3">{{ $item->name }}</td>
                                <td class="text-center fw-bold">{{ $item->stock }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('products.edit', $item->id) }}" class="btn btn-xs btn-outline-dark" title="Edit/Discount">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center p-3 text-muted">All items are selling well!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Payment Breakdown
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Cash Sales:</span>
                        <span class="fw-bold text-success">₱{{ number_format($cash_sales, 2) }}</span>
                    </div>
                    {{-- NEW: Digital Sales Row --}}
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Digital (e-Wallet / Banks):</span>
                        <span class="fw-bold text-info">₱{{ number_format($digital_sales, 2) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Credit (Utang):</span>
                        <span class="fw-bold text-warning">₱{{ number_format($credit_sales, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span>Total:</span>
                        <span class="fw-bold">₱{{ number_format($total_sales, 2) }}</span>
                    </div>
                </div>  
            </div>
        </div>
    </div>

    {{-- ... Sales Transaction Table follows here ... --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            {{-- REMOVE action="..." from the form tag so buttons can decide where to go --}}
            <form method="GET" class="row g-3 align-items-end">
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
                
                {{-- UPDATED BUTTONS AREA --}}
                <div class="col-md-4 d-flex gap-2">
                    {{-- Filter Button --}}
                    <button type="submit" 
                            formaction="{{ route('reports.index') }}" 
                            class="btn btn-primary flex-fill">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                    {{-- Export Button --}}
                    <button type="submit" 
                            formaction="{{ route('reports.export') }}" 
                            class="btn btn-success flex-fill">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
{{-- ... existing code ... --}}

<div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Sales</h5>
                    <h2 class="fw-bold">₱{{ number_format($total_sales, 2) }}</h2>
                    <small>Revenue</small>
                </div>
            </div>
        </div>

        {{-- NEW: GROSS PROFIT CARD --}}
        <div class="col-md-3">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-coins"></i> Gross Profit</h5>
                    <h2 class="fw-bold">₱{{ number_format($gross_profit, 2) }}</h2>
                    <small>Income (Sales - Cost)</small>
                </div>
            </div>
        </div>

        {{-- Tithes Card (Existing) --}}
        @if($tithesEnabled == '1')
        <div class="col-md-3">
            <div class="card bg-info text-white mb-4 position-relative overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-hand-holding-heart"></i> Tithes (10%)</h5>
                    <h2 class="fw-bold">₱{{ number_format($tithesAmount, 2) }}</h2>
                    <small class="text-white-50">Allocated for Offering</small>
                </div>
                <i class="fas fa-church position-absolute" style="font-size: 5rem; opacity: 0.2; right: 10px; bottom: -10px;"></i>
            </div>
        </div>
        @endif
        
        <div class="col-md-3">
            <div class="card bg-secondary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Transactions</h5>
                    <h2 class="fw-bold">{{ $total_transactions }}</h2>
                    <small>Count</small>
                </div>
            </div>
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