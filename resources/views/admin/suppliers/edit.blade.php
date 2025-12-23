@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="fw-bold text-dark mb-0">Edit Supplier</h4>
                <a href="{{ route('suppliers.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-warning text-dark py-3 border-0 bg-opacity-75">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Details</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary text-uppercase small">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0" value="{{ $supplier->name }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary text-uppercase small">Contact Information</label>
                            <textarea name="contact_info" class="form-control bg-light border-0 rounded-3" rows="4">{{ $supplier->contact_info }}</textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancel</a>
                            <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection