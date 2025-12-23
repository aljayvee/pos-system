<div class="modal fade" id="editCustomerModal-{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-warning bg-opacity-10 text-dark border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2 text-warning"></i>Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Full Name</label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0" value="{{ $customer->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Contact Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 text-muted"><i class="fas fa-phone"></i></span>
                            <input type="text" name="contact" class="form-control bg-light border-0" value="{{ $customer->contact }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Address</label>
                        <textarea name="address" class="form-control bg-light border-0" rows="3">{{ $customer->address }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>