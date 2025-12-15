@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <div>
            <h1 class="h2 mb-0"><i class="fas fa-history text-secondary me-2"></i>Full Adjustment History</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Logs</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('inventory.export') }}" class="btn btn-outline-success">
            <i class="fas fa-download me-1"></i> Export Data
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary text-uppercase small fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Timestamp</th>
                            <th class="py-3">Product Info</th>
                            <th class="py-3">Type / Reason</th>
                            <th class="py-3 text-center">Change</th>
                            <th class="py-3">Adjusted By</th>
                            <th class="py-3 pe-4">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adj)
                        <tr>
                            <td class="ps-4 text-muted" style="white-space: nowrap;">
                                {{ $adj->created_at->format('M d, Y') }} <br>
                                <small>{{ $adj->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $adj->product->name ?? 'Unknown Item' }}</span>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match(strtolower($adj->type)) {
                                        'wastage', 'spoilage/expired', 'damage', 'theft/lost' => 'bg-danger-subtle text-danger',
                                        'internal use' => 'bg-warning-subtle text-warning',
                                        default => 'bg-primary-subtle text-primary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} border border-opacity-10">{{ $adj->type }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold {{ $adj->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width:30px; height:30px;">
                                        <i class="fas fa-user text-secondary small"></i>
                                    </div>
                                    <span>{{ $adj->user->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="text-muted small pe-4" style="max-width: 250px;">
                                {{ $adj->remarks ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No adjustment history found.
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