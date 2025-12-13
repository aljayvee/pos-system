{{-- 
   FILE: resources/views/admin/products/create.blade.php 
   FIXES:
   1. Replaced Html5QrcodeScanner (UI) with Html5Qrcode (Pro API) for 1D precision.
   2. Added 'id="sku"' to the input field (Fixed the bug where data wouldn't populate).
   3. Added Sound Feedback (Beep).
   4. Added CSS for a proper "Scanner Look".
--}}
@extends('admin.layout')

@section('content')
{{-- External Libraries --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<style>
    /* Scanner Visuals to match Cashier View */
    #reader { width: 100%; border-radius: 8px; overflow: hidden; background: black; }
    #reader__scan_region { background: rgba(255, 255, 255, 0.1) !important; border: 2px solid #10b981 !important; }
    
    /* Mobile Fullscreen Modal */
    @media (max-width: 768px) {
        #scanModal .modal-dialog { margin: 0; max-width: 100%; height: 100%; }
        #scanModal .modal-content { height: 100%; border-radius: 0; }
        #reader { height: 60vh; object-fit: cover; }
    }
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add Product</h1>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                
                {{-- Product Name --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Product Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Bear Brand Swak" required>
                </div>

                {{-- Category & Unit --}}
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select" required>
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

                {{-- Pricing --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Selling Price (SRP)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">Cost Price (Puhunan)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" name="cost" class="form-control" placeholder="0.00">
                        </div>
                        <small class="text-muted">Required for Profit calculation.</small>
                    </div>
                </div>

                {{-- Inventory --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Initial Stock</label>
                        <input type="number" name="stock" class="form-control" value="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Reorder Point</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-bell"></i></span>
                            <input type="number" name="reorder_point" class="form-control" 
                                value="{{ old('reorder_point', $product->reorder_point ?? 10) }}" min="0">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Expiration Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" name="expiration_date" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Barcode / SKU Section --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Barcode / SKU</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-white"><i class="fas fa-barcode"></i></span>
                        {{-- ADDED ID="sku" HERE --}}
                        <input type="text" id="sku" name="sku" class="form-control form-control-lg" placeholder="Scan or enter code">
                        
                        <button type="button" class="btn btn-dark px-4" onclick="openScanner()">
                            <i class="fas fa-camera"></i> Scan
                        </button>
                    </div>
                    <div class="form-text">Click "Scan" to use your camera or use a USB scanner.</div>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i> Save Product</button>
            </form>
        </div>
    </div>
</div>

{{-- === ROBUST SCANNER MODAL === --}}
<div class="modal fade" id="scanModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered"> 
        <div class="modal-content">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title"><i class="fas fa-barcode me-2"></i>Scan Barcode</h5>
                <button type="button" class="btn-close btn-close-white" onclick="stopScanner()"></button>
            </div>
            <div class="modal-body bg-black p-0 d-flex justify-content-center align-items-center position-relative">
                <div id="reader" style="width: 100%; min-height: 300px;"></div>
                
                {{-- Overlay Guide --}}
                <div class="position-absolute text-white text-center w-100 pointer-events-none" style="bottom: 20px; z-index: 10;">
                    <small class="bg-dark bg-opacity-50 px-3 py-1 rounded-pill">Align 1D Barcode in Box</small>
                </div>
            </div>
            <div class="modal-footer p-2 justify-content-center">
                 <button type="button" class="btn btn-secondary w-100" onclick="stopScanner()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    let html5QrCode;
    // Sound Effect
    const beepSound = new Audio("data:audio/wav;base64,UklGRl9vT1BXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU");
    beepSound.src = "https://actions.google.com/sounds/v1/science_fiction/scifi_laser.ogg"; 

    function openScanner() {
        const modal = new bootstrap.Modal(document.getElementById('scanModal'));
        modal.show();

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const config = { 
            fps: 15, 
            qrbox: { width: 300, height: 150 }, // Rectangular for 1D
            aspectRatio: 1.0,
            experimentalFeatures: { useBarCodeDetectorIfSupported: true } 
        };

        html5QrCode.start(
            { facingMode: "environment" }, 
            config, 
            (decodedText) => {
                // Success
                beepSound.play();
                document.getElementById('sku').value = decodedText; // Fill Input
                stopScanner(); // Close Modal
            },
            (error) => {
                // Ignore failures (scanning...)
            }
        ).catch(err => {
            alert("Camera failed: " + err);
            stopScanner();
        });
    }

    function stopScanner() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                // Close modal manually if needed
                const modalEl = document.getElementById('scanModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }).catch(err => {
                console.log("Stop failed: ", err);
                // Force close modal anyway
                const modalEl = document.getElementById('scanModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
        } else {
            const modalEl = document.getElementById('scanModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    }
</script>
@endsection