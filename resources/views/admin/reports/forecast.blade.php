@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mt-4 mb-4 gap-3">
        <div>
            <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-magic text-primary me-2"></i>Forecast</h1>
            <p class="text-muted mb-0 small">Stock predictions based on 30-day sales velocity.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Sales</a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Inventory</a>
            <a href="{{ route('reports.credits') }}" class="btn btn-outline-primary shadow-sm flex-fill flex-xl-grow-0">Credits</a>
            <a href="{{ route('reports.forecast') }}" class="btn btn-primary shadow-sm flex-fill flex-xl-grow-0">Forecast</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        
        {{-- Desktop Table --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-secondary">
                        <tr>
                            <th class="ps-4">Product</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Daily Sales</th>
                            <th class="text-center">Days Left</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Reorder Suggestion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($forecastData as $item)
                        <tr class="{{ $item['reorder_qty'] > 0 ? 'bg-warning-subtle' : '' }}">
                            <td class="ps-4">
                                <div class="fw-bold">{{ $item['name'] }}</div>
                                <small class="text-muted">{{ $item['category'] }}</small>
                            </td>
                            <td class="text-center">{{ $item['stock'] }}</td>
                            {{-- FIX: Format number here --}}
                            <td class="text-center text-muted">{{ number_format($item['ads'], 2) }}/day</td>
                            <td class="text-center fw-bold">
                                {{ $item['days_left'] > 365 ? '> 1 Year' : number_format($item['days_left'], 1) . ' Days' }}
                            </td>
                            <td>
                                @if(str_contains($item['status'], 'Out')) <span class="badge bg-dark">Empty</span>
                                @elseif(str_contains($item['status'], 'Critical')) <span class="badge bg-danger">Critical</span>
                                @elseif(str_contains($item['status'], 'Low')) <span class="badge bg-warning text-dark">Low</span>
                                @else <span class="badge bg-success">Healthy</span>
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

        {{-- Mobile Cards --}}
        <div class="d-lg-none">
            <div class="list-group list-group-flush">
                @forelse($forecastData as $item)
                <div class="list-group-item p-3 {{ $item['reorder_qty'] > 0 ? 'bg-warning-subtle' : '' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold">{{ $item['name'] }}</div>
                        <span class="badge bg-light text-dark border">{{ $item['stock'] }} in stock</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        {{-- FIX: Format number here --}}
                        <small class="text-muted">Avg: {{ number_format($item['ads'], 2) }} sold/day</small>
                        @if($item['reorder_qty'] > 0)
                            <span class="badge bg-success">+{{ $item['reorder_qty'] }} Reorder</span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-2">
                        <small class="fw-bold">Days Left:</small>
                        <div class="progress flex-grow-1" style="height: 10px;">
                            @php
                                // FIX: $item['days_left'] is now a float, so math works safely
                                $val = min($item['days_left'], 30);
                                $pct = ($val / 30) * 100;
                                $color = $item['days_left'] < 7 ? 'bg-danger' : ($item['days_left'] < 14 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress-bar {{ $color }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <small class="text-muted" style="width: 60px; text-align: right">
                            {{ $item['days_left'] > 365 ? '>1yr' : number_format($item['days_left'], 1) . 'd' }}
                        </small>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">No data.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection