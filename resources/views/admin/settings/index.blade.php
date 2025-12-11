@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="fas fa-cogs"></i> System Settings</h1>

    <div class="row">
        <div class="col-md-8">
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
                        
                        {{-- TOGGLE SWITCH (Off by default) --}}
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_loyalty" value="0">
                            <input class="form-check-input" type="checkbox" id="loyaltySwitch" name="enable_loyalty" value="1" 
                                {{ ($settings['enable_loyalty'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="loyaltySwitch">Enable Points & Rewards</label>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Points Value (in Pesos)</label>
                                <div class="input-group">
                                    <span class="input-group-text">1 Point = ₱</span>
                                    <input type="number" step="0.01" name="points_conversion" class="form-control" 
                                           value="{{ $settings['points_conversion'] ?? '1.00' }}">
                                </div>
                                <div class="form-text">How much discount a customer gets per point.</div>
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

                        <h5 class="text-danger"><i class="fas fa-database me-1"></i> Data Management</h5>
                        <p class="text-muted small">Download a full backup of your system to keep your data safe.</p>

                        <div class="d-flex align-items-center bg-light p-3 border rounded">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Full Database Backup (.sql)</h6>
                                <small class="text-muted">Includes Users, Products, Sales, Inventory, and Settings.</small>
                            </div>
                            <a href="{{ route('settings.backup') }}" class="btn btn-outline-danger">
                                <i class="fas fa-download me-2"></i> Download Backup
                            </a>
                        </div>
                        
                        <hr class="my-4">

                        <h5 class="text-danger"><i class="fas fa-database me-1"></i> Data Management</h5>
                        <p class="text-muted small">Manage your system data securely.</p>

                        <div class="d-flex align-items-center bg-light p-3 border rounded mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Backup Database (.sql)</h6>
                                <small class="text-muted">Download a full copy of your system data.</small>
                            </div>
                            <a href="{{ route('settings.backup') }}" class="btn btn-outline-primary">
                                <i class="fas fa-download me-2"></i> Download
                            </a>
                        </div>

                        <div class="bg-light p-3 border rounded border-danger border-start border-4">
                            <h6 class="mb-2 fw-bold text-danger">Restore Database</h6>
                            <p class="small text-muted mb-3">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Warning:</strong> Restoring will replace all current data (Products, Sales, Users) with the data in the file. This cannot be undone.
                            </p>
                            
                            <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data" 
                                  onsubmit="return confirm('CRITICAL WARNING: This will WIPE current data and replace it with the backup. Are you sure?');">
                                @csrf
                                <div class="input-group">
                                    <input type="file" name="backup_file" class="form-control" required accept=".sql">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-upload me-1"></i> Upload & Restore
                                    </button>
                                </div>
                            </form>
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