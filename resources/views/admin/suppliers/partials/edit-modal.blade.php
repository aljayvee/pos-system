<div class="modal fade" id="editSupplierModal-{{ $supplier->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Supplier Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $supplier->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Information</label>
                        <input type="text" name="contact_info" class="form-control" value="{{ $supplier->contact_info }}">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>