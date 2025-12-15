<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-file-csv me-2"></i>Import Products</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3">
                        <small class="text-muted d-block fw-bold mb-1">CSV Format Required:</small>
                        <code class="text-dark">Name, Category, Price, Stock, SKU</code>
                    </div>
                    <label class="form-label fw-bold">Select CSV File</label>
                    <input type="file" name="csv_file" class="form-control form-control-lg" required accept=".csv">
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>