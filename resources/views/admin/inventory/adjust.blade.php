@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-clipboard-list text-warning"></i> Stock Adjustments</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark fw-bold">
                    Record Adjustment
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success small">{{ session('success') }}</div>
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
                            <label class="form-label">Product</label>
                            <select name="product_id" class="form-select select2" required>
                                <option value="">-- Select Item --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (Cur: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="type" id="type-sub" value="subtract" checked>
                                <label class="btn btn-outline-danger w-50" for="type-sub"><i class="fas fa-minus-circle"></i> Remove (Loss)</label>

                                <input type="radio" class="btn-check" name="type" id="type-add" value="add">
                                <label class="btn btn-outline-success w-50" for="type-add"><i class="fas fa-plus-circle"></i> Add (Found)</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <select name="reason" class="form-select" required>
                                <option value="Spoilage/Expired">Spoilage / Expired</option>
                                <option value="Damaged">Damaged Item</option>
                                <option value="Theft/Lost">Theft / Lost</option>
                                <option value="Internal Use">Internal Use (Consumed)</option>
                                <option value="Inventory Correction">Inventory Count Correction</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks (Optional)</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Details..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Save Adjustment</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Adjustment History</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Reason</th>
                                <th class="text-end">Qty</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adjustments as $adj)
                            <tr>
                                <td>{{ $adj->created_at->format('M d, Y') }}</td>
                                <td class="fw-bold">{{ $adj->product->name ?? 'Unknown' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $adj->type }}</span>
                                    @if($adj->remarks)<br><small class="text-muted">{{ $adj->remarks }}</small>@endif
                                </td>
                                <td class="text-end fw-bold {{ $adj->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $adj->quantity > 0 ? '+' : '' }}{{ $adj->quantity }}
                                </td>
                                <td class="small">{{ $adj->user->name ?? 'System' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No adjustments recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $adjustments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection