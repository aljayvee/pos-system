@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="fas fa-magic text-primary me-2"></i>Forecast</h4>
            <p class="text-muted small mb-0">Stock depletion predictions based on 30-day sales velocity.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-white border shadow-sm flex-fill flex-xl-grow-0 rounded-pill px-4 text-secondary hover-primary">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-primary shadow-sm flex-fill flex-xl-grow-0 rounded-pill fw-bold px-4">Forecast</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
             <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-hourglass-half me-2 text-primary"></i>Stock Velocity</h5>
        </div>
        
        {{-- Desktop Table --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-secondary fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Product</th>
                            <th class="text-center py-3">Stock</th>
                            <th class="text-center py-3">Daily Sales</th>
                            <th class="text-center py-3">Days Left</th>
                            <th class="py-3">Status</th>
                            <th class="text-end pe-4 py-3">Reorder Suggestion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($forecastData as $item)
                        <tr class="{{ $item['reorder_qty'] > 0 ? 'bg-warning-subtle' : '' }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $item['name'] }}</span>
                                <div class="text-muted small">{{ $item['category'] }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $item['stock'] }}</span>
                            </td>
                            <td class="text-center text-muted">{{ number_format($item['ads'], 2) }}/day</td>
                            <td class="text-center fw-bold">
                                {{ $item['days_left'] > 365 ? '> 1 Year' : number_format($item['days_left'], 1) . ' Days' }}
                            </td>
                            <td>
                                @if(str_contains($item['status'], 'Out')) <span class="badge bg-dark rounded-pill px-3">Empty</span>
                                @elseif(str_contains($item['status'], 'Critical')) <span class="badge bg-danger rounded-pill px-3">Critical</span>
                                @elseif(str_contains($item['status'], 'Low')) <span class="badge bg-warning text-dark-emphasis rounded-pill px-3">Low</span>
                                @else <span class="badge bg-success rounded-pill px-3">Healthy</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($item['reorder_qty'] > 0)
                                    <span class="text-success fw-bold">+{{ $item['reorder_qty'] }} units</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Insufficient data to forecast.</td></tr>
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
                        @if($item['reorder_qty'] > 0)
                            <span class="badge bg-warning text-dark shadow-sm rounded-pill">+{{ $item['reorder_qty'] }} Reorder</span>
                        @endif
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Supply Remaining</span>
                            <span class="fw-bold {{ $item['days_left'] < 7 ? 'text-danger' : 'text-dark' }}">
                                {{ $item['days_left'] > 365 ? '>1 Year' : number_format($item['days_left'], 1) . ' Days' }}
                            </span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            @php
                                $val = min($item['days_left'], 30);
                                $pct = ($val / 30) * 100;
                                $color = $item['days_left'] < 7 ? 'bg-danger' : ($item['days_left'] < 14 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress-bar {{ $color }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <small class="text-muted mt-1 d-block text-end" style="font-size: 0.65rem;">Selling {{ number_format($item['ads'], 2) }}/day</small>
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