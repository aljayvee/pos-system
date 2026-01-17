@extends('admin.layout')

@section('content')
    <div class="container-fluid px-2 py-3 px-md-4 py-md-4">
        {{-- MOBILE HEADER --}}
        <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3 mb-3"
            style="top: 0;">
            <a href="{{ route('inventory.index') }}" class="text-dark"><i class="fas fa-arrow-left"></i></a>
            <h6 class="m-0 fw-bold text-dark">Stock Adjustment</h6>
            <div style="width: 40px;"></div>
        </div>

        {{-- HEADER --}}
        <div class="mb-4 d-none d-lg-block">
            <a href="{{ route('inventory.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold mb-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <h4 class="fw-bold text-dark mb-1">Stock Adjustment</h4>
            <p class="text-muted small mb-0">Manually correct stock levels for damages, loss, or internal use.</p>
        </div>

        <div class="row g-4 mb-5 pb-5 mb-lg-0 pb-lg-0">
            {{-- ADJUSTMENT FORM --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 rounded-4">
                    <div
                        class="card-header bg-warning bg-opacity-10 text-dark fw-bold py-3 border-bottom-0 d-none d-lg-block">
                        <i class="fas fa-pen me-2 text-warning"></i>Record Adjustment
                    </div>
                    <div class="card-body p-4">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0"
                                role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger rounded-3 shadow-sm border-0 small">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('inventory.storeAdjustment') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Select Product</label>
                                <select name="product_id" class="form-select bg-light border-0 select2 py-3" required>
                                    <option value="">-- Search Item --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} (Qty: {{ $p->stock }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Action Type (Preferred)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="type" id="type-sub" value="subtract"
                                            checked>
                                        <label
                                            class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3 rounded-4 shadow-sm border-2"
                                            for="type-sub">
                                            <i class="fas fa-minus-circle fa-2x mb-2"></i>
                                            <span class="fw-bold">Remove</span>
                                            <small class="opacity-75" style="font-size: 0.7rem">(Loss/Damage)</small>
                                        </label>
                                    </div>

                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Quantity</label>
                                <input type="number" name="quantity" class="form-control bg-light border-0 py-3 fw-bold"
                                    min="1" placeholder="0" required inputmode="numeric">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Reason</label>
                                <select name="reason" class="form-select bg-light border-0 py-3" required>
                                    <option value="Spoilage/Expired">Spoilage / Expired</option>
                                    <option value="Damaged">Damaged Item</option>
                                    <option value="Theft/Lost">Theft / Lost</option>
                                    <option value="Internal Use">Internal Use (Consumed)</option>
                                    <option value="Inventory Correction">Inventory Count Correction</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Remarks</label>
                                <textarea name="remarks" class="form-control bg-light border-0" rows="3"
                                    placeholder="Optional details..."></textarea>
                            </div>

                            <div class="d-none d-lg-block">
                                <button type="submit"
                                    class="btn btn-dark w-100 py-3 fw-bold shadow-sm rounded-pill text-uppercase tracking-wide">
                                    <i class="fas fa-save me-2"></i> Save Adjustment
                                </button>
                            </div>

                            {{-- MOBILE STATIC BUTTON --}}
                            <div class="d-lg-none mt-4 mb-5 pb-5">
                                <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill fw-bold shadow-sm">
                                    Save Adjustment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- RECENT LOGS --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-history me-2 text-primary"></i>Recent
                            Adjustments</h5>
                    </div>

                    {{-- Desktop Table --}}
                    <div class="card-body p-0 d-none d-lg-block">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary text-uppercase small">
                                    <tr>
                                        <th class="ps-4 py-3">Date</th>
                                        <th class="py-3">Product</th>
                                        <th class="py-3">Reason</th>
                                        <th class="text-end pe-4 py-3">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($adjustments as $adj)
                                        <tr>
                                            <td class="ps-4 text-muted small" style="min-width: 100px">
                                                {{ $adj->created_at->format('M d, Y') }}<br>
                                                {{ $adj->created_at->format('h:i A') }}
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-bold text-dark d-block">{{ $adj->product->name ?? 'Unknown' }}</span>
                                                <div class="d-flex align-items-center mt-1">
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2"
                                                        style="width:20px; height:20px;">
                                                        <i class="fas fa-user text-secondary" style="font-size:0.6rem;"></i>
                                                    </div>
                                                    <span class="small text-muted">{{ $adj->user->name ?? 'System' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-light text-dark border px-3 py-2 rounded-pill">{{ $adj->type }}</span>
                                                @if($adj->remarks)
                                                    <div class="small text-muted mt-1 text-truncate" style="max-width: 200px;">
                                                        {{ $adj->remarks }}</div>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="fw-bold {{ $adj->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">No adjustments recorded yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Mobile List View --}}
                    <div class="d-lg-none">
                        <ul class="list-group list-group-flush">
                            @forelse($adjustments as $adj)
                                <li class="list-group-item p-3 border-bottom-0 hover-bg-light">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $iconClass = match (strtolower($adj->type)) {
                                                    'wastage', 'spoilage/expired', 'damage', 'theft/lost' => 'fa-trash-alt text-danger',
                                                    'internal use' => 'fa-clipboard-check text-warning',
                                                    default => 'fa-box-open text-primary',
                                                };
                                                $txtColor = match (strtolower($adj->type)) {
                                                    'wastage', 'spoilage/expired', 'damage', 'theft/lost' => 'text-danger',
                                                    'internal use' => 'text-warning',
                                                    default => 'text-primary',
                                                };
                                            @endphp
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center {{ $txtColor }}"
                                                style="width: 32px; height: 32px;">
                                                <i class="fas {{ $iconClass }} small"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                    {{ $adj->product->name ?? 'Unknown' }}</div>
                                                <div class="small text-muted">{{ $adj->created_at->format('M d, h:i A') }}</div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold {{ $adj->quantity > 0 ? 'text-success' : 'text-danger' }} fs-6">
                                                {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }}
                                            </div>
                                            <span class="badge bg-light text-secondary border rounded-pill"
                                                style="font-size: 0.65rem;">{{ ucfirst($adj->type) }}</span>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <div class="text-center py-4 text-muted">No adjustments yet</div>
                            @endforelse
                        </ul>
                    </div>

                    @if($adjustments->hasPages())
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-center py-3">
                            {{ $adjustments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection