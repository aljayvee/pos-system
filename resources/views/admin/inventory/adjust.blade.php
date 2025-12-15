@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center justify-content-between mt-4 mb-4">
        <h1 class="h2 mb-0"><i class="fas fa-clipboard-check text-warning me-2"></i>Stock Adjustment</h1>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-4">
        {{-- ADJUSTMENT FORM --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-warning text-dark fw-bold py-3">
                    <i class="fas fa-pen me-1"></i> Record Adjustment
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger small">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('inventory.storeAdjustment') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Select Product</label>
                            <select name="product_id" class="form-select select2" required>
                                <option value="">-- Search Item --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (Current: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Action Type</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-sub" value="subtract" checked>
                                    <label class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="type-sub">
                                        <i class="fas fa-minus-circle fa-lg mb-2"></i>
                                        <span>Remove</span>
                                        <small style="font-size: 0.7rem">(Loss/Damage)</small>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-add" value="add">
                                    <label class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" for="type-add">
                                        <i class="fas fa-plus-circle fa-lg mb-2"></i>
                                        <span>Add Stock</span>
                                        <small style="font-size: 0.7rem">(Correction/Found)</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Quantity</label>
                            <input type="number" name="quantity" class="form-control form-control-lg" min="1" placeholder="0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Reason</label>
                            <select name="reason" class="form-select" required>
                                <option value="Spoilage/Expired">Spoilage / Expired</option>
                                <option value="Damaged">Damaged Item</option>
                                <option value="Theft/Lost">Theft / Lost</option>
                                <option value="Internal Use">Internal Use (Consumed)</option>
                                <option value="Inventory Correction">Inventory Count Correction</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Optional details..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold shadow-sm">
                            <i class="fas fa-save me-1"></i> Save Adjustment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RECENT LOGS --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-secondary"><i class="fas fa-clock me-2"></i>Recent Adjustments</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Product</th>
                                    <th>Reason</th>
                                    <th class="text-end pe-3">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adjustments as $adj)
                                <tr>
                                    <td class="ps-3 text-muted small" style="min-width: 100px">
                                        {{ $adj->created_at->format('M d, Y') }}<br>
                                        {{ $adj->created_at->format('h:i A') }}
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark d-block">{{ $adj->product->name ?? 'Unknown' }}</span>
                                        <span class="small text-muted"><i class="fas fa-user-circle me-1"></i>{{ $adj->user->name ?? 'System' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $adj->type }}</span>
                                        @if($adj->remarks)
                                            <div class="small text-muted mt-1 text-truncate" style="max-width: 150px;">{{ $adj->remarks }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <span class="badge rounded-pill {{ $adj->quantity < 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} px-3 py-2">
                                            {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No adjustments recorded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    {{ $adjustments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection