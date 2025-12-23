@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="fas fa-chart-line text-primary me-2"></i>Smart Forecast</h4>
            <p class="text-muted small mb-0">AI-driven velocity analysis and stock depletion predictions.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary single-click-link">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-primary shadow-sm flex-fill flex-xl-grow-0 rounded-pill fw-bold px-4 single-click-link">Forecast</a>
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-danger bg-opacity-10">
                <div class="card-body p-3">
                    <small class="text-danger fw-bold text-uppercase" style="font-size: 0.65rem;">Stockout Risk</small>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ $outOfStockCount }}</h3>
                    <small class="text-muted">Items Out of Stock</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning bg-opacity-10">
                <div class="card-body p-3">
                    <small class="text-warning fw-bold text-dark-emphasis text-uppercase" style="font-size: 0.65rem;">Critical Level</small>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ $criticalCount }}</h3>
                    <small class="text-muted">Need Reorder</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="fw-bold"><i class="fas fa-info-circle text-primary me-2"></i>Prediction Logic</h6>
                        <p class="small text-muted mb-0 lh-sm">Forecasts are based on <strong>30-day sales velocity</strong> (Activity-Based Costing). Items are classified as Fast, Average, or Slow moving to prioritize restock budget.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between align-items-center">
             <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-hourglass-half me-2 text-primary"></i>Stock Velocity & Health</h5>
             <small class="text-muted fst-italic">Sorted by Urgency & Revenue Contribution</small>
        </div>
        
        {{-- Desktop Table --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-secondary fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Product</th>
                            <th class="text-center py-3">Class (ABC)</th>
                            <th class="text-center py-3">Velocity</th>
                            <th class="text-center py-3">Stock Left</th>
                            <th class="text-center py-3">Days Until Empty</th>
                            <th class="py-3">Status</th>
                            <th class="text-end pe-4 py-3">Reorder Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($forecastData as $item)
                        <tr class="{{ $item['status'] === 'Out of Stock' ? 'table-danger' : ($item['status'] === 'Critical' ? 'table-warning' : '') }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $item['name'] }}</div>
                                        <div class="text-muted small">{{ $item['category'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($item['class'] === 'A')
                                    <span class="badge bg-primary rounded-pill px-3 shadow-sm border border-primary-subtle" title="High Revenue Contributor">Class A</span>
                                @elseif($item['class'] === 'B')
                                    <span class="badge bg-info text-dark rounded-pill px-3 border border-info-subtle">Class B</span>
                                @else
                                    <span class="badge bg-light text-secondary rounded-pill px-3 border">Class C</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['movement'] === 'Fast Moving')
                                    <span class="text-success fw-bold small"><i class="fas fa-bolt me-1"></i>Fast</span>
                                @elseif($item['movement'] === 'Slow Moving')
                                    <span class="text-muted small">Slow</span>
                                @elseif($item['movement'] === 'Non-Moving')
                                    <span class="text-secondary small fst-italic">Stagnant</span>
                                @else
                                    <span class="text-dark small">Average</span>
                                @endif
                                <div style="font-size: 0.7rem;" class="text-muted">{{ number_format($item['velocity'], 2) }}/day</div>
                            </td>
                            <td class="text-center fw-bold">{{ $item['stock'] }}</td>
                            <td class="text-center">
                                @if($item['doi'] > 365)
                                    <span class="badge bg-light text-muted border rounded-pill">> 1 Year</span>
                                @else
                                    <span class="fw-bold {{ $item['doi'] < 7 ? 'text-danger' : 'text-dark' }}">
                                        {{ number_format($item['doi'], 1) }} Days
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($item['status'] === 'Out of Stock') <span class="badge bg-dark rounded-pill px-3">Empty</span>
                                @elseif($item['status'] === 'Critical') <span class="badge bg-danger rounded-pill px-3">Critical</span>
                                @elseif($item['status'] === 'Low') <span class="badge bg-warning text-dark-emphasis rounded-pill px-3">Low</span>
                                @else <span class="badge bg-success rounded-pill px-3">Healthy</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($item['reorder_qty'] > 0)
                                    <div class="fw-bold text-danger">+{{ $item['reorder_qty'] }}</div>
                                    <small class="text-muted" style="font-size: 0.65rem;">to reach safety stock</small>
                                @else
                                    <span class="text-success small"><i class="fas fa-check"></i></span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">Insufficient data to forecast.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Native View --}}
        <div class="d-lg-none bg-light p-3">
            @forelse($forecastData as $item)
            <div class="card border-0 shadow-sm mb-3 rounded-4 {{ $item['reorder_qty'] > 0 ? 'border-start border-4 border-warning' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold text-dark">{{ $item['name'] }}</div>
                            <small class="text-muted">{{ $item['stock'] }} in stock</small>
                        </div>
                        <div class="text-end">
                            @if($item['class'] === 'A')
                                <span class="badge bg-primary shadow-sm rounded-pill mb-1">Class A</span>
                            @endif
                            @if($item['reorder_qty'] > 0)
                                <div class="badge bg-warning text-dark shadow-sm rounded-pill">+{{ $item['reorder_qty'] }} Order</div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Depletion</span>
                            <span class="fw-bold {{ $item['doi'] < 7 ? 'text-danger' : 'text-dark' }}">
                                {{ $item['doi'] > 365 ? '>1 Year' : number_format($item['doi'], 1) . ' Days' }}
                            </span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 4px;">
                            @php
                                $val = min($item['doi'], 30);
                                $pct = ($val / 30) * 100;
                                $color = $item['doi'] < 7 ? 'bg-danger' : ($item['doi'] < 14 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress-bar {{ $color }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                             <small class="text-muted style='font-size: 0.65rem;'">{{ $item['movement'] }}</small>
                             <small class="text-dark fw-bold" style="font-size: 0.65rem;">{{ number_format($item['velocity'], 2) }}/day</small>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">No data available.</div>
            @endforelse
        </div>
    </div>
</div>
<style>
    .hover-primary:hover { background-color: #0d6efd !important; color: white !important; border-color: #0d6efd !important; }
</style>
@endsection