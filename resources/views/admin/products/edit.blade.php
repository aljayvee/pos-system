@extends('admin.layout')

@section('content')
<style>
    .form-label { font-weight: 500; color: #4b5563; }
</style>

<div class="container-fluid px-2 py-3 px-md-4 py-md-4" style="max-width: 1200px;">
    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('products.index') }}" class="btn btn-light border shadow-sm fw-bold mb-3">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <h4 class="fw-bold text-dark mb-1">Edit Product</h4>
        <p class="text-muted small mb-0">Updating details for: <strong>{{ $product->name }}</strong></p>
    </div>

    <form id="editProductForm" action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Left Column: Product Details --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-edit me-2"></i>Product Information</h5>
                    </div>
                    <div class="card-body pt-0">
                        {{-- Name --}}
                        <div class="mb-4">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0" value="{{ $product->name }}" required>
                        </div>

                        {{-- Category & Unit --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select form-select-lg bg-light border-0">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <select name="unit" class="form-select form-select-lg bg-light border-0" required>
                                    @foreach(['pc','pack','kg','g','l','ml','box','bottle','can'] as $u)
                                        <option value="{{ $u }}" {{ $product->unit == $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Image Upload Section --}}
                        <div class="mb-4">
                            <label class="form-label">Product Image</label>
                            <div class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-light">
                                {{-- Preview Box --}}
                                <div class="rounded-3 d-flex align-items-center justify-content-center bg-white border" 
                                    style="width: 80px; height: 80px; overflow: hidden; position: relative;">
                                    @if($product->image)
                                        <img id="imagePreview" src="{{ asset('storage/' . $product->image) }}" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
                                        <i id="placeholderIcon" class="fas fa-image text-muted fa-2x" style="display: none;"></i>
                                    @else
                                        <img id="imagePreview" src="#" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                        <i id="placeholderIcon" class="fas fa-image text-muted fa-2x"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this)">
                                    <div class="form-text text-muted">Upload to replace current image. Supported: JPG, PNG (Max 2MB)</div>
                                </div>
                            </div>
                        </div>

                        {{-- Pricing Section --}}
                        <div class="p-4 bg-primary bg-opacity-10 rounded-4 mt-4">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-tag me-2"></i>Pricing</h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold">Selling Price (SRP) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-0 text-success fw-bold">₱</span>
                                        <input type="number" step="0.01" name="price" class="form-control form-control-lg border-0 fw-bold text-success" value="{{ $product->price }}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label text-muted">Cost Price (Puhanan)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-0 text-muted">₱</span>
                                        <input type="number" step="0.01" name="cost" class="form-control form-control-lg border-0 text-muted" value="{{ $product->cost }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Inventory & Barcode --}}
            <div class="col-lg-4">
                
                {{-- Barcode Card --}}
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-barcode me-2"></i>Barcode</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <label class="form-label small text-muted">SKU / Code</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-qrcode text-muted"></i></span>
                                <input type="text" name="sku" class="form-control bg-light border-0 fw-bold" value="{{ $product->sku }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Inventory Card --}}
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 text-warning text-dark fw-bold"><i class="fas fa-warehouse me-2"></i>Inventory</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <label class="form-label">Current Stock</label>
                            <input type="number" name="stock" class="form-control bg-light border-0" value="{{ $product->stock }}">
                            <div class="form-text small text-muted">Use "Adjust Stock" tool for large audits.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reorder Point <small class="text-muted">(Low Stock Alert)</small></label>
                            <input type="number" name="reorder_point" class="form-control bg-light border-0" value="{{ $product->reorder_point }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expiration Date</label>
                            <input type="date" name="expiration_date" class="form-control bg-light border-0" 
                                   value="{{ $product->expiration_date ? $product->expiration_date->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-grid gap-2 mt-4">
                    <button type="button" onclick="validateAndUpdate({{ $product->id }})" class="btn btn-primary btn-lg shadow-sm rounded-pill fw-bold">
                        <i class="fas fa-save me-2"></i> Update Product
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill text-muted">Cancel Changes</a>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- DUPLICATE WARNING MODAL --}}
<div class="modal fade" id="duplicateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-circle me-2"></i>Duplicate Found</h5>
            </div>
            <div class="modal-body p-4 text-center">
                 <div class="mb-3">
                    <i class="fas fa-ban fa-3x text-danger opacity-50"></i>
                </div>
                <h6 id="modalMessage" class="fw-bold mb-2"></h6>
                <p class="text-muted mb-0">You cannot use this Name or SKU because it is already taken by another product.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close & Change</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-Capitalization Script
    document.querySelector('input[name="name"]').addEventListener('input', function(e) {
        let words = this.value.split(' ');
        for (let i = 0; i < words.length; i++) {
            if (words[i].length > 0) {
                words[i] = words[i][0].toUpperCase() + words[i].substr(1);
            }
        }
        this.value = words.join(' ');
    });

    // Validation Logic
    async function validateAndUpdate(currentId) {
        const name = document.querySelector('input[name="name"]').value;
        const sku = document.querySelector('input[name="sku"]').value;
        const form = document.getElementById('editProductForm');
        const csrfToken = document.querySelector('input[name="_token"]').value;

        if (!name) {
            alert("Product Name is required");
            return;
        }

        try {
            const response = await fetch("{{ route('products.check_duplicate') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                body: JSON.stringify({
                    name: name,
                    sku: sku,
                    exclude_id: currentId, // PASS ID TO EXCLUDE SELF
                    _token: csrfToken
                })
            });

            const data = await response.json();

            if (data.exists) {
                // Show Warning
                document.getElementById('modalMessage').innerText = data.message;
                const modal = new bootstrap.Modal(document.getElementById('duplicateModal'));
                modal.show();
            } else {
                // Submit if valid
                form.submit();
            }
        } catch (error) {
            console.error(error);
            // Fallback: submit anyway if check fails, let backend handle it
            form.submit();
        }
    }

    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('placeholderIcon');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection