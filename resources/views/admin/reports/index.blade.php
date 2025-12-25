@extends('admin.layout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    {{-- MOBILE HEADER & NAV --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Analytics</h6>
        </div>
        
        {{-- Horizontal Scrollable Nav --}}
        <div class="d-flex overflow-auto px-3 pb-3 gap-2 no-scrollbar">
            <a href="{{ route('reports.index') }}" class="btn btn-primary rounded-pill shadow-sm fw-bold px-4 flex-shrink-0">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-light border rounded-pill px-4 text-secondary flex-shrink-0">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-light border rounded-pill px-4 text-secondary flex-shrink-0">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-light border rounded-pill px-4 text-secondary flex-shrink-0">Forecast</a>
        </div>
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="fas fa-chart-pie text-primary me-2"></i>Analytics</h4>
            <p class="text-muted small mb-0">Overview of sales performance and business health.</p>
        </div>
        
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-primary shadow-sm flex-fill flex-xl-grow-0 rounded-pill fw-bold px-4 single-click-link">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Forecast</a>
        </div>
    </div>

    <div class="px-3 px-md-0 pt-3 pt-md-0">
        {{-- FILTERS --}}
        <div class="card mb-4 border-0 shadow-sm rounded-4">
            <div class="card-body p-2">
                <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-center">
                    @if(config('safety_flag_features.multi_store'))
                    @if($isMultiStore == '1')
                    <div class="col-12 col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-store text-muted"></i></span>
                            <select name="store_filter" class="form-select bg-light border-0" onchange="this.form.submit()">
                                <option value="all" {{ $targetStore == 'all' ? 'selected' : '' }}>-- All Branches --</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ $targetStore == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                    @endif

                    <div class="col-6 col-md-2">
                        <select name="type" class="form-select bg-light border-0" onchange="this.form.submit()">
                            <option value="daily" {{ $type == 'daily' ? 'selected' : '' }}>Daily View</option>
                            <option value="weekly" {{ $type == 'weekly' ? 'selected' : '' }}>Weekly View</option>
                            <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>Monthly View</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="far fa-calendar-alt text-muted"></i></span>
                            <input type="date" name="start_date" class="form-control bg-light border-0" value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <button type="submit" class="btn btn-dark w-100 rounded-pill shadow-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                    </div>
                    <div class="col-6 col-md-2">
                        <a href="{{ route('reports.export', ['report_type' => 'sales', 'start_date' => $startDate]) }}" class="btn btn-success w-100 rounded-pill shadow-sm text-white">
                            <i class="fas fa-download me-1"></i> CSV
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- METRICS GRID --}}
        <div class="row g-2 g-xl-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <div class="card-body p-3 text-white">
                        <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.65rem;">Total Revenue</small>
                        <h3 class="fw-bold mb-0 mt-1">₱{{ number_format($total_sales, 2) }}</h3>
                        <div class="small text-white-50 mt-1"><i class="fas fa-coins me-1 opacity-50"></i>Gross Sales</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
                    <div class="card-body p-3 text-white">
                        <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.65rem;">Gross Profit</small>
                        <h3 class="fw-bold mb-0 mt-1">₱{{ number_format($gross_profit, 2) }}</h3>
                        <div class="small text-white-50 mt-1"><i class="fas fa-chart-line me-1 opacity-50"></i>Estimate</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0 rounded-4 border-start border-4 border-warning bg-white">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Transactions</small>
                        <h3 class="fw-bold mb-0 mt-1 text-dark">{{ number_format($total_transactions) }}</h3>
                        <div class="small text-muted mt-1"><i class="fas fa-receipt me-1 opacity-50"></i>Total Count</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);">
                    <div class="card-body p-3 text-white">
                        <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.65rem;">Tithes (10%)</small>
                        <h3 class="fw-bold mb-0 mt-1">₱{{ number_format($tithesAmount, 2) }}</h3>
                        <div class="small text-white-50 mt-1"><i class="fas fa-hand-holding-heart me-1 opacity-50"></i>Calculated</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 1: CHARTS & TOP ITEMS --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 rounded-4">
                    <div class="card-header bg-white fw-bold py-3 border-bottom border-light">Sales by Category</div>
                    <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 300px;">
                        @if($salesByCategory->count() > 0)
                            <div style="position: relative; height: 250px; width: 100%;">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-chart-pie fa-3x mb-3 opacity-25"></i>
                                <p class="small mb-0">No categorical data available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-success bg-opacity-10 py-3 d-flex align-items-center">
                        <h5 class="mb-0 fw-bold text-success"><i class="fas fa-trophy me-2"></i>Top Sellers</h5>
                    </div>
                    
                    {{-- Desktop Table --}}
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-uppercase small text-secondary">
                                    <tr>
                                        <th class="ps-4">Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end pe-4">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topItems as $item)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $item->product->name ?? 'Unknown' }}</td>
                                        <td class="text-center"><span class="badge bg-light text-dark border shadow-sm px-3 rounded-pill">{{ $item->total_qty }}</span></td>
                                        <td class="text-end pe-4 text-success fw-bold">₱{{ number_format($item->total_revenue, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-5 text-muted">No sales items recorded for this period.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Mobile List (Leaderboard Style) --}}
                    <div class="d-md-none p-3">
                        <div class="list-group list-group-flush gap-2">
                            @forelse($topItems as $index => $item)
                            <div class="list-group-item p-3 border-0 bg-light rounded-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3 text-secondary fw-bold" style="width: 40px; height: 40px;">#{{ $index + 1 }}</div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark">{{ $item->product->name ?? 'Unknown' }}</div>
                                        <small class="text-muted">{{ $item->total_qty }} units sold</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success">₱{{ number_format($item->total_revenue, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center text-muted py-4">No sales data found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TRANSACTION HISTORY --}}
        <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-receipt me-2 text-primary"></i>Recent Transactions</h5>
            </div>
            
            {{-- Desktop Table --}}
            <div class="d-none d-lg-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small text-uppercase fw-bold text-secondary">
                            <tr>
                                <th class="ps-4 py-3">Ref ID</th>
                                <th class="py-3">Date</th>
                                <th class="py-3">Customer</th>
                                <th class="py-3">Method</th>
                                <th class="text-end py-3">Total</th>
                                <th class="text-end pe-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                            <tr>
                                <td class="ps-4 text-muted"><span class="badge bg-light text-dark border rounded-pill px-3">#{{ $sale->id }}</span></td>
                                <td>{{ $sale->created_at->format('M d, h:i A') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle me-2 d-flex justify-content-center align-items-center text-primary" style="width:30px; height:30px">
                                            <i class="fas fa-user small"></i>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $sale->customer->name ?? 'Walk-in' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $methodColor = match($sale->payment_method) {
                                            'credit' => 'danger',
                                            'cash' => 'success',
                                            'gcash', 'paymaya' => 'info',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $methodColor }}-subtle text-{{ $methodColor }} border border-{{ $methodColor }}-subtle rounded-pill text-uppercase px-3">
                                        {{ $sale->payment_method }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-dark">₱{{ number_format($sale->total_amount, 2) }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-sm btn-light border text-primary fw-bold rounded-pill px-3 shadow-sm hover-lift">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No transactions found for this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Cards --}}
            <div class="d-lg-none bg-light p-3">
                @forelse($sales as $sale)
                <div class="card shadow-sm border-0 mb-3 rounded-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-secondary border rounded-pill">#{{ $sale->id }}</span>
                            <small class="text-muted"><i class="far fa-clock me-1"></i>{{ $sale->created_at->format('M d, h:i A') }}</small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h5 class="fw-bold mb-0 text-dark">₱{{ number_format($sale->total_amount, 2) }}</h5>
                            @php
                                $methodColor = match($sale->payment_method) {
                                    'credit' => 'danger',
                                    'cash' => 'success',
                                    default => 'primary'
                                };
                            @endphp
                            <span class="badge bg-{{ $methodColor }}-subtle text-{{ $methodColor }} rounded-pill text-uppercase border border-{{ $methodColor }}-subtle">
                                {{ $sale->payment_method }}
                            </span>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-bold"><i class="fas fa-user me-1"></i> {{ $sale->customer->name ?? 'Walk-in' }}</span>
                            <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold text-primary">Details</a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">No transactions found.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('categoryChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($salesByCategory->pluck('name')) !!},
                    datasets: [{
                        data: {!! json_encode($salesByCategory->pluck('total_revenue')) !!},
                        backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6610f2'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 20, usePointStyle: true } } 
                    },
                    cutout: '70%',
                }
            });
        }
    });
</script>
<style>
    .hover-primary:hover { background-color: #0d6efd !important; color: white !important; border-color: #0d6efd !important; }
    .hover-lift:hover { transform: translateY(-2px); transition: all 0.2s; }
</style>
@endsection