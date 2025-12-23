{{-- FIXED: Using $credit->credit_id instead of $credit->id --}}
<div class="modal fade" id="payCreditModal-{{ $credit->credit_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('credits.pay', $credit->credit_id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-success bg-opacity-10 text-dark border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2 text-success"></i>Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4 bg-light p-3 rounded-4 border border-light">
                        <div>
                            <small class="text-secondary text-uppercase d-block fw-bold small">Customer</small>
                            <span class="fs-6 text-dark fw-bold">{{ $credit->customer->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="text-end">
                            <small class="text-secondary text-uppercase d-block fw-bold small">Balance Due</small>
                            <span class="fs-4 text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Payment Amount</label>
                        <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden">
                            <span class="input-group-text bg-white border-0 fw-bold text-secondary ps-3">₱</span>
                            <input type="number" name="amount" class="form-control border-0 fw-bold text-dark" 
                                   max="{{ $credit->remaining_balance }}" step="0.01" required 
                                   placeholder="0.00">
                        </div>
                        <div class="form-text small mt-2 ms-2"><i class="fas fa-info-circle me-1"></i>Enter amount collected from customer.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Notes (Optional)</label>
                        <textarea name="notes" class="form-control bg-light border-0 rounded-3" rows="2" placeholder="e.g. Paid via Gcash, Partial payment"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">Confirm Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>