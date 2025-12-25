@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('suppliers.index') }}" class="text-dark"><i class="fas fa-arrow-left fa-lg"></i></a>
                <h6 class="m-0 fw-bold text-dark">Edit Supplier</h6>
            </div>
        </div>
    </div>

    <div class="row justify-content-center px-3 px-md-0 pt-3 pt-md-0">
        <div class="col-12 col-md-8 col-lg-6">
            {{-- DESKTOP HEADER --}}
            <div class="d-none d-lg-flex align-items-center justify-content-between mb-4">
                <h4 class="fw-bold text-dark mb-0">Edit Supplier</h4>
                <a href="{{ route('suppliers.index') }}" class="btn btn-light border shadow-sm rounded-pill fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-warning text-dark py-3 border-0 bg-opacity-75 d-none d-lg-block">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Details</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary text-uppercase small">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-light border-0 py-3" value="{{ $supplier->name }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary text-uppercase small">Contact Information</label>
                            <textarea name="contact_info" class="form-control bg-light border-0 rounded-3 py-3" rows="4">{{ $supplier->contact_info }}</textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-light rounded-pill px-4 fw-bold d-none d-md-block">Cancel</a>
                            <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm py-3">
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