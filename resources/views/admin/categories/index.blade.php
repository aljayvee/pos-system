@extends('admin.layout')

@section('content')
<div class="container-fluid py-4" style="max-width: 1200px;">
    
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Category Management</h4>
            <p class="text-muted small mb-0">Organize your products into logical groups.</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">Total: {{ $categories->count() }}</span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content: List & Form -->
        <div class="col-lg-8">
            
            <!-- Add New Category Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-4">
                    <label class="form-label fw-bold text-dark mb-2">Add New Category</label>
                    <form action="{{ route('categories.store') }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <div class="position-relative flex-fill">
                            <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                                <i class="fas fa-tag"></i>
                            </span>
                            <input type="text" name="name" class="form-control form-control-lg ps-5 bg-light border-0" 
                                   placeholder="e.g. Beverages, Snacks, Electronics" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-4 rounded-3 shadow-sm hover-scale transition-all">
                            <i class="fas fa-plus"></i> <span class="d-none d-sm-inline ms-1">Add</span>
                        </button>
                    </form>
                    @error('name')
                        <div class="text-danger small mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Categories List -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom border-light p-4">
                    <h6 class="fw-bold text-dark mb-0">Existing Categories</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($categories as $cat)
                        <div class="list-group-item p-3 d-flex align-items-center justify-content-between hover-bg-light transition-all border-bottom-0 border-top">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" 
                                     style="width: 42px; height: 42px;">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <div>
                                    <span class="fw-bold text-dark d-block">{{ $cat->name }}</span>
                                    <span class="text-muted small">ID: #{{ $cat->id }}</span>
                                </div>
                            </div>
                            
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-light text-danger btn-sm rounded-circle p-2 hover-bg-danger-soft transition-all" 
                                        style="width: 36px; height: 36px;" title="Delete Category">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-folder-open fa-3x text-muted opacity-25"></i>
                            </div>
                            <h6 class="text-muted">No categories found</h6>
                            <p class="small text-muted">Create your first category using the form above.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar: Info & Tips -->
        <div class="col-lg-4">
            
            <!-- Info Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-primary bg-gradient text-white overflow-hidden mb-3">
                <div class="card-body p-4 position-relative">
                    <i class="fas fa-lightbulb fa-5x position-absolute top-50 end-0 translate-middle-y text-white opacity-25 me-n3"></i>
                    <h5 class="fw-bold mb-3">Pro Tip</h5>
                    <p class="opacity-75 mb-0 small lh-lg">
                        Categories help you organize your inventory. When adding products later, you'll select these categories to group items together in reports and the cashier view.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                         <a href="{{ route('products.index') }}" class="btn btn-light text-start p-3 d-flex align-items-center gap-3 hover-shadow transition-all">
                             <i class="fas fa-box text-primary"></i>
                             <div>
                                 <span class="d-block fw-bold text-dark small">Go to Products</span>
                                 <span class="d-block text-muted x-small">Manage your items</span>
                             </div>
                             <i class="fas fa-chevron-right ms-auto text-muted small"></i>
                         </a>
                         <a href="{{ route('inventory.index') }}" class="btn btn-light text-start p-3 d-flex align-items-center gap-3 hover-shadow transition-all">
                             <i class="fas fa-boxes text-success"></i>
                             <div>
                                 <span class="d-block fw-bold text-dark small">Check Inventory</span>
                                 <span class="d-block text-muted x-small">View stock levels</span>
                             </div>
                             <i class="fas fa-chevron-right ms-auto text-muted small"></i>
                         </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.hover-scale:hover { transform: scale(1.02); }
.hover-bg-light:hover { background-color: #f8f9fa; }
.hover-bg-danger-soft:hover { background-color: rgba(220, 53, 69, 0.1); color: #dc3545 !important; }
.hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.05)!important; transform: translateY(-2px); }
.x-small { font-size: 0.75rem; }
.transition-all { transition: all 0.2s ease-in-out; }

/* Mobile Optimizations */
@media (max-width: 576px) {
    input::placeholder { font-size: 0.50rem; }
}
</style>
@endsection