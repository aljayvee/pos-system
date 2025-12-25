@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3 mb-3" style="top: 0;">
        <a href="{{ route('inventory.index') }}" class="text-dark"><i class="fas fa-arrow-left"></i></a>
        <h6 class="m-0 fw-bold text-dark">History</h6>
        <a href="{{ route('inventory.export') }}" class="text-success"><i class="fas fa-file-arrow-down"></i></a>
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-history text-secondary me-2"></i>Adjustment Logs
            </h4>
            <p class="text-muted small mb-0">Track all stock changes, wastage, and corrections.</p>
        </div>
        <div>
            <a href="{{ route('inventory.index') }}" class="btn btn-light border shadow-sm rounded-pill me-2">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('inventory.export') }}" class="btn btn-success rounded-pill fw-bold shadow-sm px-4">
                <i class="fas fa-file-export me-1"></i> Export Data
            </a>
        </div>
    </div>

    {{-- DESKTOP TABLE VIEW --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden d-none d-lg-block">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list-alt me-2 text-primary"></i>History Records</h5>
        </div>
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
                        @if(isset($adjustments))
                            @forelse($adjustments as $adj)
                            <tr>
                                <td class="ps-4 text-muted" style="white-space: nowrap;">
                                    <div class="fw-bold text-dark">{{ $adj->created_at->format('M d, Y') }}</div>
                                    <small>{{ $adj->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-1 me-2 text-secondary">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $adj->product->name ?? 'Unknown Item' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match(strtolower($adj->type)) {
                                            'wastage', 'spoilage/expired', 'damage', 'theft/lost' => 'bg-danger-subtle text-danger',
                                            'internal use' => 'bg-warning-subtle text-warning text-dark-emphasis',
                                            default => 'bg-primary-subtle text-primary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} border border-opacity-10 px-3 py-2 rounded-pill">{{ ucfirst($adj->type) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold fs-6 {{ $adj->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:28px; height:28px;">
                                            <i class="fas fa-user small"></i>
                                        </div>
                                        <span class="small fw-bold">{{ $adj->user->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="text-muted small pe-4" style="max-width: 250px;">
                                    {{ $adj->remarks ?? '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-history fa-2x mb-3 opacity-25"></i><br>
                                    No adjustment history found.
                                </td>
                            </tr>
                            @endforelse
                        @else
                            <tr><td colspan="6" class="text-center py-5">No Data Loaded</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MOBILE NATIVE LIST VIEW --}}
    <div class="d-lg-none card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <ul class="list-group list-group-flush">
            @if(isset($adjustments))
                @forelse($adjustments as $adj)
                <li class="list-group-item p-3 border-bottom-0 hover-bg-light">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="d-flex align-items-center gap-2">
                            @php
                                $iconClass = match(strtolower($adj->type)) {
                                    'wastage', 'spoilage/expired', 'damage', 'theft/lost' => 'fa-trash-alt text-danger',
                                    'internal use' => 'fa-clipboard-check text-warning',
                                    default => 'fa-box-open text-primary',
                                };
                                $txtColor = match(strtolower($adj->type)) {
                                    'wastage', 'spoilage/expired', 'damage', 'theft/lost' => 'text-danger',
                                    'internal use' => 'text-warning',
                                    default => 'text-primary',
                                };
                            @endphp
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center {{ $txtColor }}" style="width: 32px; height: 32px;">
                                <i class="fas {{ $iconClass }} small"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $adj->product->name ?? 'Unknown' }}</div>
                                <div class="small text-muted">{{ $adj->created_at->format('M d, h:i A') }} &bull; {{ $adj->user->name ?? 'Sys' }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold {{ $adj->quantity > 0 ? 'text-success' : 'text-danger' }} fs-6">
                                {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }}
                            </div>
                            <span class="badge {{ $badgeClass }} rounded-pill" style="font-size: 0.65rem;">{{ ucfirst($adj->type) }}</span>
                        </div>
                    </div>
                    @if($adj->remarks)
                        <div class="bg-light rounded p-2 mt-2 ms-5 small text-muted">
                            <i class="fas fa-quote-left me-1 opacity-25"></i> {{ $adj->remarks }}
                        </div>
                    @endif
                </li>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-history fa-3x mb-3 text-light-gray opacity-25"></i><br>
                    No history found.
                </div>
                @endforelse
            @endif
        </ul>
    </div>

    @if(isset($adjustments) && $adjustments->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $adjustments->links() }}
    </div>
    @endif
</div>
@endsection