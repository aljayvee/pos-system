@extends('admin.layout')

@section('content')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add Product</h1>
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" required>
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
                
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Selling Price (SRP)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                    </div>
                    
                    {{-- NEW: Cost Price --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">Cost Price (Puhunan)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" name="cost" class="form-control">
                        </div>
                        <small class="text-muted">Required for Profit calculation.</small>
                    </div>
                </div>

               

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Initial Stock</label>
                        <input type="number" name="stock" class="form-control" value="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Reorder Point (Low Stock Alert)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-bell"></i></span>
                            <input type="number" name="reorder_point" class="form-control" 
                                value="{{ old('reorder_point', $product->reorder_point ?? 10) }}" min="0">
                        </div>
                        <small class="text-muted">System will alert when stock reaches this level.</small>
                    </div>
                </div>


                <div class="mb-3">
                    <label class="form-label">Barcode / SKU (if available)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="sku" class="form-control" placeholder="Scan or enter code">
                        
                        <button type="button" class="btn btn-outline-secondary" onclick="openScanner()">
                            <i class="fas fa-camera"></i> Scan
                        </button>
                    </div>
                    <div class="form-text">Click "Scan" to use your camera.</div>
                </div>

                <button type="submit" class="btn btn-primary">Save Product</button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="scanModal" tabindex="-1">
    <div class="modal-dialog"> <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan Barcode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="stopScanner()"></button>
            </div>
            <div class="modal-body text-center">
                <div id="reader" style="width: 100%; background-color: #f0f0f0;"></div>
               
            </div>
        </div>
    </div>
</div>
<script>
    let html5QrcodeScanner;

    function openScanner() {
        const modal = new bootstrap.Modal(document.getElementById('scanModal'));
        modal.show();

        // HIGH PERFORMANCE CONFIGURATION
        const config = { 
            fps: 20, // Increased from 10 to 20 for faster scanning
            qrbox: { width: 300, height: 150 }, // Wide box for 1D barcodes
            aspectRatio: 1.0, 
            
            // Enable UI Controls for better accuracy
            showTorchButtonIfSupported: true, // Turn on flash for blue/low-contrast codes
            showZoomSliderIfSupported: true,  // vital for long distance scanning
            defaultZoomValueIfSupported: 1.5, // slight zoom to help focus

            // STRICTLY define formats to look for product barcodes
            formatsToSupport: [ 
                Html5QrcodeSupportedFormats.UPC_A, 
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8, 
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39
            ],
            
            // Use Browser Native API (Faster & more robust for colored barcodes)
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            }
        };

        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5QrcodeScanner("reader", config, /* verbose= */ false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        // 1. Put the scanned code into the input
        document.getElementById('sku-input').value = decodedText;
        
        // 2. Close the modal
        stopScanner();
        const modalEl = document.getElementById('scanModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        modalInstance.hide();
        
        // Optional: Alert or beep
        // alert("Scanned: " + decodedText);
    }

    // --- THIS WAS MISSING ---
    function onScanFailure(error) {
        // This function is called constantly when a QR code is NOT found.
        // We leave it empty to avoid spamming the console.
        // console.warn(`Code scan error = ${error}`);
    }

    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
        }
    }
</script>
@endsection