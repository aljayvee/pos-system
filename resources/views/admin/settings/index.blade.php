@extends('admin.layout')

@section('content')
<style>
    /* === PREMIUM SETTINGS THEME === */
    .settings-container { max-width: 1400px; margin: 0 auto; }
    
    .card-settings {
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border-radius: 20px;
        background: white;
        margin-bottom: 2rem;
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
    }
    .card-settings:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    }

    .card-header-clean {
        background: white;
        border-bottom: 1px solid #f0f0f0;
        padding: 1.5rem 1.75rem;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .icon-box {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
        transition: all 0.3s ease;
    }
    .icon-primary { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #4f46e5; }
    .icon-warning { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #d97706; }
    .icon-success { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #16a34a; }
    .icon-danger  { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #dc2626; }
    .icon-dark    { background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); color: #1f2937; }

    .form-label { font-weight: 600; color: #4b5563; font-size: 0.9rem; margin-bottom: 0.5rem; }
    
    /* Global Input Styling */
    .form-control, .form-select {
        border-radius: 12px;
        background-color: #f8fafc;
        border: 1px solid transparent;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        background-color: #fff;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .btn-save {
        border-radius: 12px;
        padding: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        font-size: 1rem;
    }

    /* Sticky Sidebar for Desktop */
    @media (min-width: 992px) {
        .sticky-sidebar { position: sticky; top: 100px; }
    }

    /* Secret Toggle Buttons */
    .btn-reveal {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
        border: 1px solid transparent;
        background: #f1f5f9;
        color: #64748b;
    }
    .btn-reveal:hover { background: #e2e8f0; color: #334155; }
</style>

<div class="settings-container px-2 py-3 px-md-4 py-md-4">
    
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0 tracking-tight">System Settings</h3>
            <p class="text-muted small m-0">Manage store configuration, compliance, and features.</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-3 text-success"></i>
                <div>
                    <strong>Success</strong><br>
                    {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4" role="alert">
             <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fs-4 me-3 text-danger"></i>
                <div>
                    <strong>Action Failed</strong><br>
                    {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                            <h6 class="fw-bold m-0 text-dark fs-5">Store Profile</h6>
                            <small class="text-muted">General store information and branding</small>
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
                                <label class="form-label">Store Address</label>
                                <input type="text" name="store_address" class="form-control form-control-lg" 
                                       value="{{ $settings['store_address'] ?? 'City, Province' }}" placeholder="Street, Brgy, City">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="store_contact" class="form-control form-control-lg" 
                                       value="{{ $settings['store_contact'] ?? '' }}" placeholder="0912-345-6789">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="form-label">Receipt Footer Message</label>
                            <input type="text" name="receipt_footer" class="form-control form-control-lg text-muted" 
                                   value="{{ $settings['receipt_footer'] ?? 'Thank you for your purchase!' }}">
                        </div>
                        
                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary w-100 btn-save shadow-sm">
                                <i class="fas fa-save me-2"></i> Save Store Settings
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
                            <h6 class="fw-bold m-0 text-dark fs-5">BIR & Tax Compliance</h6>
                            <small class="text-muted">Legal and invoicing details</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        {{-- Toggle --}}
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-4 mb-4">
                            <div>
                                <label class="form-check-label fw-bold text-dark mb-0 d-block fs-6" for="taxSwitch">Enable Receipt Tax Printing</label>
                                <small class="text-muted">Shows VAT, TIN, and Permit on receipts.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="enable_tax" value="0">
                                <input class="form-check-input" type="checkbox" id="taxSwitch" name="enable_tax" value="1" 
                                    style="transform: scale(1.3);"
                                    {{ ($settings['enable_tax'] ?? '0') == '1' ? 'checked' : '' }}
                                    onchange="handleTaxSwitchChange(this)">
                            </div>
                        </div>

                        <div id="tax-fields" style="display: {{ ($settings['enable_tax'] ?? '0') == '1' ? 'block' : 'none' }};" class="animate__animated animate__fadeIn">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">TIN (Tax ID)</label>
                                    <div class="input-group">
                                        <input type="password" name="store_tin" id="store_tin" class="form-control form-control-lg" 
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
                                        <input type="password" name="business_permit" id="business_permit" class="form-control form-control-lg" 
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
                                    <input type="number" name="tax_rate" class="form-control form-control-lg" 
                                           value="{{ $settings['tax_rate'] ?? '12' }}" min="0" max="100">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Tax Type</label>
                                    <select name="tax_type" class="form-select form-select-lg">
                                        <option value="inclusive" {{ ($settings['tax_type'] ?? '') == 'inclusive' ? 'selected' : '' }}>VAT Inclusive (Price includes tax)</option>
                                        <option value="exclusive" {{ ($settings['tax_type'] ?? '') == 'exclusive' ? 'selected' : '' }}>VAT Exclusive (Tax added at checkout)</option>
                                        <option value="non_vat" {{ ($settings['tax_type'] ?? '') == 'non_vat' ? 'selected' : '' }}>Non-VAT Registered</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-dark w-100 btn-save shadow-sm">
                                <i class="fas fa-check-double me-2"></i> Save Tax Configuration
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 3. LOYALTY --}}
                <div class="card-settings">
                    <div class="card-header-clean">
                        <div class="icon-box icon-warning shadow-sm"><i class="fas fa-star"></i></div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark fs-5">Loyalty Program</h6>
                            <small class="text-muted">Customer rewards and points system</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4 bg-light p-3 rounded-4">
                            <label class="form-check-label fw-bold text-dark fs-6" for="loyaltySwitch">Enable Points System</label>
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="enable_loyalty" value="0">
                                <input class="form-check-input" type="checkbox" id="loyaltySwitch" name="enable_loyalty" value="1" 
                                    style="transform: scale(1.3);"
                                    {{ ($settings['enable_loyalty'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Earning (Spend to get 1 Point)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 text-muted fw-bold ps-3 py-2" style="border-radius: 12px 0 0 12px;">₱</span>
                                    <input type="number" name="loyalty_ratio" class="form-control form-control-lg border-0 ps-2" min="1" 
                                           value="{{ $settings['loyalty_ratio'] ?? '100' }}" style="border-radius: 0 12px 12px 0;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Redemption (Value of 1 Point)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 text-muted fw-bold ps-3 py-2" style="border-radius: 12px 0 0 12px;">₱</span>
                                    <input type="number" step="0.01" name="points_conversion" class="form-control form-control-lg border-0 ps-2" 
                                           value="{{ $settings['points_conversion'] ?? '1.00' }}" style="border-radius: 0 12px 12px 0;">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-warning text-dark w-100 btn-save shadow-sm">
                                <i class="fas fa-crown me-2"></i> Save Loyalty Settings
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 4. FEATURES & TOGGLES --}}
                <div class="card-settings">
                    <div class="card-header-clean">
                        <div class="icon-box icon-success shadow-sm"><i class="fas fa-toggle-on"></i></div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark fs-5">Features & Integrations</h6>
                            <small class="text-muted">Turn optional system features on or off</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        
                        {{-- Barcode --}}
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-light">
                             <div>
                                <h6 class="fw-bold text-dark m-0">Barcode Printing</h6>
                                <small class="text-muted">Generate and print barcodes for products</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="enable_barcode" value="0">
                                <input class="form-check-input" type="checkbox" id="barcodeSwitch" name="enable_barcode" value="1" 
                                    style="transform: scale(1.3);"
                                    {{ ($settings['enable_barcode'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        {{-- Tithes --}}
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-light">
                            <div>
                                <h6 class="fw-bold text-dark m-0">Tithes Calculation</h6>
                                <small class="text-muted">Automatically calculate 10% tithes on revenue</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="enable_tithes" value="0">
                                <input class="form-check-input" type="checkbox" id="tithesSwitch" name="enable_tithes" value="1" 
                                    style="transform: scale(1.3);"
                                    {{ ($settings['enable_tithes'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        {{-- PAYMONGO (Safety Flag) --}}
                        @if(config('safety_flag_features.online_payment'))
                        <div class="bg-light p-3 rounded-4 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-check-label fw-bold text-success d-flex align-items-center fs-6" for="paymongoSwitch">
                                    <i class="fas fa-wallet me-2"></i> Online Payment (PayMongo)
                                </label>
                                <div class="form-check form-switch m-0">
                                    <input type="hidden" name="enable_paymongo" value="0">
                                    <input class="form-check-input" type="checkbox" id="paymongoSwitch" name="enable_paymongo" value="1" 
                                        style="transform: scale(1.3);"
                                        {{ ($settings['enable_paymongo'] ?? '0') == '1' ? 'checked' : '' }} 
                                        onchange="togglePaymongoFields()">
                                </div>
                            </div>

                            <div id="paymongo-fields" style="display: {{ ($settings['enable_paymongo'] ?? '0') == '1' ? 'block' : 'none' }};" class="animate__animated animate__fadeIn">
                                <div class="mb-3">
                                    <label class="form-label small">Secret Key</label>
                                    <input type="password" name="paymongo_secret_key" class="form-control form-control-lg" 
                                           value="{{ $settings['paymongo_secret_key'] ?? '' }}" placeholder="sk_test_...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Public Key</label>
                                    <input type="text" name="paymongo_public_key" class="form-control form-control-lg" 
                                           value="{{ $settings['paymongo_public_key'] ?? '' }}" placeholder="pk_test_...">
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- MULTI-STORE (Safety Flag) --}} 
                        @if(config('safety_flag_features.multi_store'))
                        <div class="bg-light p-3 rounded-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-check-label fw-bold text-primary d-flex align-items-center fs-6" for="multiStoreSwitch">
                                    <i class="fas fa-network-wired me-2"></i> Multi-Store System
                                </label>
                                <div class="form-check form-switch m-0">
                                    <input type="hidden" name="enable_multi_store" value="0">
                                    <input class="form-check-input" type="checkbox" id="multiStoreSwitch" name="enable_multi_store" value="1" 
                                        style="transform: scale(1.3);"
                                        {{ ($settings['enable_multi_store'] ?? '0') == '1' ? 'checked' : '' }}
                                        onchange="toggleStoreManagement()">
                                </div>
                            </div>
                            <div id="store-management-link" class="mt-3 animate__animated animate__fadeIn" style="display: {{ ($settings['enable_multi_store'] ?? '0') == '1' ? 'block' : 'none' }};">
                                <a href="{{ route('stores.index') }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                                    Manage Stores & Branches
                                </a>
                            </div>
                        </div>
                        @endif

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success w-100 btn-save shadow-sm">
                                <i class="fas fa-sliders-h me-2"></i> Save Feature Settings
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- RIGHT COLUMN: Data Management --}}
        <div class="col-lg-4 col-12">
            <div class="sticky-sidebar">
                <div class="card-settings border-0">
                    <div class="card-header-clean bg-danger bg-opacity-10 border-0">
                        <div class="icon-box icon-danger shadow-sm"><i class="fas fa-database"></i></div>
                        <div>
                             <h6 class="fw-bold m-0 text-danger fs-5">Data Management</h6>
                             <small class="text-danger-emphasis">Backup and Restoration</small>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-danger bg-opacity-10 rounded-bottom-4">
                        <p class="small text-danger-emphasis mb-4">Backup your database regularly to prevent data loss. <strong>Security Warning:</strong> Restore overwrites everything.</p>
                        
                        <a href="{{ route('settings.backup') }}" class="btn btn-outline-danger w-100 mb-4 py-2 rounded-3 border-2 fw-bold bg-white text-danger">
                            <i class="fas fa-download me-2"></i> Download Backup (.sql)
                        </a>

                        <hr class="text-danger opacity-25">

                        <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data" 
                              onsubmit="return confirm('WARNING: This will WIPE all current data and replace it with the backup. Are you sure?');">
                            @csrf
                            <label class="form-label fw-bold text-danger">Restore Database</label>
                            <input type="file" name="backup_file" class="form-control mb-3" required accept=".sql">
                            
                            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold shadow-sm rounded-3">
                                <i class="fas fa-upload me-2"></i> Upload & Restore
                            </button>
                        </form>
                    
                        <hr class="text-danger opacity-25 my-4">

                        {{-- Software Update --}}
                         <div class="mt-2">
                            <label class="form-label fw-bold text-danger-emphasis"><i class="fas fa-sync-alt me-1"></i> Software Update</label>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small text-muted">Current Version:</span>
                                <span class="badge bg-white text-dark shadow-sm">{{ config('version.full') }}</span>
                            </div>

                            {{-- Beta Toggle --}}
                            <div class="form-check form-switch mb-3 bg-white p-2 rounded-3 shadow-sm d-flex align-items-center justify-content-between">
                                <label class="form-check-label small fw-bold ps-2 text-dark" for="betaToggle">
                                    <i class="fas fa-flask text-primary me-1"></i> Beta Program
                                </label>
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="betaToggle" 
                                    style="transform: scale(1.1);"
                                    {{ ($settings['enable_beta'] ?? '0') == '1' ? 'checked' : '' }}
                                    onchange="toggleBetaProgram(this)">
                            </div>
                            
                            <button type="button" class="btn btn-light w-100 py-2 fw-bold text-primary shadow-sm" onclick="checkForUpdates()">
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

@endsection