@extends('admin.layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-cogs text-secondary"></i> System Settings</h2>

    <div class="row">
        {{-- LEFT COLUMN: Configuration Forms --}}
        <div class="col-md-8">
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                
                {{-- 1. STORE PROFILE --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-store me-2"></i> Store Profile (Receipt Header)
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Store Name</label>
                            <input type="text" name="store_name" class="form-control" 
                                   value="{{ $settings['store_name'] ?? 'My Sari-Sari Store' }}" placeholder="Enter store name">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Store Address</label>
                                <input type="text" name="store_address" class="form-control" 
                                       value="{{ $settings['store_address'] ?? 'City, Province' }}" placeholder="Street, Brgy, City">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contact Number</label>
                                <input type="text" name="store_contact" class="form-control" 
                                       value="{{ $settings['store_contact'] ?? '' }}" placeholder="0912-345-6789">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Receipt Footer Message</label>
                            <input type="text" name="receipt_footer" class="form-control" 
                                   value="{{ $settings['receipt_footer'] ?? 'Thank you for your purchase!' }}">
                        </div>
                    </div>
                </div>

                {{-- 2. NEW: BIR / GOVERNMENT COMPLIANCE --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-file-contract me-2"></i> BIR / Government Compliance (Tax)
                    </div>
                    <div class="card-body">
                        {{-- Toggle Switch --}}
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_tax" value="0">
                            <input class="form-check-input" type="checkbox" id="taxSwitch" name="enable_tax" value="1" 
                                {{ ($settings['enable_tax'] ?? '0') == '1' ? 'checked' : '' }}
                                onchange="toggleTaxFields()">
                            <label class="form-check-label fw-bold" for="taxSwitch">Enable VAT & BIR Details on Receipt</label>
                        </div>

                        <div id="tax-fields" style="display: {{ ($settings['enable_tax'] ?? '0') == '1' ? 'block' : 'none' }};">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">TIN (Tax Identification Number)</label>
                                    <div class="input-group">
                                        <input type="password" name="store_tin" id="store_tin" class="form-control" 
                                               value="{{ $settings['store_tin'] ?? '' }}" placeholder="000-000-000-000">
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleVisibility('store_tin')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-danger small">* Hidden for security</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Business Permit / DTI No.</label>
                                    <div class="input-group">
                                        <input type="password" name="business_permit" id="business_permit" class="form-control" 
                                               value="{{ $settings['business_permit'] ?? '' }}" placeholder="Permit Number">
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleVisibility('business_permit')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-danger small">* Hidden for security</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">VAT Rate (%)</label>
                                    <input type="number" name="tax_rate" class="form-control" 
                                           value="{{ $settings['tax_rate'] ?? '12' }}" min="0" max="100">
                                    <div class="form-text">Standard PH VAT is 12%</div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold small">Tax Type</label>
                                    <select name="tax_type" class="form-select">
                                        <option value="inclusive" {{ ($settings['tax_type'] ?? '') == 'inclusive' ? 'selected' : '' }}>VAT Inclusive (Price already includes Tax)</option>
                                        <option value="exclusive" {{ ($settings['tax_type'] ?? '') == 'exclusive' ? 'selected' : '' }}>VAT Exclusive (Add Tax to Total)</option>
                                        <option value="non_vat" {{ ($settings['tax_type'] ?? '') == 'non_vat' ? 'selected' : '' }}>Non-VAT Registered</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. LOYALTY PROGRAM --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-star me-2"></i> Loyalty Program
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_loyalty" value="0">
                            <input class="form-check-input" type="checkbox" id="loyaltySwitch" name="enable_loyalty" value="1" 
                                {{ ($settings['enable_loyalty'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="loyaltySwitch">Enable Points & Rewards</label>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Earning Rule</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">1 Point per ₱</span>
                                    <input type="number" name="loyalty_ratio" class="form-control" min="1" 
                                           value="{{ $settings['loyalty_ratio'] ?? '100' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Redemption Value</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">1 Point = ₱</span>
                                    <input type="number" step="0.01" name="points_conversion" class="form-control" 
                                           value="{{ $settings['points_conversion'] ?? '1.00' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. FEATURES & TOGGLES --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-toggle-on me-2"></i> Features & Toggles
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_barcode" value="0">
                            <input class="form-check-input" type="checkbox" id="barcodeSwitch" name="enable_barcode" value="1" 
                                {{ ($settings['enable_barcode'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="barcodeSwitch">Enable Barcode Printing</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_tithes" value="0">
                            <input class="form-check-input" type="checkbox" id="tithesSwitch" name="enable_tithes" value="1" 
                                {{ ($settings['enable_tithes'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="tithesSwitch">Enable Tithes Calculation (10%)</label>
                        </div>

                        <hr>

                        {{-- PAYMONGO INTEGRATION --}}
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_paymongo" value="0">
                            <input class="form-check-input" type="checkbox" id="paymongoSwitch" name="enable_paymongo" value="1" 
                                {{ ($settings['enable_paymongo'] ?? '0') == '1' ? 'checked' : '' }} 
                                onchange="togglePaymongoFields()">
                            <label class="form-check-label fw-bold text-success" for="paymongoSwitch">
                                <i class="fas fa-wallet me-1"></i> Enable Online Payment (PayMongo)
                            </label>
                        </div>

                        <div id="paymongo-fields" style="display: {{ ($settings['enable_paymongo'] ?? '0') == '1' ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">PayMongo Secret Key</label>
                                <input type="password" name="paymongo_secret_key" class="form-control form-control-sm" 
                                       value="{{ $settings['paymongo_secret_key'] ?? '' }}" placeholder="sk_test_...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">PayMongo Public Key</label>
                                <input type="text" name="paymongo_public_key" class="form-control form-control-sm" 
                                       value="{{ $settings['paymongo_public_key'] ?? '' }}" placeholder="pk_test_...">
                            </div>
                        </div>

                        <hr>
                        {{-- MULTI-STORE TOGGLE --}}
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_multi_store" value="0">
                            <input class="form-check-input" type="checkbox" id="multiStoreSwitch" name="enable_multi_store" value="1" 
                                {{ ($settings['enable_multi_store'] ?? '0') == '1' ? 'checked' : '' }}
                                onchange="toggleStoreManagement()">
                            <label class="form-check-label fw-bold text-primary" for="multiStoreSwitch">
                                <i class="fas fa-network-wired me-1"></i> Enable Multi-Store / Branches
                            </label>
                        </div>

                        <div id="store-management-link" class="mb-3 ps-4" style="display: {{ ($settings['enable_multi_store'] ?? '0') == '1' ? 'block' : 'none' }};">
                            <a href="{{ route('stores.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-store-alt me-1"></i> Manage Stores & Branches
                            </a>
                        </div>

                    </div>
                </div>

                <div class="d-grid mb-5">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold">
                        <i class="fas fa-save me-2"></i> Save All Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- RIGHT COLUMN: Data Management --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-database me-1"></i> Data Management
                </div>
                <div class="card-body">
                    <p class="small text-muted">Create a backup or restore data.</p>
                    <div class="d-grid mb-3">
                        <a href="{{ route('settings.backup') }}" class="btn btn-outline-dark">
                            <i class="fas fa-download me-2"></i> Download Backup (.sql)
                        </a>
                    </div>
                    <hr>
                    <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data" 
                          onsubmit="return confirm('WARNING: This will WIPE all current data. Continue?');">
                        @csrf
                        <label class="form-label fw-bold text-danger small">Restore Database</label>
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
    </div>
</div>

<script>
    function togglePaymongoFields() {
        const isChecked = document.getElementById('paymongoSwitch').checked;
        document.getElementById('paymongo-fields').style.display = isChecked ? 'block' : 'none';
    }

    function toggleStoreManagement() {
        const isChecked = document.getElementById('multiStoreSwitch').checked;
        document.getElementById('store-management-link').style.display = isChecked ? 'block' : 'none';
    }

    function toggleTaxFields() {
        const isChecked = document.getElementById('taxSwitch').checked;
        document.getElementById('tax-fields').style.display = isChecked ? 'block' : 'none';
    }

    function toggleVisibility(id) {
        const input = document.getElementById(id);
        if (input.type === "password") {
            // Simple check: Ask user to confirm they are the admin
            // For stricter security, you would verify this via AJAX, 
            // but this prevents accidental display.
            if(confirm("Display sensitive BIR information?")) {
                input.type = "text";
            }
        } else {
            input.type = "password";
        }
    }
</script>
@endsection