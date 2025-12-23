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

<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0 tracking-tight">Add Product</h3>
            <p class="text-muted small m-0">Create a new item in your inventory.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form id="addProductForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            {{-- Left Column: Details --}}
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-primary text-white py-3 border-0">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-box me-2"></i>Product Details</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        {{-- Name --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0" placeholder="e.g. Bear Brand Swak" required>
                        </div>

                        {{-- Category & Unit --}}
                        <div class="row g-4 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select form-select-lg bg-light border-0 select2">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Unit <span class="text-danger">*</span></label>
                                <select name="unit" class="form-select form-select-lg bg-light border-0" required>
                                    <option value="pc">Piece (pc)</option>
                                    <option value="pack">Pack</option>
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="g">Gram (g)</option>
                                    <option value="l">Liter (L)</option>
                                    <option value="ml">Milliliter (ml)</option>
                                    <option value="box">Box</option>
                                    <option value="bottle">Bottle</option>
                                    <option value="can">Can</option>
                                </select>
                            </div>
                        </div>

                        {{-- Pricing Section --}}
                        <div class="p-4 bg-primary bg-opacity-10 rounded-4 mt-4 mb-4 border border-primary border-opacity-10">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-tag me-2"></i>Pricing</h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold text-dark">Selling Price (SRP) <span class="text-danger">*</span></label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white border-0 text-success fw-bold">₱</span>
                                        <input type="number" step="0.01" name="price" class="form-control form-control-lg border-0 fw-bold text-success" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label text-muted">Cost Price (Puhanan)</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white border-0 text-muted">₱</span>
                                        <input type="number" step="0.01" name="cost" class="form-control form-control-lg border-0 text-muted" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Image Upload --}}
                        <div class="mb-2">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Product Image</label>
                            <div class="d-flex align-items-center gap-3 p-3 border-0 bg-light rounded-4">
                                <div class="rounded-4 d-flex align-items-center justify-content-center bg-white shadow-sm" 
                                    style="width: 80px; height: 80px; overflow: hidden;">
                                    <img id="imagePreview" src="#" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                    <i id="placeholderIcon" class="fas fa-image text-secondary fa-2x opacity-50"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="image" class="form-control form-control-lg bg-white border-0 shadow-sm" accept="image/*" onchange="previewImage(this)">
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
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-barcode me-2 text-secondary"></i>Barcode</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Scan or manually input code</label>
                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-qrcode text-muted"></i></span>
                                <input type="text" id="sku" name="sku" class="form-control bg-white border-0 fw-bold" placeholder="e.g. 01234567890">
                            </div>
                        </div>
                        <button type="button" class="btn btn-dark w-100 py-2 rounded-pill shadow-sm" onclick="openScanner()">
                            <i class="fas fa-camera me-2"></i> Scan Barcode
                        </button>
                    </div>
                </div>

                {{-- Inventory Card --}}
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-warehouse me-2 text-warning"></i>Inventory</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Initial Stock</label>
                            <input type="number" name="stock" class="form-control bg-light border-0" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Reorder Point</label>
                            <input type="number" name="reorder_point" class="form-control bg-light border-0" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Expiration Date</label>
                            <input type="date" name="expiration_date" class="form-control bg-light border-0">
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="d-grid mt-4">
                    <button type="button" onclick="validateAndSubmit()" class="btn btn-primary btn-lg shadow-lg rounded-pill fw-bold">
                        <i class="fas fa-save me-2"></i> Save Product
                    </button>
                </div>

            </div>
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

        try {
            const response = await fetch("{{ route('products.check_duplicate') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify(formData)
            });
            const data = await response.json();

            if (data.exists) {
                existingProductId = data.product_id;
                document.getElementById('modalMessage').innerText = data.message;
                const modal = new bootstrap.Modal(document.getElementById('duplicateModal'));
                modal.show();
            } else {
                form.submit();
            }
        } catch (error) {
            console.error("Validation Error:", error);
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

    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('placeholderIcon');
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
