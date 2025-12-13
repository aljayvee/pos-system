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
                    <hr>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-sm fw-bold">
                            <i class="fas fa-save me-2"></i> Save Store Settings
                        </button>
                    </div>
                </div>

                {{-- 2. BIR / GOVERNMENT COMPLIANCE --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-file-contract me-2"></i> BIR / Government Compliance (Tax)
                    </div>
                    <div class="card-body">
                        {{-- Toggle Switch --}}
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="enable_tax" value="0">
                            {{-- Logic: onchange triggers the intelligent handler --}}
                            <input class="form-check-input" type="checkbox" id="taxSwitch" name="enable_tax" value="1" 
                                {{ ($settings['enable_tax'] ?? '0') == '1' ? 'checked' : '' }}
                                onchange="handleTaxSwitchChange(this)">
                            <label class="form-check-label fw-bold" for="taxSwitch">Enable VAT & BIR Details on Receipt</label>
                        </div>

                        <div id="tax-fields" style="display: {{ ($settings['enable_tax'] ?? '0') == '1' ? 'block' : 'none' }};">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">TIN (Tax Identification Number)</label>
                                    <div class="input-group">
                                        {{-- 
                                            LOGIC FIX: 
                                            1. If value exists, it is READONLY (cannot delete/edit).
                                            2. Placeholder shows "Saved".
                                        --}}
                                        <input type="password" name="store_tin" id="store_tin" class="form-control" 
                                               value="" 
                                               placeholder="{{ !empty($settings['store_tin']) ? '******** (Saved)' : 'Enter TIN' }}"
                                               {{ !empty($settings['store_tin']) ? 'readonly' : '' }}>
                                        
                                        <button class="btn btn-outline-secondary" type="button" id="btn-tin" onclick="handleSecretToggle('store_tin', 'btn-tin')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Business Permit / DTI No.</label>
                                    <div class="input-group">
                                        <input type="password" name="business_permit" id="business_permit" class="form-control" 
                                               value="" 
                                               placeholder="{{ !empty($settings['business_permit']) ? '******** (Saved)' : 'Enter Permit No.' }}"
                                               {{ !empty($settings['business_permit']) ? 'readonly' : '' }}>
                                        
                                        <button class="btn btn-outline-secondary" type="button" id="btn-permit" onclick="handleSecretToggle('business_permit', 'btn-permit')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">VAT Rate (%)</label>
                                    <input type="number" name="tax_rate" class="form-control" 
                                           value="{{ $settings['tax_rate'] ?? '12' }}" min="0" max="100">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold small">Tax Type</label>
                                    <select name="tax_type" class="form-select">
                                        <option value="inclusive" {{ ($settings['tax_type'] ?? '') == 'inclusive' ? 'selected' : '' }}>VAT Inclusive</option>
                                        <option value="exclusive" {{ ($settings['tax_type'] ?? '') == 'exclusive' ? 'selected' : '' }}>VAT Exclusive</option>
                                        <option value="non_vat" {{ ($settings['tax_type'] ?? '') == 'non_vat' ? 'selected' : '' }}>Non-VAT Registered</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                    <i class="fas fa-save me-2"></i> Save Tax Settings
                                </button>
                    </div>
                </div>

                {{-- STRICT DISABLE VERIFICATION MODAL --}}
<div class="modal fade" id="disableVerificationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold"><i class="fas fa-shield-alt me-2"></i>Security Check: Disable Compliance</h6>
                <button type="button" class="btn-close btn-close-white" onclick="cancelDisable()"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small mb-3">
                    <i class="fas fa-exclamation-circle me-1"></i> 
                    To turn off BIR Compliance, you must verify your identity and credentials.
                </div>
                
                <div class="mb-2">
                    <label class="small fw-bold">Admin Password</label>
                    <input type="password" id="verify-pass" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Enter Current TIN ID</label>
                    <input type="text" id="verify-tin" class="form-control" placeholder="Verify TIN">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Enter Business Permit ID</label>
                    <input type="text" id="verify-permit" class="form-control" placeholder="Verify Permit">
                </div>
                <div id="verify-error" class="text-danger small fw-bold text-center mb-2"></div>
                
                <button type="button" class="btn btn-danger w-100 fw-bold" onclick="processDisable()">
                    CONFIRM & DISABLE
                </button>
            </div>
        </div>
    </div>
</div>

                {{-- SECURITY MODAL (Add this at the bottom of content section) --}}
                <div class="modal fade" id="securityModal" tabindex="-1">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h6 class="modal-title fw-bold"><i class="fas fa-lock me-2"></i>Admin Verification</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="small text-muted mb-2">Enter your admin password to view this sensitive field.</p>
                                <input type="hidden" id="target-field-id">
                                <input type="password" id="admin-password" class="form-control text-center fw-bold" placeholder="Password" autofocus>
                                <div id="password-error" class="text-danger small mt-1 text-center" style="display:none;">Incorrect password</div>
                            </div>
                            <div class="modal-footer p-2">
                                <button type="button" class="btn btn-danger w-100" onclick="verifyAndReveal()">
                                    <i class="fas fa-unlock me-2"></i> Reveal
                                </button>
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
                    <hr>
                     <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                    <i class="fas fa-save me-2"></i> Save Loyalty Settings
                                </button>
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

                        <div id="store-management-link" class="mb-2 ps-4" style="display: {{ ($settings['enable_multi_store'] ?? '0') == '1' ? 'block' : 'none' }};">
                            <a href="{{ route('stores.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-store-alt me-1"></i> Manage Stores & Branches
                            </a>
                        </div>
                    </div>
                    <hr>
                        <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                        <i class="fas fa-save me-2"></i> Save Feature Settings
                    </button>
                </div>
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
    let securityModal;
    let disableModal;

    document.addEventListener('DOMContentLoaded', function() {
        securityModal = new bootstrap.Modal(document.getElementById('securityModal'));
        disableModal = new bootstrap.Modal(document.getElementById('disableVerificationModal'));
        
        // Enter key support
        document.getElementById('admin-password').addEventListener('keyup', e => { if(e.key === 'Enter') verifyAndReveal(); });
    });

    // --- 1. TOGGLE LOGIC (SHOW / HIDE / READONLY) ---
    function handleSecretToggle(fieldId, btnId) {
        const input = document.getElementById(fieldId);
        const icon = document.querySelector(`#${btnId} i`);

        // CASE A: It is Hidden & Saved (Readonly) -> REQUEST REVEAL
        if (input.readOnly && input.placeholder.includes('Saved')) {
            requestReveal(fieldId); // Open Password Modal
        } 
        // CASE B: It is Revealed -> HIDE IT BACK
        else if (!input.readOnly && input.value !== '') {
            input.value = ''; // Clear plain text
            input.type = 'password';
            input.readOnly = true; // Lock it again
            input.placeholder = '******** (Saved)';
            icon.className = 'fas fa-eye'; // Reset Icon
        }
        // CASE C: It is New/Empty (Not Saved) -> Standard Toggle
        else {
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    }

    // --- 2. REVEAL LOGIC (AJAX) ---
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
                // UNLOCK THE FIELD
                const input = document.getElementById(fieldKey);
                input.value = data.value; // Show decrypted value
                input.type = "text"; 
                input.readOnly = false; // ALLOW EDITING/DELETING
                
                // Update Icon to "Slash" (Hide)
                const btnId = fieldKey === 'store_tin' ? 'btn-tin' : 'btn-permit';
                document.querySelector(`#${btnId} i`).className = 'fas fa-eye-slash';
                
                securityModal.hide();
            } else {
                errorMsg.innerText = data.message || "Incorrect Password";
                errorMsg.style.display = 'block';
            }
        });
    }

    // --- 3. SWITCH LOGIC (STRICT OFF) ---
    function handleTaxSwitchChange(el) {
        if (el.checked) {
            // Turning ON: Just show fields
            document.getElementById('tax-fields').style.display = 'block';
        } else {
            // Turning OFF: STOP! Verify first.
            el.checked = true; // Force it back ON visually
            disableModal.show();
        }
    }

    function cancelDisable() {
        disableModal.hide();
        // Switch remains ON (checked)
    }

    function processDisable() {
        const pass = document.getElementById('verify-pass').value;
        const tin = document.getElementById('verify-tin').value;
        const permit = document.getElementById('verify-permit').value;
        const errorEl = document.getElementById('verify-error');

        if(!pass || !tin || !permit) {
            errorEl.innerText = "All fields are required.";
            return;
        }

        errorEl.innerText = "Verifying...";

        fetch("{{ route('settings.verify_disable_bir') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value },
            body: JSON.stringify({ password: pass, tin: tin, permit: permit })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Success: Turn OFF
                const switchEl = document.getElementById('taxSwitch');
                switchEl.checked = false; // Physically uncheck
                document.getElementById('tax-fields').style.display = 'none'; // Hide UI
                disableModal.hide();
                
                // Clear Verification Fields
                document.getElementById('verify-pass').value = '';
                document.getElementById('verify-tin').value = '';
                document.getElementById('verify-permit').value = '';
            } else {
                errorEl.innerText = data.message;
            }
        })
        .catch(() => errorEl.innerText = "Server Error.");
    }

    // Standard Toggles
    function togglePaymongoFields() {
        const isChecked = document.getElementById('paymongoSwitch').checked;
        document.getElementById('paymongo-fields').style.display = isChecked ? 'block' : 'none';
    }
    function toggleStoreManagement() {
        const isChecked = document.getElementById('multiStoreSwitch').checked;
        document.getElementById('store-management-link').style.display = isChecked ? 'block' : 'none';
    }
    
    // Form Save Validation
    document.querySelector('form').addEventListener('submit', function(event) {
        const taxSwitch = document.getElementById('taxSwitch');
        if (taxSwitch.checked) {
            const tinInput = document.getElementById('store_tin');
            const permitInput = document.getElementById('business_permit');
            
            // Allow if value is set OR placeholder implies saved data
            const tinOk = tinInput.value.trim() !== '' || tinInput.placeholder.includes('Saved');
            const permitOk = permitInput.value.trim() !== '' || permitInput.placeholder.includes('Saved');

            if (!tinOk || !permitOk) {
                event.preventDefault();
                alert("Please enter TIN and Business Permit to save.");
            }
        }
    });
</script>
@endsection