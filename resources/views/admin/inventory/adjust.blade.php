@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    {{-- HEADER --}}
    <div class="mb-4">
        <a href="{{ route('inventory.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold mb-3">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <h4 class="fw-bold text-dark mb-1">Stock Adjustment</h4>
        <p class="text-muted small mb-0">Manually correct stock levels for damages, loss, or internal use.</p>
    </div>

    <div class="row g-4">
        {{-- ADJUSTMENT FORM --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-header bg-warning bg-opacity-10 text-dark fw-bold py-3 border-bottom-0">
                    <i class="fas fa-pen me-2 text-warning"></i>Record Adjustment
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
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
                            <select name="product_id" class="form-select bg-light border-0 select2" required>
                                <option value="">-- Search Item --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (Qty: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Action Type</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-sub" value="subtract" checked>
                                    <label class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3 rounded-4 shadow-sm border-2" for="type-sub">
                                        <i class="fas fa-minus-circle fa-2x mb-2"></i>
                                        <span class="fw-bold">Remove</span>
                                        <small class="opacity-75" style="font-size: 0.7rem">(Loss/Damage)</small>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-add" value="add">
                                    <label class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3 rounded-4 shadow-sm border-2" for="type-add">
                                        <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                        <span class="fw-bold">Add Stock</span>
                                        <small class="opacity-75" style="font-size: 0.7rem">(Correction)</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Quantity</label>
                            <input type="number" name="quantity" class="form-control bg-light border-0" min="1" placeholder="0" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Reason</label>
                            <select name="reason" class="form-select bg-light border-0" required>
                                <option value="Spoilage/Expired">Spoilage / Expired</option>
                                <option value="Damaged">Damaged Item</option>
                                <option value="Theft/Lost">Theft / Lost</option>
                                <option value="Internal Use">Internal Use (Consumed)</option>
                                <option value="Inventory Correction">Inventory Count Correction</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Remarks</label>
                            <textarea name="remarks" class="form-control bg-light border-0" rows="3" placeholder="Optional details..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-3 fw-bold shadow-sm rounded-pill text-uppercase tracking-wide">
                            <i class="fas fa-save me-2"></i> Save Adjustment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RECENT LOGS --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-history me-2 text-primary"></i>Recent Adjustments</h5>
                </div>
                
                {{-- Desktop Table --}}
                <div class="card-body p-0 d-none d-md-block">
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
                                        <span class="fw-bold text-dark d-block">{{ $adj->product->name ?? 'Unknown' }}</span>
                                        <div class="d-flex align-items-center mt-1">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width:20px; height:20px;">
                                                <i class="fas fa-user text-secondary" style="font-size:0.6rem;"></i>
                                            </div>
                                            <span class="small text-muted">{{ $adj->user->name ?? 'System' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">{{ $adj->type }}</span>
                                        @if($adj->remarks)
                                            <div class="small text-muted mt-1 text-truncate" style="max-width: 200px;">{{ $adj->remarks }}</div>
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
                                    <td colspan="4" class="text-center py-5 text-muted">No adjustments recorded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile List View --}}
                <div class="card-body p-3 d-md-none bg-light">
                    @forelse($adjustments as $adj)
                    <div class="card border-0 shadow-sm mb-3 rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">{{ $adj->created_at->format('M d, h:i A') }}</span>
                                <span class="badge bg-light text-dark border rounded-pill">{{ $adj->type }}</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">{{ $adj->product->name ?? 'Unknown' }}</h6>
                             <div class="d-flex justify-content-between align-items-center mt-3 bg-light rounded-3 p-2">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:24px; height:24px;">
                                        <i class="fas fa-user-circle text-secondary" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <small class="text-muted">{{ $adj->user->name ?? 'System' }}</small>
                                </div>
                                <span class="fw-bold fs-5 {{ $adj->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">No adjustments yet</div>
                    @endforelse
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