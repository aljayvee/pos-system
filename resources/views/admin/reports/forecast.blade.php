@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-magic text-primary"></i> Inventory Forecast</h2>
            <p class="text-muted">Prediction based on last 30 days sales velocity.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Back to Reports</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Current Stock</th>
                            <th class="text-center">Avg. Daily Sales</th>
                            <th class="text-center">Est. Days Left</th>
                            <th>Status</th>
                            <th class="text-end">Recommended Reorder (for 2 weeks)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($forecastData as $item)
                        <tr class="{{ $item['reorder_qty'] > 0 ? 'table-warning' : '' }}">
                            <td class="fw-bold">{{ $item['name'] }} <br> <small class="text-muted fw-normal">{{ $item['category'] }}</small></td>
                            <td class="text-center">{{ $item['stock'] }}</td>
                            <td class="text-center">{{ $item['ads'] }} / day</td>
                            <td class="text-center fw-bold">{{ $item['days_left'] > 365 ? '> 1 Year' : $item['days_left'] . ' Days' }}</td>
                            <td>
                                @if($item['status'] == 'Out of Stock') <span class="badge bg-dark">Out of Stock</span>
                                @elseif(str_contains($item['status'], 'Critical')) <span class="badge bg-danger">{{ $item['status'] }}</span>
                                @elseif(str_contains($item['status'], 'Low')) <span class="badge bg-warning text-dark">{{ $item['status'] }}</span>
                                @else <span class="badge bg-success">Healthy</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($item['reorder_qty'] > 0)
                                    <span class="text-success fw-bold">+{{ $item['reorder_qty'] }} units</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Not enough sales data to generate a forecast.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection