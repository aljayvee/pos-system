@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center justify-content-between mt-4 mb-4">
        <h1 class="h2 mb-0 text-gray-800">Edit Supplier</h1>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Supplier Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ $supplier->name }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contact Information</label>
                    <textarea name="contact_info" class="form-control" rows="3">{{ $supplier->contact_info }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light text-secondary">Cancel</a>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="fas fa-save me-1"></i> Update Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection