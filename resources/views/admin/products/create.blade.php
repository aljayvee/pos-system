@extends('admin.layout')

@section('content')
{{-- External Library for Barcode --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<style>
    #reader { width: 100%; border-radius: 12px; overflow: hidden; background: black; }
    @media (max-width: 768px) {
        #scanModal .modal-dialog { margin: 0; max-width: 100%; height: 100%; }
        #scanModal .modal-content { height: 100%; border-radius: 0; }
        #reader { height: 60vh; object-fit: cover; }
    }
</style>

<div class="container-fluid px-0 px-md-4 py-0 py-md-4 bg-light h-100">
    
    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex justify-content-between align-items-center mb-4 pt-4">
        <div>
            <h3 class="fw-bold text-dark m-0 tracking-tight">Add New Product</h3>
            <p class="text-muted small m-0">Create a new item in your inventory.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3">
        <a href="{{ route('products.index') }}" class="text-secondary fw-bold text-decoration-none small">Cancel</a>
        <h6 class="m-0 fw-bold text-dark">New Product</h6>
        <div style="width: 40px;"></div> {{-- Spacer --}}
    </div>

    <form id="addProductForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="pb-5 mb-5 pb-lg-0 mb-lg-0">
        @csrf

        <div class="row g-0 g-lg-4">
            {{-- Left Column: Details --}}
            <div class="col-lg-8">
                {{-- MOBILE: Image Upload (Top) --}}
                <div class="d-lg-none bg-white p-4 text-center border-bottom mb-3">
                    <div class="position-relative d-inline-block">
                        <div class="rounded-4 d-flex align-items-center justify-content-center bg-light border" 
                            style="width: 120px; height: 120px; overflow: hidden; cursor: pointer;" onclick="document.getElementById('mobileImageInput').click()">
                            <img id="mobileImagePreview" src="#" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                            <div id="mobilePlaceholderIcon" class="text-center">
                                <i class="fas fa-camera text-secondary fa-2x opacity-50 mb-2"></i>
                                <div class="small text-muted fw-bold" style="font-size: 0.7rem;">Add Photo</div>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="mobileImageInput" name="image" class="d-none" accept="image/*" onchange="previewImage(this, 'mobileImagePreview', 'mobilePlaceholderIcon')">
                </div>

                {{-- Basic Info Card --}}
                <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 overflow-hidden mb-3 mb-lg-4 mx-0 mx-md-0">
                    <div class="card-header bg-white py-3 border-bottom d-none d-lg-block">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-box me-2 text-primary"></i>Product Details</h5>
                    </div>
                    
                    <div class="card-body p-3 p-lg-4">
                        {{-- Name --}}
                        <div class="mb-3 mb-lg-4">
                            <label class="form-label fw-bold small text-secondary d-none d-lg-block">PRODUCT NAME <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-light border-0 py-3 fw-bold text-dark fs-5" placeholder="Product Name" required>
                        </div>

                        {{-- Section: Categorization --}}
                        {{-- Section: Categorization --}}
                        <div class="row g-2 g-lg-4 mb-3 mb-lg-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-secondary d-none d-lg-block">Category</label>
                                <div class="form-floating form-floating-custom">
                                    <select name="category_id" class="form-select bg-light border-0 fw-bold" id="categorySelect">
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="categorySelect" class="d-lg-none">Category</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-secondary d-none d-lg-block">Unit (e.g. Pc, Kg, Box)</label>
                                <div class="form-floating form-floating-custom">
                                    <input type="text" name="unit" class="form-control bg-light border-0 fw-bold" list="unitOptions" placeholder="e.g. Pc" required>
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

                        {{-- DESKTOP: Image Upload --}}
                        <div class="mb-2 d-none d-lg-block mt-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Product Image</label>
                            <div class="d-flex align-items-center gap-3 p-3 border-0 bg-light rounded-4">
                                <div class="rounded-4 d-flex align-items-center justify-content-center bg-white shadow-sm" 
                                    style="width: 80px; height: 80px; overflow: hidden;">
                                    <img id="desktopImagePreview" src="#" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                    <i id="desktopPlaceholderIcon" class="fas fa-image text-secondary fa-2x opacity-50"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" id="desktopImageInput" name="image" class="form-control bg-white border-0 shadow-sm" accept="image/*" onchange="previewImage(this, 'desktopImagePreview', 'desktopPlaceholderIcon')">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing Card --}}
                <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 overflow-hidden mb-3 mb-lg-4">
                    <div class="card-header bg-white py-3 border-bottom d-none d-lg-block">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-tag me-2 text-success"></i>Pricing</h6>
                    </div>
                    <div class="card-body p-3 p-lg-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark d-none d-lg-block">Selling Price (SRP) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border-0">
                                    <span class="input-group-text bg-success bg-opacity-10 text-success fw-bold border-0 fs-5">₱</span>
                                    <input type="number" step="0.01" name="price" class="form-control bg-light border-0 fw-bold fs-4 text-dark" placeholder="0.00" required>
                                </div>
                                <div class="form-text small d-lg-none ms-1">Selling Price</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted d-none d-lg-block">Cost Price</label>
                                <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border-0">
                                    <span class="input-group-text bg-light text-muted border-0 fs-5">₱</span>
                                    <input type="number" step="0.01" name="cost" class="form-control bg-light border-0 fw-bold fs-4 text-secondary" placeholder="0.00">
                                </div>
                                <div class="form-text small d-lg-none ms-1">Cost Price (Optional)</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Include Multi-Buy Pricing Partial --}}
                @include('admin.products.partials.pricing_tiers')
            </div>

            {{-- Right Column --}}
            <div class="col-lg-4">
                {{-- Barcode Card --}}
                <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 mb-3 mb-lg-4">
                    <div class="card-body p-3 p-lg-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="fas fa-barcode me-2 text-secondary"></i> Barcode / SKU
                        </h6>
                        <div class="input-group shadow-sm rounded-3 overflow-hidden d-flex mb-3">
                            <input type="text" id="sku" name="sku" class="form-control bg-light border-0 fw-bold py-3" placeholder="Scan or type...">
                            <button type="button" class="btn btn-dark px-3" onclick="openScanner()">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Inventory Card --}}
                <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 mb-3 mb-lg-4">
                    <div class="card-header bg-white py-3 border-bottom d-none d-lg-block">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-warehouse me-2 text-warning"></i>Inventory</h5>
                    </div>
                    <div class="card-body p-3 p-lg-4">
                        <div class="row g-2">
                             <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">Stock</label>
                                <input type="number" name="stock" class="form-control bg-light border-0 p-3 fw-bold" value="0">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">Reorder Pt</label>
                                <input type="number" name="reorder_point" class="form-control bg-light border-0 p-3 fw-bold" value="0">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-secondary">Expiration</label>
                            <input type="date" name="expiration_date" class="form-control bg-light border-0 p-3">
                        </div>
                    </div>
                </div>

                {{-- DESKTOP: Submit Button --}}
                <div class="d-none d-lg-grid mt-4">
                    <button type="button" onclick="validateAndSubmit()" class="btn btn-primary btn-lg shadow-lg rounded-pill fw-bold">
                        <i class="fas fa-save me-2"></i> Save Product
                    </button>
                </div>

            </div>
        </div>

        {{-- MOBILE: Static Bottom Button --}}
        <div class="d-lg-none px-3 mt-4 mb-5">
            <button type="button" onclick="validateAndSubmit()" class="btn btn-primary w-100 rounded-pill fw-bold py-3 text-uppercase ls-1 shadow-sm">
                Save Product
            </button>
        </div>
    </form>
</div>

{{-- SCANNER MODAL (Reused logic) --}}
<div class="modal fade" id="scanModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered"> 
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title"><i class="fas fa-qrcode me-2"></i>Scan Barcode</h5>
                <button type="button" class="btn-close btn-close-white" onclick="stopScanner()"></button>
            </div>
            <div class="modal-body bg-black p-0 d-flex justify-content-center align-items-center position-relative">
                <div id="reader" style="width: 100%; min-height: 300px;"></div>
                <div class="position-absolute text-white text-center w-100 pe-none" style="bottom: 20px; z-index: 10;">
                    <small class="bg-dark bg-opacity-75 px-3 py-1 rounded-pill border border-light border-opacity-25">Align barcode within the frame</small>
                </div>
            </div>
            <div class="modal-footer p-2 justify-content-center bg-dark border-top-0">
                 <button type="button" class="btn btn-secondary w-100 rounded-pill" onclick="stopScanner()">Close Scanner</button>
            </div>
        </div>
    </div>
</div>

{{-- DUPLICATE WARNING MODAL --}}
<div class="modal fade" id="duplicateModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning border-0">
                <h5 class="modal-title text-dark fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Duplicate Found</h5>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-copy fa-3x text-warning"></i>
                </div>
                <h6 class="fw-bold" id="modalMessage"></h6>
                <p class="text-muted mb-0">This product already exists in the system. Would you like to view or restock it instead?</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" onclick="handleCancel()">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="redirectToRestock()">Go to Product</button>
            </div>
        </div>
    </div>
</div>

<script>
    let html5QrCode;
    let existingProductId = null;

    async function validateAndSubmit() {
        const nameInput = document.querySelector('input[name="name"]');
        const skuInput = document.querySelector('input[name="sku"]');
        const form = document.getElementById('addProductForm');
        
        if (!nameInput.value) { alert("Product Name is required"); return; }

        const formData = {
            name: nameInput.value,
            sku: skuInput.value,
            _token: document.querySelector('input[name="_token"]').value
        };

        // Handle Image Input Conflict (Mobile vs Desktop)
        const mobileInput = document.getElementById('mobileImageInput');
        const desktopInput = document.getElementById('desktopImageInput');

        // Reset names first to avoid confusion
        mobileInput.removeAttribute('name');
        desktopInput.removeAttribute('name');

        if (mobileInput.files.length > 0) {
            mobileInput.setAttribute('name', 'image');
        } else if (desktopInput.files.length > 0) {
            desktopInput.setAttribute('name', 'image');
        } else {
            // If neither has a file, default to desktop (or whichever) so backend sees 'image' => null
            desktopInput.setAttribute('name', 'image');
        }

        // Select buttons
        const desktopBtn = document.querySelector('button[onclick="validateAndSubmit()"]');
        const mobileBtn = document.querySelector('.fixed-bottom button[onclick="validateAndSubmit()"]');

        const originalDesktopText = desktopBtn ? desktopBtn.innerHTML : '';
        const originalMobileText = mobileBtn ? mobileBtn.innerHTML : '';

        // Helper to set loading state
        const setLoading = (isLoading) => {
            if (desktopBtn) {
                desktopBtn.disabled = isLoading;
                desktopBtn.innerHTML = isLoading ? '<i class="fas fa-spinner fa-spin me-2"></i> Processing...' : originalDesktopText;
            }
            if (mobileBtn) {
                mobileBtn.disabled = isLoading;
                mobileBtn.innerHTML = isLoading ? '<i class="fas fa-spinner fa-spin me-2"></i> Processing...' : originalMobileText;
            }
        };

        try {
            setLoading(true); // Start loading

            const response = await fetch("{{ route('products.check_duplicate') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify(formData)
            });
            const data = await response.json();

            if (data.exists) {
                setLoading(false); // Stop loading if error/modal
                existingProductId = data.product_id;
                document.getElementById('modalMessage').innerText = data.message;
                const modal = new bootstrap.Modal(document.getElementById('duplicateModal'));
                modal.show();
            } else {
                form.submit();
                // Don't enable buttons, let page reload
            }
        } catch (error) {
            console.error("Validation Error:", error);
            alert("Error validating product. Please try again.");
            setLoading(false); // Stop loading on error
        }
            alert("Error validating product. Please try again.");
        }
    }

    function handleCancel() {
        document.getElementById('addProductForm').reset();
        existingProductId = null;
        const modal = bootstrap.Modal.getInstance(document.getElementById('duplicateModal'));
        modal.hide();
    }

    function redirectToRestock() {
        if (existingProductId) { window.location.href = `/admin/products/${existingProductId}/edit`; }
    }
    
    function playScannerBeep() {
        const context = new (window.AudioContext || window.webkitAudioContext)();
        const osc = context.createOscillator();
        const gain = context.createGain();
        osc.connect(gain); gain.connect(context.destination);
        osc.type = "square"; osc.frequency.value = 1500; gain.gain.value = 0.1;           
        osc.start(); osc.stop(context.currentTime + 0.1); 
    }
    
    function openScanner() {
        const modal = new bootstrap.Modal(document.getElementById('scanModal'));
        modal.show();
        if (!html5QrCode) { html5QrCode = new Html5Qrcode("reader"); }
        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 150 } }, 
            (decodedText) => { playScannerBeep(); document.getElementById('sku').value = decodedText; stopScanner(); }
        ).catch(err => { alert("Camera access failed."); stopScanner(); });
    }

    function stopScanner() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => { html5QrCode.clear(); bootstrap.Modal.getInstance(document.getElementById('scanModal')).hide(); })
            .catch(() => { bootstrap.Modal.getInstance(document.getElementById('scanModal')).hide(); });
        }
    }

    function previewImage(input, previewId, placeholderId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { preview.src = e.target.result; preview.style.display = 'block'; placeholder.style.display = 'none'; }
            reader.readAsDataURL(input.files[0]);
        } else { preview.style.display = 'none'; placeholder.style.display = 'block'; }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const nameInput = document.querySelector('input[name="name"]');
        if(nameInput){
            nameInput.addEventListener('input', function(e) {
                let words = this.value.split(' ');
                for (let i = 0; i < words.length; i++) {
                    if (words[i].length > 0) words[i] = words[i][0].toUpperCase() + words[i].substr(1);
                }
                this.value = words.join(' ');
            });
        }

    });
</script>
@endsection
