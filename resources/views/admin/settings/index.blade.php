@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="fas fa-cogs"></i> System Settings</h1>

    <div class="row">
        <div class="col-md-8">
            <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-database me-1"></i> Data Management
                </div>
                <div class="card-body">
                    <p class="small text-muted">Create a full backup of your store's data or restore from a previous file.</p>
                    
                    <div class="d-grid mb-3">
                        <a href="{{ route('settings.backup') }}" class="btn btn-outline-dark">
                            <i class="fas fa-download me-2"></i> Download Backup (.sql)
                        </a>
                    </div>

                    <hr>

                    <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data" 
                          onsubmit="return confirm('WARNING: This will WIPE all current data and replace it with the backup. Continue?');">
                        @csrf
                        <label class="form-label fw-bold text-danger small">Restore Data</label>
                        <input type="file" name="backup_file" class="form-control form-control-sm mb-2" required accept=".sql">
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-upload me-1"></i> Upload & Restore
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    General Configuration
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Store Name</label>
                            <input type="text" name="store_name" class="form-control" 
                                   value="{{ $settings['store_name'] ?? 'My Store' }}">
                        </div>

                        <hr class="my-4">
                    

                        <h5 class="text-warning"><i class="fas fa-star me-1"></i> Loyalty Program</h5>
                        
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_loyalty" value="0">
                            <input class="form-check-input" type="checkbox" id="loyaltySwitch" name="enable_loyalty" value="1" 
                                {{ ($settings['enable_loyalty'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="loyaltySwitch">Enable Points & Rewards</label>
                        </div>

                        {{-- NEW: Earning Ratio --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Earning Rule</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">Customer earns 1 Point for every ₱</span>
                                    <input type="number" name="loyalty_ratio" class="form-control" min="1" 
                                           value="{{ $settings['loyalty_ratio'] ?? '100' }}">
                                </div>
                                <div class="form-text">Example: Enter 100 to give 1 point per ₱100 spent.</div>
                            </div>
                            
                            {{-- Existing: Points Value --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Redemption Value</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">1 Point = ₱</span>
                                    <input type="number" step="0.01" name="points_conversion" class="form-control" 
                                           value="{{ $settings['points_conversion'] ?? '1.00' }}">
                                </div>
                                <div class="form-text">Discount value when redeeming points.</div>
                            </div>
                        </div>
                         <hr class="my-4">
                        <h5 class="text-primary"><i class="fas fa-hand-holding-heart me-1"></i> Tithes Calculation (10%)</h5>
                        <p class="text-muted small">Automatically calculate 10% of daily sales for tithes (Seventh-Day Adventist).</p>

                        <div class="form-check form-switch mb-3">
                            {{-- Hidden input ensures '0' is sent if unchecked --}}
                            <input type="hidden" name="enable_tithes" value="0">
                            <input class="form-check-input" type="checkbox" id="tithesSwitch" name="enable_tithes" value="1" 
                                {{ ($settings['enable_tithes'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="tithesSwitch">Enable Tithes Calculation</label>
                        </div>

                        <hr class="my-4">

                        <h5 class="text-secondary"><i class="fas fa-boxes me-1"></i> Product Features</h5>
                        
                        {{-- Barcode Toggle (Default Off) --}}
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_barcode" value="0">
                            <input class="form-check-input" type="checkbox" id="barcodeSwitch" name="enable_barcode" value="1" 
                                {{ ($settings['enable_barcode'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="barcodeSwitch">Enable Barcode Label Printing</label>
                            <div class="form-text">Allows generating printable barcode stickers for products.</div>
                        </div>

                        
                        
                        <hr class="my-4">

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection