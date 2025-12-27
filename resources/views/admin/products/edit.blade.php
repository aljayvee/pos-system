@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4 bg-light h-100">
    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex justify-content-between align-items-center mb-4 pt-4">
        <div>
            <h3 class="fw-bold text-dark m-0 tracking-tight">Edit Product</h3>
            <p class="text-muted small m-0">Updating details for: <strong>{{ $product->name }}</strong></p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3">
        <a href="{{ route('products.index') }}" class="text-secondary fw-bold text-decoration-none small">Cancel</a>
        <h6 class="m-0 fw-bold text-dark">Edit Product</h6>
        <div style="width: 40px;"></div>
    </div>

    <form id="editProductForm" action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="pb-5 mb-5 pb-lg-0 mb-lg-0">
        @csrf
        @method('PUT')

        <div class="row g-0 g-lg-4">
            {{-- Left Column: Product Details --}}
            <div class="col-lg-8">
                
                {{-- MOBILE: Image Upload (Top) --}}
                <div class="d-lg-none bg-white p-4 text-center border-bottom mb-3">
                    <div class="position-relative d-inline-block">
                        <div class="rounded-4 d-flex align-items-center justify-content-center bg-light border" 
                            style="width: 120px; height: 120px; overflow: hidden; cursor: pointer;" onclick="document.getElementById('mobileImageInput').click()">
                            @if($product->image)
                                <img id="mobileImagePreview" src="{{ asset('storage/' . $product->image) }}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                                <div id="mobilePlaceholderIcon" class="text-center" style="display: none;">
                                    <i class="fas fa-camera text-secondary fa-2x opacity-50 mb-2"></i>
                                    <div class="small text-muted fw-bold" style="font-size: 0.7rem;">Change Photo</div>
                                </div>
                            @else
                                <img id="mobileImagePreview" src="#" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                <div id="mobilePlaceholderIcon" class="text-center">
                                    <i class="fas fa-camera text-secondary fa-2x opacity-50 mb-2"></i>
                                    <div class="small text-muted fw-bold" style="font-size: 0.7rem;">Add Photo</div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <input type="file" id="mobileImageInput" name="image" class="d-none" accept="image/*" onchange="previewImage(this, 'mobileImagePreview', 'mobilePlaceholderIcon')">
                </div>

                {{-- Basic Info Card --}}
                <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 overflow-hidden mb-3 mb-lg-4 mx-0 mx-md-0">
                    <div class="card-header bg-warning text-dark py-3 border-bottom d-none d-lg-block">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Product Information</h5>
                    </div>
                    <div class="card-body p-3 p-lg-4">
                        {{-- Name --}}
                        <div class="mb-3 mb-lg-4">
                            <label class="form-label fw-bold small text-secondary d-none d-lg-block">PRODUCT NAME <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-light border-0 py-3 fw-bold text-dark fs-5" value="{{ $product->name }}" required>
                        </div>

                        {{-- Category & Unit --}}
                        <div class="row g-2 g-lg-4 mb-3 mb-lg-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-secondary d-none d-lg-block">Category <span class="text-danger">*</span></label>
                                <div class="form-floating form-floating-custom">
                                    <select name="category_id" class="form-select bg-light border-0 fw-bold" id="categorySelect">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="categorySelect" class="d-lg-none">Category</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-secondary d-none d-lg-block">Unit (e.g. Pc, Kg, Box)</label>
                                <div class="form-floating form-floating-custom">
                                    <input type="text" name="unit" class="form-control bg-light border-0 fw-bold" list="unitOptions" value="{{ $product->unit }}" placeholder="e.g. Pc" required>
                                    <label class="d-lg-none">Unit (e.g. Pc, Kg)</label>
                                    <datalist id="unitOptions">
                                        <option value="Pc">
                                        <option value="Pack">
                                        <option value="Box">
                                        <option value="Bottle">
                                        <option value="Can">
                                        <option value="Kg">
                                        <option value="L">
                                        <option value="Set">
                                    </datalist>
                                </div>
                            </div>
                        </div>

                        {{-- Pricing Section --}}
                        <div class="p-0 p-lg-4 bg-transparent bg-lg-light rounded-4 mt-3 mt-lg-4 mb-0 mb-lg-4 border-lg">
                            <h6 class="fw-bold mb-3 text-dark d-none d-lg-block"><i class="fas fa-tag me-2"></i>Pricing</h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold text-dark d-none d-lg-block">Selling Price (SRP) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border-0">
                                        <span class="input-group-text bg-success bg-opacity-10 text-success fw-bold border-0 fs-5">₱</span>
                                        <input type="number" step="0.01" name="price" class="form-control bg-light border-0 fw-bold fs-4 text-dark" value="{{ $product->price }}" required>
                                    </div>
                                    <div class="form-text small d-lg-none ms-1">Selling Price</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label text-muted d-none d-lg-block">Cost Price (Puhanan)</label>
                                    <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border-0">
                                        <span class="input-group-text bg-light border-0 text-muted fs-5">₱</span>
                                        <input type="number" step="0.01" name="cost" class="form-control bg-light border-0 fw-bold fs-4 text-secondary" value="{{ $product->cost }}">
                                    </div>
                                    <div class="form-text small d-lg-none ms-1">Cost Price (Optional)</div>
                                </div>
                            </div>
                        </div>

                        {{-- DESKTOP: Image Upload --}}
                        <div class="mb-2 d-none d-lg-block mt-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Product Image</label>
                            <div class="d-flex align-items-center gap-3 p-3 border-0 bg-light rounded-4">
                                <div class="rounded-4 d-flex align-items-center justify-content-center bg-white shadow-sm" 
                                    style="width: 80px; height: 80px; overflow: hidden; position: relative;">
                                    @if($product->image)
                                        <img id="desktopImagePreview" src="{{ asset('storage/' . $product->image) }}" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
                                        <i id="desktopPlaceholderIcon" class="fas fa-image text-muted fa-2x" style="display: none;"></i>
                                    @else
                                        <img id="desktopImagePreview" src="#" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                        <i id="desktopPlaceholderIcon" class="fas fa-image text-muted fa-2x"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" id="desktopImageInput" name="image" class="form-control bg-white border-0 shadow-sm" accept="image/*" onchange="previewImage(this, 'desktopImagePreview', 'desktopPlaceholderIcon')">
                                </div>
                            </div>
                        </div>
                </div>

                {{-- Include Multi-Buy Pricing Partial --}}
                @include('admin.products.partials.pricing_tiers')
            </div>

            {{-- Right Column: Inventory & Barcode --}}
            <div class="col-lg-4">
                
                {{-- Barcode Card --}}
                <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 mb-3 mb-lg-4">
                    <div class="card-header bg-white py-3 border-bottom-0 d-none d-lg-block">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-barcode me-2 text-secondary"></i>Barcode</h5>
                    </div>
                    <div class="card-body p-3 p-lg-4">
                        <div class="mb-3">
                            <label class="form-label small text-muted d-none d-lg-block">SKU / Code</label>
                            <h6 class="fw-bold mb-3 d-lg-none d-flex align-items-center">
                                <i class="fas fa-barcode me-2 text-secondary"></i> Barcode / SKU
                            </h6>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-qrcode text-muted"></i></span>
                                <input type="text" name="sku" class="form-control bg-white border-0 fw-bold py-3" value="{{ $product->sku }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Inventory Card --}}
                <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 mb-3 mb-lg-4">
                    <div class="card-header bg-white py-3 border-bottom-0 d-none d-lg-block">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-warehouse me-2 text-warning"></i>Inventory</h5>
                    </div>
                    <div class="card-body p-3 p-lg-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Current Stock</label>
                            <input type="number" name="stock" class="form-control bg-light border-0 text-muted p-3 fw-bold" value="{{ $product->stock }}" readonly disabled style="cursor: not-allowed;">
                            <div class="form-text small text-warning mt-2"><i class="fas fa-exclamation-circle me-1"></i> Stock levels are managed via <strong>Purchase History</strong> or <strong>Stock Adjustments</strong>.</div>
                        </div>
                        <div class="row g-2">
                             <div class="col-12 mb-3">
                                <label class="form-label fw-bold small text-secondary">Reorder Pt</label>
                                <input type="number" name="reorder_point" class="form-control bg-light border-0 p-3 fw-bold" value="{{ $product->reorder_point }}">
                            </div>
                            <div class="col-12 mb-0">
                                <label class="form-label fw-bold small text-secondary">Expiration</label>
                                <input type="date" name="expiration_date" class="form-control bg-light border-0 p-3" 
                                       value="{{ $product->expiration_date ? $product->expiration_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-none d-lg-grid gap-2 mt-4">
                    <button type="button" id="btn-update-desktop" onclick="validateAndUpdate({{ $product->id }})" class="btn btn-warning btn-lg shadow-lg rounded-pill fw-bold text-dark">
                        <i class="fas fa-save me-2"></i> Update Product
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill text-muted fw-bold">Cancel Changes</a>
                </div>
            </div>
        </div>

        {{-- MOBILE: Static Bottom Button --}}
        <div class="d-lg-none px-3 mt-4 mb-5">
            <button type="button" id="btn-update-mobile" onclick="validateAndUpdate({{ $product->id }})" class="btn btn-primary w-100 rounded-pill fw-bold py-3 text-uppercase ls-1 shadow-sm">
                Update Product
            </button>
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
    // Auto-Capitalization & Back Prevention
    document.addEventListener("DOMContentLoaded", function() {
        // Local Back Prevention Removed (Handled Globally in layout.blade.php)

        document.querySelector('input[name="name"]').addEventListener('input', function(e) {
            let words = this.value.split(' ');
            for (let i = 0; i < words.length; i++) {
                if (words[i].length > 0) words[i] = words[i][0].toUpperCase() + words[i].substr(1);
            }
            this.value = words.join(' ');
        });
    });

    async function validateAndUpdate(currentId) {
        const name = document.querySelector('input[name="name"]').value;
        const sku = document.querySelector('input[name="sku"]').value;
        const form = document.getElementById('editProductForm');
        const csrfToken = document.querySelector('input[name="_token"]').value;

        // Handle Image Input Conflict (Mobile vs Desktop)
        const mobileInput = document.getElementById('mobileImageInput');
        const desktopInput = document.getElementById('desktopImageInput');

        // Reset names
        mobileInput.removeAttribute('name');
        desktopInput.removeAttribute('name');

        if (mobileInput.files.length > 0) {
            mobileInput.setAttribute('name', 'image');
        } else if (desktopInput.files.length > 0) {
            desktopInput.setAttribute('name', 'image');
        } else {
            // Default to desktop if no new file selected (backend handles optional image)
            desktopInput.setAttribute('name', 'image');
        }

        if (!name) { alert("Product Name is required"); return; }
        
        // Select buttons by ID
        const desktopBtn = document.getElementById('btn-update-desktop');
        const mobileBtn = document.getElementById('btn-update-mobile');

        const originalDesktopText = desktopBtn ? desktopBtn.innerHTML : '';
        const originalMobileText = mobileBtn ? mobileBtn.innerHTML : '';

        // Helper to set loading state
        const setLoading = (isLoading) => {
            if (desktopBtn) {
                desktopBtn.disabled = isLoading;
                desktopBtn.innerHTML = isLoading ? '<i class="fas fa-spinner fa-spin me-2"></i> Submitting, please wait...' : originalDesktopText;
            }
            if (mobileBtn) {
                mobileBtn.disabled = isLoading;
                mobileBtn.innerHTML = isLoading ? '<i class="fas fa-spinner fa-spin me-2"></i> Submitting, please wait...' : originalMobileText;
            }
        };

        try {
            setLoading(true); // Start loading

            const response = await fetch("{{ route('products.check_duplicate') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ name: name, sku: sku, exclude_id: currentId, _token: csrfToken })
            });

            const data = await response.json();

            if (data.exists) {
                setLoading(false); // Stop loading if error
                document.getElementById('modalMessage').innerText = data.message;
                new bootstrap.Modal(document.getElementById('duplicateModal')).show();
            } else {
                form.submit();
                // Don't enable buttons, let page reload
            }
        } catch (error) {
            console.error(error);
            form.submit(); // Submit anyway on error? Or stop? 
            // The original logic was to submit anyway. 
            // I'll keep it consistent but maybe enabling buttons is moot if it submits.
            // But if submission fails (e.g. server error not related to duplicate), 
            // the page will reload with errors anyway.
        }
    }

    function previewImage(input, previewId, placeholderId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { preview.src = e.target.result; preview.style.display = 'block'; placeholder.style.display = 'none'; }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
