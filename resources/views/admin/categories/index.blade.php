@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    {{-- Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark m-0 tracking-tight">Category Management</h3>
            <p class="text-muted small m-0">Organize products into logical groups.</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill shadow-sm">
                Total: {{ $categories->count() }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Content --}}
        <div class="col-lg-8">
            
            {{-- Add New Category Card --}}
            <div class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Add New Category</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="d-flex gap-2 align-items-center">
                            <div class="position-relative flex-fill">
                                <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                                    <i class="fas fa-tag"></i>
                                </span>
                                <input type="text" name="name" class="form-control form-control-lg ps-5 bg-light border-0 shadow-sm" 
                                       placeholder="e.g. Beverages, Snacks, Electronics" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg px-4 rounded-3 shadow-lg fw-bold">
                                Add
                            </button>
                        </div>
                        @error('name')
                            <div class="text-danger small mt-2 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            </div>

            {{-- Categories List --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom border-light p-4">
                    <h6 class="fw-bold text-dark mb-0">Existing Categories</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($categories as $cat)
                        <div class="list-group-item p-3 d-flex align-items-center justify-content-between hover-bg-light transition-all">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                                    <span class="fw-bold fs-5">{{ substr($cat->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <span class="fw-bold text-dark d-block fs-6">{{ $cat->name }}</span>
                                    <span class="text-muted x-small">ID: #{{ $cat->id }}</span>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                {{-- Edit Button (Trigger Modal) --}}
                                <button type="button" class="btn btn-light text-primary btn-sm rounded-circle shadow-sm" 
                                        style="width: 36px; height: 36px;" 
                                        onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}')" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                {{-- Delete Form --}}
                                <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-light text-danger btn-sm rounded-circle shadow-sm" 
                                            style="width: 36px; height: 36px;" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-folder-open fa-3x text-muted opacity-25"></i>
                            </div>
                            <h6 class="text-muted fw-bold">No categories found</h6>
                            <p class="small text-muted">Create your first category above.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Pro Tip --}}
            <div class="card border-0 shadow-sm rounded-4 bg-primary bg-gradient text-white overflow-hidden mb-4">
                <div class="card-body p-4 position-relative">
                    <i class="fas fa-lightbulb fa-5x position-absolute top-50 end-0 translate-middle-y text-white opacity-25 me-n3"></i>
                    <h5 class="fw-bold mb-2">Did you know?</h5>
                    <p class="opacity-75 mb-0 small lh-lg">
                        You can organize products more effectively by keeping category names simple and distinct.
                    </p>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3">Quick Navigation</h6>
                    <div class="d-grid gap-2">
                         <a href="{{ route('products.index') }}" class="btn btn-light text-start p-3 d-flex align-items-center gap-3 border shadow-sm rounded-3">
                             <div class="bg-white p-2 rounded-circle shadow-sm"><i class="fas fa-box text-primary"></i></div>
                             <div><span class="d-block fw-bold text-dark small">Products</span><span class="d-block text-muted x-small">Manage catalog</span></div>
                             <i class="fas fa-chevron-right ms-auto text-muted small"></i>
                         </a>
                         <a href="{{ route('inventory.index') }}" class="btn btn-light text-start p-3 d-flex align-items-center gap-3 border shadow-sm rounded-3">
                             <div class="bg-white p-2 rounded-circle shadow-sm"><i class="fas fa-boxes text-success"></i></div>
                             <div><span class="d-block fw-bold text-dark small">Inventory</span><span class="d-block text-muted x-small">Check stock</span></div>
                             <i class="fas fa-chevron-right ms-auto text-muted small"></i>
                         </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Category Name</label>
                        <input type="text" name="name" id="editCategoryName" class="form-control form-control-lg bg-light border-0" required>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" onclick="submitEditForm()" class="btn btn-warning rounded-pill fw-bold shadow-sm px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(id, name) {
        // Set Action URL dynamically
        const form = document.getElementById('editCategoryForm');
        form.action = `/admin/categories/${id}`;
        
        // Populate Input
        document.getElementById('editCategoryName').value = name;
        
        // Show Modal
        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
    }

    function submitEditForm() {
        document.getElementById('editCategoryForm').submit();
    }
</script>

<style>
    .hover-bg-light:hover { background-color: #f8f9fa !important; }
    .x-small { font-size: 0.75rem; }
    .transition-all { transition: all 0.2s ease-in-out; }
</style>
@endsection