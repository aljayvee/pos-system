@extends('admin.layout')

@section('content')
<style>
    /* === MODERN SETTINGS THEME === */
    :root {
        --primary-soft: #e0e7ff;
        --primary-text: #4f46e5;
        --card-border-radius: 16px;
    }
    
    .settings-container { max-width: 1400px; margin: 0 auto; }
    
    .card-settings {
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        border-radius: var(--card-border-radius);
        background: white;
        margin-bottom: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card-settings:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .card-header-clean {
        background: transparent;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .icon-box {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
    }
    .icon-primary { background: var(--primary-soft); color: var(--primary-text); }
    .icon-warning { background: #fef3c7; color: #d97706; }
    .icon-success { background: #dcfce7; color: #16a34a; }
    .icon-danger  { background: #fee2e2; color: #dc2626; }
    .icon-dark    { background: #f3f4f6; color: #1f2937; }

    .form-label { font-weight: 600; color: #374151; font-size: 0.85rem; margin-bottom: 0.4rem; }
    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        padding: 0.6rem 1rem;
        font-size: 0.95rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-text);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .btn-save {
        border-radius: 10px;
        padding: 0.6rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    /* Sticky Sidebar for Desktop */
    @media (min-width: 992px) {
        .sticky-sidebar { position: sticky; top: 20px; }
    }
</style>

<div class="settings-container py-4 px-3 px-md-4">
    
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0 tracking-tight">System Settings</h3>
            <p class="text-muted small m-0">Manage store configuration, compliance, and features.</p>
        </div>
    </div>

    <div class="row g-4">
        {{-- LEFT COLUMN: Configuration Forms --}}
        <div class="col-lg-8 col-12">
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                
                {{-- 1. STORE PROFILE --}}
                <div class="card-settings">
                    <div class="card-header-clean">
                        <!--<div class="icon-box icon-primary"><i class="fas fa-store"></i></div>-->
                        <h6 class="fw-bold m-0 text-dark "><i class="fas fa-store"></i> Store Profile</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label">Store Name</label>
                            <input type="text" name="store_name" class="form-control fw-bold" 
                                   value="{{ $settings['store_name'] ?? 'My Sari-Sari Store' }}" placeholder="Enter store name">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Store Address</label>
                                <input type="text" name="store_address" class="form-control" 
                                       value="{{ $settings['store_address'] ?? 'City, Province' }}" placeholder="Street, Brgy, City">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="store_contact" class="form-control" 
                                       value="{{ $settings['store_contact'] ?? '' }}" placeholder="0912-345-6789">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Receipt Footer Message</label>
                            <input type="text" name="receipt_footer" class="form-control text-muted" 
                                   value="{{ $settings['receipt_footer'] ?? 'Thank you for your purchase!' }}">
                        </div>
                        
                        <div class="mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-primary w-100 btn-save">
                                Save Store Settings
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 2. BIR / COMPLIANCE --}}
                <div class="card-settings">
                    <div class="card-header-clean">
                        
                        <h6 class="fw-bold m-0 text-dark"><i class="fas fa-file-invoice"> </i>BIR & Tax Compliance</h6>
                    </div>
                    <div class="card-body p-4">
                        {{-- Toggle --}}
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-3 mb-3">
                            <div>
                                <label class="form-check-label fw-bold text-dark mb-0 d-block" for="taxSwitch">Enable Receipt Tax Printing</label>
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

                        <div id="tax-fields" style="display: {{ ($settings['enable_tax'] ?? '0') == '1' ? 'block' : 'none' }};">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">TIN (Tax ID)</label>
                                    <div class="input-group">
                                        <input type="password" name="store_tin" id="store_tin" class="form-control" 
                                               value="" 
                                               placeholder="{{ !empty($settings['store_tin']) ? '******** (Saved)' : 'Enter TIN' }}"
                                               {{ !empty($settings['store_tin']) ? 'readonly' : '' }}>
                                        <button class="btn btn-light border" type="button" id="btn-tin" onclick="handleSecretToggle('store_tin', 'btn-tin')">
                                            <i class="fas fa-eye text-secondary"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Permit / DTI</label>
                                    <div class="input-group">
                                        <input type="password" name="business_permit" id="business_permit" class="form-control" 
                                               value="" 
                                               placeholder="{{ !empty($settings['business_permit']) ? '******** (Saved)' : 'Enter Permit No.' }}"
                                               {{ !empty($settings['business_permit']) ? 'readonly' : '' }}>
                                        <button class="btn btn-light border" type="button" id="btn-permit" onclick="handleSecretToggle('business_permit', 'btn-permit')">
                                            <i class="fas fa-eye text-secondary"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">VAT Rate (%)</label>
                                    <input type="number" name="tax_rate" class="form-control" 
                                           value="{{ $settings['tax_rate'] ?? '12' }}" min="0" max="100">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Tax Type</label>
                                    <select name="tax_type" class="form-select">
                                        <option value="inclusive" {{ ($settings['tax_type'] ?? '') == 'inclusive' ? 'selected' : '' }}>VAT Inclusive (Price includes tax)</option>
                                        <option value="exclusive" {{ ($settings['tax_type'] ?? '') == 'exclusive' ? 'selected' : '' }}>VAT Exclusive (Tax added at checkout)</option>
                                        <option value="non_vat" {{ ($settings['tax_type'] ?? '') == 'non_vat' ? 'selected' : '' }}>Non-VAT Registered</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-dark w-100 btn-save">
                                Save Tax Configuration
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 3. LOYALTY --}}
                <div class="card-settings">
                    <div class="card-header-clean">
                        
                        <h6 class="fw-bold m-0 text-dark"><i class="fas fa-star"></i> Loyalty Program</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <label class="form-check-label fw-bold text-dark" for="loyaltySwitch">Enable Points System</label>
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="enable_loyalty" value="0">
                                <input class="form-check-input" type="checkbox" id="loyaltySwitch" name="enable_loyalty" value="1" 
                                    style="transform: scale(1.3);"
                                    {{ ($settings['enable_loyalty'] ?? '0') == '1' ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase">Earning (Spend to get 1 Point)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">₱</span>
                                    <input type="number" name="loyalty_ratio" class="form-control border-start-0 ps-0" min="1" 
                                           value="{{ $settings['loyalty_ratio'] ?? '100' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase">Redemption (Value of 1 Point)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">₱</span>
                                    <input type="number" step="0.01" name="points_conversion" class="form-control border-start-0 ps-0" 
                                           value="{{ $settings['points_conversion'] ?? '1.00' }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-warning text-dark w-100 btn-save">
                                Save Loyalty Settings
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 4. FEATURES & TOGGLES --}}
                <div class="card-settings">
                    <div class="card-header-clean">
                        
                        <h6 class="fw-bold m-0 text-dark"><i class="fas fa-toggle-on"></i> Features & Integrations</h6>
                    </div>
                    <div class="card-body p-4">
                        
                        {{-- Barcode --}}
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_barcode" value="0">
                            <input class="form-check-input" type="checkbox" id="barcodeSwitch" name="enable_barcode" value="1" 
                                {{ ($settings['enable_barcode'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label ms-2" for="barcodeSwitch">Enable Barcode Printing</label>
                        </div>

                        {{-- Tithes --}}
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_tithes" value="0">
                            <input class="form-check-input" type="checkbox" id="tithesSwitch" name="enable_tithes" value="1" 
                                {{ ($settings['enable_tithes'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label ms-2" for="tithesSwitch">Enable Tithes Calculation (10%)</label>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        {{-- PAYMONGO --}}
                        <div class="bg-light p-3 rounded-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-check-label fw-bold text-success d-flex align-items-center" for="paymongoSwitch">
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

                            <div id="paymongo-fields" style="display: {{ ($settings['enable_paymongo'] ?? '0') == '1' ? 'block' : 'none' }};">
                                <div class="mb-2">
                                    <label class="form-label small">Secret Key</label>
                                    <input type="password" name="paymongo_secret_key" class="form-control" 
                                           value="{{ $settings['paymongo_secret_key'] ?? '' }}" placeholder="sk_test_...">
                                </div>
                                <div>
                                    <label class="form-label small">Public Key</label>
                                    <input type="text" name="paymongo_public_key" class="form-control" 
                                           value="{{ $settings['paymongo_public_key'] ?? '' }}" placeholder="pk_test_...">
                                </div>
                            </div>
                        </div>

                        {{-- MULTI-STORE --}}
                        <div class="bg-light p-3 rounded-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-check-label fw-bold text-primary d-flex align-items-center" for="multiStoreSwitch">
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
                            <div id="store-management-link" class="mt-2" style="display: {{ ($settings['enable_multi_store'] ?? '0') == '1' ? 'block' : 'none' }};">
                                <a href="{{ route('stores.index') }}" class="btn btn-sm btn-outline-primary w-100">
                                    Manage Stores & Branches
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-success w-100 btn-save">
                                Save Feature Settings
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- RIGHT COLUMN: Data Management --}}
        <div class="col-lg-4 col-12">
            <div class="sticky-sidebar">
                <div class="card-settings border-danger border-opacity-25">
                    <div class="card-header-clean bg-danger bg-opacity-10 border-danger border-opacity-10">
                        <div class="icon-box icon-danger"></div>
                        <h6 class="fw-bold m-0 text-danger"> <i class="fas fa-database"></i> Data Management</h6>
                    </div>
                    <div class="card-body p-4">
                        <p class="small text-muted mb-3">Backup your database regularly to prevent data loss.</p>
                        
                        <a href="{{ route('settings.backup') }}" class="btn btn-outline-dark w-100 mb-4 py-2">
                            <i class="fas fa-download me-2"></i> Download Backup (.sql)
                        </a>

                        <hr class="text-muted opacity-25">

                        <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data" 
                              onsubmit="return confirm('WARNING: This will WIPE all current data and replace it with the backup. Are you sure?');">
                            @csrf
                            <label class="form-label fw-bold text-danger">Restore Database</label>
                            <input type="file" name="backup_file" class="form-control mb-3" required accept=".sql">
                            
                            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">
                                <i class="fas fa-upload me-2"></i> Upload & Restore
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS & SCRIPTS (Retained) --}}
@include('admin.settings.partials.modals')

<script>
    // Keeping your exact original logic
    let securityModal, disableModal;

    document.addEventListener('DOMContentLoaded', function() {
        securityModal = new bootstrap.Modal(document.getElementById('securityModal'));
        disableModal = new bootstrap.Modal(document.getElementById('disableVerificationModal'));
        document.getElementById('admin-password').addEventListener('keyup', e => { if(e.key === 'Enter') verifyAndReveal(); });
    });

    function handleSecretToggle(fieldId, btnId) {
        const input = document.getElementById(fieldId);
        const icon = document.querySelector(`#${btnId} i`);
        if (input.readOnly && input.placeholder.includes('Saved')) {
            requestReveal(fieldId);
        } else if (!input.readOnly && input.value !== '') {
            input.value = ''; input.type = 'password'; input.readOnly = true; input.placeholder = '******** (Saved)';
            icon.className = 'fas fa-eye';
        } else {
            if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; } 
            else { input.type = 'password'; icon.className = 'fas fa-eye'; }
        }
    }

    function requestReveal(fieldId) {
        document.getElementById('target-field-id').value = fieldId;
        document.getElementById('admin-password').value = '';
        document.getElementById('password-error').style.display = 'none';
        securityModal.show();
        setTimeout(() => document.getElementById('admin-password').focus(), 500);
    }

    function verifyAndReveal() {
        const password = document.getElementById('admin-password').value;
        const fieldKey = document.getElementById('target-field-id').value;
        const errorMsg = document.getElementById('password-error');
        if (!password) return;

        fetch("{{ route('settings.reveal') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value },
            body: JSON.stringify({ password: password, key: fieldKey })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const input = document.getElementById(fieldKey);
                input.value = data.value; input.type = "text"; input.readOnly = false;
                const btnId = fieldKey === 'store_tin' ? 'btn-tin' : 'btn-permit';
                document.querySelector(`#${btnId} i`).className = 'fas fa-eye-slash';
                securityModal.hide();
            } else {
                errorMsg.innerText = data.message || "Incorrect Password";
                errorMsg.style.display = 'block';
            }
        });
    }

    function handleTaxSwitchChange(el) {
        if (el.checked) document.getElementById('tax-fields').style.display = 'block';
        else { el.checked = true; disableModal.show(); }
    }

    function cancelDisable() { disableModal.hide(); }

    function processDisable() {
        const pass = document.getElementById('verify-pass').value;
        const tin = document.getElementById('verify-tin').value;
        const permit = document.getElementById('verify-permit').value;
        const errorEl = document.getElementById('verify-error');

        if(!pass || !tin || !permit) { errorEl.innerText = "All fields are required."; return; }
        errorEl.innerText = "Verifying...";

        fetch("{{ route('settings.verify_disable_bir') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value },
            body: JSON.stringify({ password: pass, tin: tin, permit: permit })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('taxSwitch').checked = false;
                document.getElementById('tax-fields').style.display = 'none';
                disableModal.hide();
                document.getElementById('verify-pass').value = '';
                document.getElementById('verify-tin').value = '';
                document.getElementById('verify-permit').value = '';
            } else { errorEl.innerText = data.message; }
        })
        .catch(() => errorEl.innerText = "Server Error.");
    }

    function togglePaymongoFields() {
        const isChecked = document.getElementById('paymongoSwitch').checked;
        document.getElementById('paymongo-fields').style.display = isChecked ? 'block' : 'none';
    }
    function toggleStoreManagement() {
        const isChecked = document.getElementById('multiStoreSwitch').checked;
        document.getElementById('store-management-link').style.display = isChecked ? 'block' : 'none';
    }
    
    document.querySelector('form').addEventListener('submit', function(event) {
        const taxSwitch = document.getElementById('taxSwitch');
        if (taxSwitch.checked) {
            const tinInput = document.getElementById('store_tin');
            const permitInput = document.getElementById('business_permit');
            const tinOk = tinInput.value.trim() !== '' || tinInput.placeholder.includes('Saved');
            const permitOk = permitInput.value.trim() !== '' || permitInput.placeholder.includes('Saved');
            if (!tinOk || !permitOk) {
                event.preventDefault();
                alert("Please enter TIN and Business Permit to save.");
            }
        }
    });
</script>

{{-- INLINE MODALS TO PREVENT 'include not found' ERROR IF YOU DON'T HAVE PARTIALS --}}
{{-- SECURITY VERIFICATION MODAL --}}
<div class="modal fade" id="securityModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h6 class="modal-title fw-bold"><i class="fas fa-lock me-2"></i>Verify Admin</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="small text-muted mb-3">Enter your password to reveal this field.</p>
                <input type="hidden" id="target-field-id">
                <input type="password" id="admin-password" class="form-control text-center fw-bold fs-5 mb-2" placeholder="Password">
                <div id="password-error" class="text-danger small fw-bold" style="display:none;"></div>
                <button type="button" class="btn btn-danger w-100 mt-3" onclick="verifyAndReveal()">Reveal Data</button>
            </div>
        </div>
    </div>
</div>

{{-- DISABLE VERIFICATION MODAL --}}
<div class="modal fade" id="disableVerificationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h6 class="modal-title fw-bold"><i class="fas fa-shield-alt me-2"></i>Security Check</h6>
                <button type="button" class="btn-close btn-close-white" onclick="cancelDisable()"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning small mb-3 border-0 bg-warning bg-opacity-10 text-warning-emphasis">
                    <i class="fas fa-exclamation-triangle me-1"></i> To disable Tax Compliance, verify your identity.
                </div>
                
                <div class="mb-3">
                    <label class="small fw-bold text-muted">Admin Password</label>
                    <input type="password" id="verify-pass" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">Current TIN (Verification)</label>
                    <input type="text" id="verify-tin" class="form-control" placeholder="Enter saved TIN">
                </div>
                <div class="mb-4">
                    <label class="small fw-bold text-muted">Current Permit # (Verification)</label>
                    <input type="text" id="verify-permit" class="form-control" placeholder="Enter saved Permit No.">
                </div>
                <div id="verify-error" class="text-danger small fw-bold text-center mb-3"></div>
                
                <button type="button" class="btn btn-danger w-100 fw-bold py-2" onclick="processDisable()">
                    CONFIRM DISABLE
                </button>
            </div>
        </div>
    </div>
</div>
@endsection