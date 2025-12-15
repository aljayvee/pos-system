{{-- FIXED: Using $credit->credit_id instead of $credit->id --}}
<div class="modal fade" id="payCreditModal-{{ $credit->credit_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('credits.pay', $credit->credit_id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Record Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4 bg-light p-3 rounded">
                        <div>
                            <small class="text-muted text-uppercase d-block fw-bold">Customer</small>
                            <span class="fs-5 text-dark fw-bold">{{ $credit->customer->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted text-uppercase d-block fw-bold">Balance Due</small>
                            <span class="fs-4 text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Amount</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">₱</span>
                            <input type="number" name="amount" class="form-control border-start-0 fw-bold text-success" 
                                   max="{{ $credit->remaining_balance }}" step="0.01" required 
                                   placeholder="0.00">
                        </div>
                        <div class="form-text small">Enter amount collected from customer.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid via Gcash, Partial payment"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">Confirm Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>