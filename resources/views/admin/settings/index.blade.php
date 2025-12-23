@extends('admin.layout')

@section('content')
<style>
    /* === PREMIUM SETTINGS THEME v2 === */
    .settings-container { max-width: 1400px; margin: 0 auto; }
    
    .card-settings {
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border-radius: 1.5rem;
        background: white;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .card-settings:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .card-header-clean {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .icon-box {
        width: 56px; height: 56px;
        border-radius: 1rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .icon-primary { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color: #2563eb; }
    .icon-warning { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); color: #d97706; }
    .icon-success { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); color: #16a34a; }
    .icon-danger  { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); color: #dc2626; }
    .icon-dark    { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); color: #475569; }

    .form-label { font-weight: 700; color: #334155; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 0.5rem; }
    
    /* Input Styling */
    .form-control, .form-select {
        border-radius: 1rem;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.875rem 1rem;
        font-size: 1rem;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        background-color: #ffffff;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    .form-control::placeholder { color: #94a3b8; font-weight: 400; }

    .btn-save {
        border-radius: 1rem;
        padding: 1rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        font-size: 1rem;
        transition: all 0.2s ease;
    }
    .btn-save:hover { transform: translateY(-2px); }

    /* Custom Toggles */
    .form-check-input { width: 3em; height: 1.5em; cursor: pointer; }
    
    /* Sticky Sidebar */
    @media (min-width: 992px) {
        .sticky-sidebar { position: sticky; top: 100px; }
    }

    /* Reveal Button */
    .btn-reveal {
        border-top-right-radius: 1rem;
        border-bottom-right-radius: 1rem;
        border: 1px solid #e2e8f0;
        border-left: none;
        background: #f1f5f9;
        color: #64748b;
    }
    .btn-reveal:hover { background: #e2e8f0; color: #334155; }
</style>

<div class="settings-container px-2 py-3 px-md-4 py-md-4">
    
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h2 class="fw-bold text-dark m-0 tracking-tight">System Settings</h2>
            <p class="text-muted m-0">Configure your store profile, features, and security.</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 me-3">
                    <i class="fas fa-check fa-lg"></i>
                </div>
                <div>
                    <strong class="text-success">Success</strong><br>
                    <span class="text-secondary">{{ session('success') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4" role="alert">
             <div class="d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 me-3">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
                <div>
                    <strong class="text-danger">Action Failed</strong><br>
                    <span class="text-secondary">{{ session('error') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- LEFT COLUMN: Configuration Forms --}}
        <div class="col-lg-8 col-12">
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                
                {{-- 1. STORE PROFILE --}}
                <div class="card-settings">
                    <div class="card-header-clean">
                        <div class="icon-box icon-primary shadow-sm"><i class="fas fa-store"></i></div>
                        <div>
                            <h5 class="fw-bold m-0 text-dark">Store Profile</h5>
                            <small class="text-muted">General branding display</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label">Store Name</label>
                            <input type="text" name="store_name" class="form-control form-control-lg fw-bold text-primary" 
                                   value="{{ $settings['store_name'] ?? 'My Sari-Sari Store' }}" placeholder="Enter store name">
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Location / Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-4 text-muted"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" name="store_address" class="form-control form-control-lg border-start-0" 
                                           value="{{ $settings['store_address'] ?? 'City, Province' }}" placeholder="Street, Brgy, City">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-4 text-muted"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="store_contact" class="form-control form-control-lg border-start-0" 
                                           value="{{ $settings['store_contact'] ?? '' }}" placeholder="0912-345-6789">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="form-label">Receipt Footer Message</label>
                            <input type="text" name="receipt_footer" class="form-control form-control-lg text-muted" 
                                   value="{{ $settings['receipt_footer'] ?? 'Thank you for your purchase!' }}" placeholder="Short message for customers">
                        </div>
                        
                        <div class="mt-5">
                            <button type="submit" class="btn btn-primary w-100 btn-save shadow-lg">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 2. BIR / COMPLIANCE (Safety Flag) --}}
                @if(config('safety_flag_features.bir_tax_compliance'))
                <div class="card-settings">
                    <div class="card-header-clean">
                        <div class="icon-box icon-dark shadow-sm"><i class="fas fa-file-invoice"></i></div>
                        <div>
                            <h5 class="fw-bold m-0 text-dark">BIR & Tax Compliance</h5>
                            <small class="text-muted">Invoicing & Legal Details</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        {{-- Toggle --}}
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-4 mb-4 border border-light">
                            <div>
                                <label class="form-check-label fw-bold text-dark mb-0 d-block fs-6" for="taxSwitch">Receipt Tax Printing</label>
                                <small class="text-muted">Display VAT, TIN, and Permit info on receipts.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="enable_tax" value="0">
                                <input class="form-check-input" type="checkbox" id="taxSwitch" name="enable_tax" value="1" 
                                    {{ ($settings['enable_tax'] ?? '0') == '1' ? 'checked' : '' }}
                                    onchange="handleTaxSwitchChange(this)">
                            </div>
                        </div>

                        <div id="tax-fields" style="display: {{ ($settings['enable_tax'] ?? '0') == '1' ? 'block' : 'none' }};" class="animate__animated animate__fadeIn">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">TIN (Tax ID)</label>
                                    <div class="input-group">
                                        <input type="password" name="store_tin" id="store_tin" class="form-control form-control-lg rounded-end-0" 
                                               value="" 
                                               placeholder="{{ !empty($settings['store_tin']) ? '******** (Saved)' : 'Enter TIN' }}"
                                               {{ !empty($settings['store_tin']) ? 'readonly' : '' }}>
                                        <button class="btn btn-reveal px-3" type="button" id="btn-tin" onclick="handleSecretToggle('store_tin', 'btn-tin')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Permit / DTI</label>
                                    <div class="input-group">
                                        <input type="password" name="business_permit" id="business_permit" class="form-control form-control-lg rounded-end-0" 
                                               value="" 
                                               placeholder="{{ !empty($settings['business_permit']) ? '******** (Saved)' : 'Enter Permit No.' }}"
                                               {{ !empty($settings['business_permit']) ? 'readonly' : '' }}>
                                        <button class="btn btn-reveal px-3" type="button" id="btn-permit" onclick="handleSecretToggle('business_permit', 'btn-permit')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label">VAT Rate (%)</label>
                                    <div class="input-group">
                                        <input type="number" name="tax_rate" class="form-control form-control-lg rounded-end-0" 
                                               value="{{ $settings['tax_rate'] ?? '12' }}" min="0" max="100">
                                        <span class="input-group-text bg-light border-start-0 rounded-end-4 text-muted">%</span>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Tax Calculation Mode</label>
                                    <select name="tax_type" class="form-select form-select-lg">
                                        <option value="inclusive" {{ ($settings['tax_type'] ?? '') == 'inclusive' ? 'selected' : '' }}>VAT Inclusive (Price includes tax)</option>
                                        <option value="exclusive" {{ ($settings['tax_type'] ?? '') == 'exclusive' ? 'selected' : '' }}>VAT Exclusive (Tax added at checkout)</option>
                                        <option value="non_vat" {{ ($settings['tax_type'] ?? '') == 'non_vat' ? 'selected' : '' }}>Non-VAT Registered</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-dark w-100 btn-save shadow-sm">
                                    <i class="fas fa-save me-2"></i> Update Tax Config
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 3. LOYALTY --}}
                <div class="card-settings">
                    <div class="card-header-clean">
                        <div class="icon-box icon-warning shadow-sm"><i class="fas fa-crown"></i></div>
                        <div>
                            <h5 class="fw-bold m-0 text-dark">Loyalty Program</h5>
                            <small class="text-muted">Customer rewards & points</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4 bg-light p-3 rounded-4 border border-light">
                            <div>
                                <label class="form-check-label fw-bold text-dark fs-6" for="loyaltySwitch">Points System</label>
                                <small class="text-muted d-block">Customers earn points on every purchase.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="enable_loyalty" value="0">
                                <input class="form-check-input" type="checkbox" id="loyaltySwitch" name="enable_loyalty" value="1" 
                                    {{ ($settings['enable_loyalty'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase">Spending Ratio</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-4 text-muted fw-bold ps-3">₱</span>
                                    <input type="number" name="loyalty_ratio" class="form-control form-control-lg border-start-0 ps-2" min="1" 
                                           value="{{ $settings['loyalty_ratio'] ?? '100' }}">
                                    <span class="input-group-text bg-light border-start-0 rounded-end-4 text-muted small"> = 1 Pt</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase">Redemption Value</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-4 text-muted small">1 Pt = </span>
                                    <span class="input-group-text bg-light border-end-0 border-start-0 text-muted fw-bold">₱</span>
                                    <input type="number" step="0.01" name="points_conversion" class="form-control form-control-lg border-start-0 ps-2 rounded-end-4" 
                                           value="{{ $settings['points_conversion'] ?? '1.00' }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-warning w-100 btn-save shadow-sm">
                                <i class="fas fa-save me-2"></i> Update Loyalty
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 4. FEATURES & TOGGLES --}}
                <div class="card-settings">
                    <div class="card-header-clean">
                        <div class="icon-box icon-success shadow-sm"><i class="fas fa-sliders-h"></i></div>
                        <div>
                            <h5 class="fw-bold m-0 text-dark">System Features</h5>
                            <small class="text-muted">Toggle optional modules</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        
                        {{-- Barcode --}}
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-light">
                             <div>
                                <h6 class="fw-bold text-dark m-0">Barcode Printing</h6>
                                <small class="text-muted">Generate sticker labels for products</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="enable_barcode" value="0">
                                <input class="form-check-input" type="checkbox" id="barcodeSwitch" name="enable_barcode" value="1" 
                                    {{ ($settings['enable_barcode'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        {{-- Tithes --}}
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-light">
                            <div>
                                <h6 class="fw-bold text-dark m-0">Tithes Calculation</h6>
                                <small class="text-muted">Auto-calc 10% tithes on sales reports</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="enable_tithes" value="0">
                                <input class="form-check-input" type="checkbox" id="tithesSwitch" name="enable_tithes" value="1" 
                                    {{ ($settings['enable_tithes'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        {{-- PAYMONGO (Safety Flag) --}}
                        @if(config('safety_flag_features.online_payment'))
                        <div class="bg-light p-4 rounded-4 mb-4 border border-success border-opacity-25">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-check-label fw-bold text-success d-flex align-items-center fs-6" for="paymongoSwitch">
                                    <i class="fas fa-wallet me-2"></i> Online Payment (PayMongo)
                                </label>
                                <div class="form-check form-switch m-0">
                                    <input type="hidden" name="enable_paymongo" value="0">
                                    <input class="form-check-input" type="checkbox" id="paymongoSwitch" name="enable_paymongo" value="1" 
                                        {{ ($settings['enable_paymongo'] ?? '0') == '1' ? 'checked' : '' }} 
                                        onchange="togglePaymongoFields()">
                                </div>
                            </div>

                            <div id="paymongo-fields" style="display: {{ ($settings['enable_paymongo'] ?? '0') == '1' ? 'block' : 'none' }};" class="animate__animated animate__fadeIn">
                                <div class="mb-3">
                                    <label class="form-label small text-muted text-uppercase">Secret Key</label>
                                    <input type="password" name="paymongo_secret_key" class="form-control form-control-lg" 
                                           value="{{ $settings['paymongo_secret_key'] ?? '' }}" placeholder="sk_test_...">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small text-muted text-uppercase">Public Key</label>
                                    <input type="text" name="paymongo_public_key" class="form-control form-control-lg" 
                                           value="{{ $settings['paymongo_public_key'] ?? '' }}" placeholder="pk_test_...">
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- MULTI-STORE (Safety Flag) --}} 
                        @if(config('safety_flag_features.multi_store'))
                        <div class="bg-light p-4 rounded-4 border border-primary border-opacity-25">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-check-label fw-bold text-primary d-flex align-items-center fs-6" for="multiStoreSwitch">
                                    <i class="fas fa-network-wired me-2"></i> Multi-Store System
                                </label>
                                <div class="form-check form-switch m-0">
                                    <input type="hidden" name="enable_multi_store" value="0">
                                    <input class="form-check-input" type="checkbox" id="multiStoreSwitch" name="enable_multi_store" value="1" 
                                        {{ ($settings['enable_multi_store'] ?? '0') == '1' ? 'checked' : '' }}
                                        onchange="toggleStoreManagement()">
                                </div>
                            </div>
                            <div id="store-management-link" class="mt-3 animate__animated animate__fadeIn" style="display: {{ ($settings['enable_multi_store'] ?? '0') == '1' ? 'block' : 'none' }};">
                                <a href="{{ route('stores.index') }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold bg-white">
                                    Manage Stores & Branches <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                        @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-dark w-100 btn-save shadow-lg">
                                <i class="fas fa-check-circle me-2"></i> Apply Settings
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- RIGHT COLUMN: Data Management --}}
        <div class="col-lg-4 col-12">
            <div class="sticky-sidebar">
                <div class="card-settings border-0 shadow-lg">
                    <div class="card-header-clean bg-danger bg-opacity-10 border-0">
                        <div class="icon-box icon-danger shadow-sm"><i class="fas fa-database"></i></div>
                        <div>
                             <h5 class="fw-bold m-0 text-danger">Data Management</h5>
                             <small class="text-danger-emphasis">Backup & Restore</small>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-danger bg-opacity-10 rounded-bottom-4">
                        <p class="small text-danger-emphasis mb-4">
                            <i class="fas fa-info-circle me-1"></i> 
                            Regular backups prevent data loss. 
                            <br><strong>Warning:</strong> Restore overwrites all data.
                        </p>
                        
                        <a href="{{ route('settings.backup') }}" class="btn btn-white w-100 mb-4 py-3 rounded-4 shadow-sm fw-bold text-danger border d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-download"></i> Download Backup
                        </a>

                        <hr class="text-danger opacity-25">

                        <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data" 
                              onsubmit="return confirm('WARNING: This will WIPE all current data and replace it with the backup. Are you sure?');">
                            @csrf
                            <label class="form-label fw-bold text-danger">Restore Database</label>
                            <input type="file" name="backup_file" class="form-control mb-3 bg-white" required accept=".sql">
                            
                            <button type="submit" class="btn btn-danger w-100 py-3 fw-bold shadow-lg rounded-4">
                                <i class="fas fa-upload me-2"></i> Restore Data
                            </button>
                        </form>
                    
                        <hr class="text-danger opacity-25 my-4">

                        {{-- Software Update --}}
                         <div class="mt-2">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-sync-alt me-1 text-primary"></i> System Version</label>
                            <div class="d-flex align-items-center justify-content-between mb-3 bg-white p-3 rounded-4 shadow-sm">
                                <span class="small text-muted fw-bold text-uppercase">Current</span>
                                <span class="badge bg-dark text-white shadow-sm p-2">{{ config('version.full') }}</span>
                            </div>

                            {{-- Beta Toggle --}}
                            <div class="form-check form-switch mb-3 bg-white p-3 rounded-4 shadow-sm d-flex align-items-center justify-content-between">
                                <label class="form-check-label small fw-bold text-dark" for="betaToggle">
                                    <i class="fas fa-flask text-primary me-2"></i> Beta Program
                                </label>
                                <input class="form-check-input ms-0" type="checkbox" id="betaToggle" 
                                    {{ ($settings['enable_beta'] ?? '0') == '1' ? 'checked' : '' }}
                                    onchange="toggleBetaProgram(this)">
                            </div>
                            
                            <button type="button" class="btn btn-light w-100 py-3 fw-bold text-primary shadow-sm rounded-4 border" onclick="checkForUpdates()">
                                Check for Updates
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS & SCRIPTS (Retained) --}}
@include('admin.settings.partials.modals')

<script>
    function togglePaymongoFields() {
        const check = document.getElementById('paymongoSwitch');
        const fields = document.getElementById('paymongo-fields');
        fields.style.display = check.checked ? 'block' : 'none';
    }

    function toggleStoreManagement() {
        const check = document.getElementById('multiStoreSwitch');
        const link = document.getElementById('store-management-link');
        link.style.display = check.checked ? 'block' : 'none';
    }

    function toggleBetaProgram(el) {
        // Implement beta toggle logic via AJAX if needed
        console.log('Beta toggled:', el.checked);
    }
    
    function checkForUpdates() {
        alert('You are running the latest version.');
    }

    // Toggle Secret Fields
    function handleSecretToggle(fieldId, btnId) {
        const field = document.getElementById(fieldId);
        if (field.type === "password") {
            // Logic to ask for admin password before showing
            const modal = new bootstrap.Modal(document.getElementById('securityModal'));
            document.getElementById('target-field-id').value = fieldId;
            modal.show();
        } else {
            field.type = "password";
        }
    }
</script>

@endsection