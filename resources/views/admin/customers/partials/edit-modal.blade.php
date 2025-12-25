<div class="modal fade" id="editCustomerModal-{{ $customer->id }}" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="w-100">
            @csrf @method('PUT')
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-light h-100">
                
                {{-- MOBILE HEADER --}}
                <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3" style="top: 0;">
                    <button type="button" class="btn btn-link text-decoration-none text-dark fw-bold p-0" data-bs-dismiss="modal">Cancel</button>
                    <h6 class="m-0 fw-bold text-dark">Edit Customer</h6>
                    <button type="submit" class="btn btn-link text-decoration-none text-warning fw-bold p-0">Save</button>
                </div>

                {{-- DESKTOP HEADER --}}
                <div class="d-none d-lg-flex modal-header bg-warning bg-opacity-10 text-dark border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2 text-warning"></i>Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="p-3">
                        <div class="bg-white rounded-4 shadow-sm p-3 mb-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary small text-uppercase mb-1">Full Name</label>
                                <input type="text" name="name" class="form-control form-control-lg bg-light border-0 fw-bold text-dark" value="{{ $customer->name }}" required placeholder="Customer Name">
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold text-secondary small text-uppercase mb-1">Contact Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 text-muted"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="contact" class="form-control form-control-lg bg-light border-0" value="{{ $customer->contact }}" placeholder="09xxxxxxxxx">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-4 shadow-sm p-3">
                             <div class="mb-0">
                                <label class="form-label fw-bold text-secondary small text-uppercase mb-1">Address</label>
                                <textarea name="address" class="form-control bg-light border-0" rows="3" placeholder="Residential or Delivery Address">{{ $customer->address }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-3 pt-0 d-none d-lg-flex">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>