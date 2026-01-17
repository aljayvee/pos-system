@extends('admin.layout')

@section('content')
    <div class="container-fluid p-0">

        <div class="container-fluid p-0">

            {{-- MOBILE HEADER --}}
            <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
                <div class="px-3 py-3 d-flex align-items-center justify-content-between">
                    <h4 class="m-0 fw-bold text-dark"><i class="fas fa-cog text-primary me-2"></i>More Features</h4>
                </div>
                {{-- STICKY TABS (Mobile Context) --}}
                <div class="bg-white border-bottom px-2 overflow-auto custom-scrollbar-hidden">
                    <ul class="nav nav-pills flex-nowrap pb-2" id="settingsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-nowrap rounded-pill border ms-1" id="store-tab"
                                data-bs-toggle="tab" data-bs-target="#store" type="button" role="tab">
                                <i class="fas fa-store me-2"></i>Store
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-nowrap rounded-pill border ms-1" id="loyalty-tab"
                                data-bs-toggle="tab" data-bs-target="#loyalty" type="button" role="tab">
                                Loyalty
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-nowrap rounded-pill border ms-1" id="modules-tab"
                                data-bs-toggle="tab" data-bs-target="#modules" type="button" role="tab">
                                Modules
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-nowrap rounded-pill border ms-1" id="data-tab" data-bs-toggle="tab"
                                data-bs-target="#data" type="button" role="tab">
                                Data
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-nowrap rounded-pill border ms-1 me-1" id="system-tab"
                                data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                                System
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- DESKTOP HEADER (Hidden Mobile) --}}
            <div class="d-none d-lg-flex justify-content-between align-items-center mb-4 px-4 pt-4">
                <div>
                    <h2 class="h3 mb-1 text-gray-800">More Features</h2>
                    <p class="text-muted mb-0">Manage your store preferences.</p>
                </div>
                <button class="btn btn-primary" onclick="submitSettings()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" id="settingsForm">
                @csrf

                {{-- DESKTOP NAV TABS (Hidden Mobile) --}}
                <div class="card shadow-sm mb-4 mx-lg-4 border-0 rounded-4">
                    <div class="card-header p-0 mx-3 mt-3 border-bottom-0 bg-white d-none d-lg-block">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#store"
                                    type="button"><i class="fas fa-store me-2"></i>Store</button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#loyalty"
                                    type="button">Loyalty</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#modules"
                                    type="button">Modules</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#data"
                                    type="button">Data</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#system"
                                    type="button">System</button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="settingsTabContent">

                            {{-- 1. STORE TAB --}}
                            <div class="tab-pane fade show active" id="store" role="tabpanel" aria-labelledby="store-tab">
                                <div class="mb-4">
                                    <h5 class="card-title mb-3">Receipt Template (Generic & Invoice)</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Store Name</label>
                                        <input type="text" name="store_name" class="form-control"
                                            value="{{ $settings['store_name'] ?? 'My Store' }}">
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-7">
                                            <label class="form-label">Address</label>
                                            <input type="text" name="store_address" class="form-control"
                                                value="{{ $settings['store_address'] ?? '' }}">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Contact</label>
                                            <input type="text" name="store_contact" class="form-control"
                                                value="{{ $settings['store_contact'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div>
                                    <h5 class="card-title mb-3">Receipt Footer</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Message</label>
                                        <input type="text" name="receipt_footer" class="form-control"
                                            value="{{ $settings['receipt_footer'] ?? 'Thank you!' }}">
                                        <div class="form-text">Appears at the bottom of printed receipts.</div>
                                    </div>
                                </div>
                            </div>



                            {{-- 3. LOYALTY TAB --}}
                            <div class="tab-pane fade" id="loyalty" role="tabpanel" aria-labelledby="loyalty-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title m-0">Loyalty Program</h5>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="enable_loyalty" value="0">
                                        <input class="form-check-input" type="checkbox" name="enable_loyalty" value="1" {{ ($settings['enable_loyalty'] ?? '0') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card bg-light border-0 h-100">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted text-uppercase mb-3"
                                                    style="font-size: 0.8rem; font-weight: bold;">Earning Ratio</h6>
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <span>Spend</span>
                                                    <input type="number" name="loyalty_ratio"
                                                        class="form-control text-center" style="width: 80px"
                                                        value="{{ $settings['loyalty_ratio'] ?? '100' }}">
                                                    <span>= 1 Pt</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light border-0 h-100">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted text-uppercase mb-3"
                                                    style="font-size: 0.8rem; font-weight: bold;">Redemption Value</h6>
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <span>1 Pt =</span>
                                                    <input type="number" step="0.01" name="points_conversion"
                                                        class="form-control text-center" style="width: 80px"
                                                        value="{{ $settings['points_conversion'] ?? '1.00' }}">
                                                    <span>PHP</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. MODULES TAB --}}
                            <div class="tab-pane fade" id="modules" role="tabpanel" aria-labelledby="modules-tab">
                                <h5 class="card-title mb-3">System Modules</h5>
                                <ul class="list-group list-group-flush mb-4">
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-bold">Barcode Printing</div>
                                            <small class="text-muted">Enable sticker label generation</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="enable_barcode" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_barcode" value="1"
                                                {{ ($settings['enable_barcode'] ?? '0') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-bold">Tithes</div>
                                            <small class="text-muted">Auto-calculate 10% tithes</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="enable_tithes" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_tithes" value="1"
                                                {{ ($settings['enable_tithes'] ?? '0') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </li>

                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-bold">Register Cash Logs</div>
                                            <small class="text-muted">Enable history and logs for cash registers</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="enable_register_logs" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_register_logs"
                                                value="1" {{ ($settings['enable_register_logs'] ?? '0') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </li>

                                    @if(config('safety_flag_features.multi_store'))
                                        <li class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold text-primary">Multi-Store</div>
                                                    <small class="text-muted">Branch management</small>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="enable_multi_store" value="0">
                                                    <input class="form-check-input" type="checkbox" id="multiStoreSwitch"
                                                        name="enable_multi_store" value="1" {{ ($settings['enable_multi_store'] ?? '0') == '1' ? 'checked' : '' }}
                                                        onchange="toggleVis('store-management-link', this.checked)">
                                                </div>
                                            </div>
                                            <div class="mt-2 {{ ($settings['enable_multi_store'] ?? '0') == '1' ? '' : 'd-none' }}"
                                                id="store-management-link">
                                                <a href="{{ route('stores.index') }}"
                                                    class="btn btn-sm btn-outline-primary">Manage Branches</a>
                                            </div>
                                        </li>
                                    @endif
                                </ul>

                                @if(config('safety_flag_features.online_payment'))
                                    <div class="card border-success border-opacity-25 bg-success bg-opacity-10">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="card-title text-success m-0">Online Payment</h5>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="enable_paymongo" value="0">
                                                    <input class="form-check-input" type="checkbox" id="paymongoSwitch"
                                                        name="enable_paymongo" value="1" {{ ($settings['enable_paymongo'] ?? '0') == '1' ? 'checked' : '' }}
                                                        onchange="toggleVis('paymongo-fields', this.checked)">
                                                </div>
                                            </div>
                                            <div id="paymongo-fields"
                                                class="{{ ($settings['enable_paymongo'] ?? '0') == '1' ? '' : 'd-none' }}">
                                                <div class="mb-3">
                                                    <label class="form-label">Secret Key</label>
                                                    <input type="password" name="paymongo_secret_key" class="form-control"
                                                        value="{{ $settings['paymongo_secret_key'] ?? '' }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">Public Key</label>
                                                    <input type="text" name="paymongo_public_key" class="form-control"
                                                        value="{{ $settings['paymongo_public_key'] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- 5. DATA TAB --}}
                            <div class="tab-pane fade" id="data" role="tabpanel" aria-labelledby="data-tab">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="card-title m-0">Danger Zone</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <h6 class="fw-bold m-0">Backup Database</h6>
                                                <small class="text-muted">Download SQL file</small>
                                            </div>
                                            <a href="{{ route('settings.backup') }}"
                                                class="btn btn-sm btn-outline-danger">Download</a>
                                        </div>
                                        <hr>

                                        {{-- Restore Form (Integrated) --}}
                                        <div>
                                            <h6 class="fw-bold text-danger mb-2">Restore Database</h6>
                                            <div class="alert alert-warning py-2 small mb-3">
                                                <i class="fas fa-exclamation-triangle me-1"></i> <strong>Warning:</strong>
                                                This will replace all current data.
                                            </div>
                                            <form action="{{ route('settings.restore') }}" method="POST"
                                                enctype="multipart/form-data"
                                                onsubmit="return confirm('CRITICAL WARNING: This will WIPE all current data. Are you absolutely sure?');">
                                                @csrf
                                                <div class="input-group">
                                                    <input type="file" name="backup_file"
                                                        class="form-control form-control-sm" required accept=".sql">
                                                    <button type="submit" class="btn btn-danger btn-sm">Restore</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 6. SYSTEM TAB --}}
                            <div class="tab-pane fade" id="system" role="tabpanel" aria-labelledby="system-tab">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title m-0">Software Update</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center py-4">
                                            <h6 class="text-muted text-uppercase small fw-bold mb-2">Current Version</h6>
                                            <h3 class="fw-bold text-dark mb-4">{{ config('version.full', '1.0.0') }}</h3>

                                            <button class="btn btn-primary btn-lg w-100" id="btnCheckUpdate"
                                                onclick="checkUpdate()">
                                                <i class="fas fa-sync-alt me-2"></i> Check for Updates
                                            </button>
                                        </div>

                                        {{-- Update Available Section --}}
                                        <div id="update-available-section" class="d-none mt-3">
                                            <div class="alert alert-success border-0 shadow-sm mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-arrow-circle-up fa-2x me-3"></i>
                                                    <div>
                                                        <h5 class="alert-heading fw-bold mb-0">Update Available!</h5>
                                                        <small>Version <span id="new-version-display"
                                                                class="fw-bold"></span> is ready.</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card bg-light border-0 mb-3">
                                                <div class="card-body py-3">
                                                    <h6 class="fw-bold small text-muted text-uppercase mb-2">What's New</h6>
                                                    <ul id="changelog-list" class="small mb-0 ps-3"></ul>
                                                </div>
                                            </div>

                                            <button class="btn btn-success btn-lg w-100 fw-bold shadow-sm" id="btnRunUpdate"
                                                onclick="runUpdate()">
                                                <i class="fas fa-cloud-download-alt me-2"></i> Update System Now
                                            </button>
                                        </div>

                                        {{-- No Update Section --}}
                                        <div id="no-update-section"
                                            class="d-none alert alert-light text-center mt-3 border">
                                            <i class="fas fa-check-circle text-success me-2 fa-lg"></i> <span
                                                class="fw-bold text-muted">You are up to date!</span>
                                        </div>

                                        {{-- Console Output (For Debugging) --}}
                                        <div id="update-console" class="mt-4 d-none">
                                            <label class="form-label small fw-bold text-muted">System Log</label>
                                            <pre class="bg-dark text-white p-3 rounded shadow-sm text-start"
                                                style="font-size: 0.75rem; max-height: 250px; overflow-y: auto; white-space: pre-wrap;"
                                                id="console-output"></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>

            {{-- Mobile Save Button (Static Bottom) --}}
            <div class="d-lg-none mt-4 mb-4 pb-4 px-3">
                <button class="btn btn-primary w-100 btn-lg rounded-pill shadow-sm" onclick="submitSettings()">
                    <i class="fas fa-save me-2"></i> Save Changes
                </button>
            </div>

        </div>

        @include('admin.store_preferences.partials.modals')

        <script>
            function submitSettings() {
                document.getElementById('settingsForm').submit();
            }

            function toggleVis(elementId, isChecked) {
                const el = document.getElementById(elementId);
                if (el) {
                    isChecked ? el.classList.remove('d-none') : el.classList.add('d-none');
                }
            }

            function handleSecretToggle(fieldId) {
                const field = document.getElementById(fieldId);
                if (!field) return;

                if (field.type === "password") {
                    const modalEl = document.getElementById('securityModal');
                    if (modalEl) {
                        const modal = new bootstrap.Modal(modalEl);
                        const targetInput = document.getElementById('target-field-id');
                        if (targetInput) targetInput.value = fieldId;
                        modal.show();
                    } else {
                        field.type = "text";
                    }
                } else {
                    field.type = "password";
                }
            }

            // --- SYSTEM UPDATE LOGIC ---
            function checkUpdate() {
                const btn = document.getElementById('btnCheckUpdate');
                const consoleEl = document.getElementById('update-console');
                const outputEl = document.getElementById('console-output');

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Checking...';

                // Reset UI
                document.getElementById('update-available-section').classList.add('d-none');
                document.getElementById('no-update-section').classList.add('d-none');
                consoleEl.classList.add('d-none');

                fetch("{{ route('settings.check_update') }}")
                    .then(res => res.json())
                    .then(data => {
                        if (data.has_update) {
                            document.getElementById('new-version-display').textContent = data.latest;
                            const list = document.getElementById('changelog-list');
                            list.innerHTML = '';
                            if (data.changelog) {
                                const li = document.createElement('li');
                                li.textContent = data.changelog;
                                list.appendChild(li);
                            }
                            document.getElementById('update-available-section').classList.remove('d-none');
                        } else {
                            document.getElementById('no-update-section').classList.remove('d-none');
                        }
                    })
                    .catch(err => {
                        consoleEl.classList.remove('d-none');
                        outputEl.textContent = "Error checking update: " + err;
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Check Again';
                    });
            }

            function runUpdate() {
                if (!confirm('This will update the system and might briefly restart services. Continue?')) return;

                const btn = document.getElementById('btnRunUpdate');
                const consoleEl = document.getElementById('update-console');
                const outputEl = document.getElementById('console-output');

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
                consoleEl.classList.remove('d-none');
                outputEl.textContent = "Requesting update...\n";

                fetch("{{ route('settings.run_update') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Content-Type': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        outputEl.textContent += (data.output || '');
                        if (data.success) {
                            outputEl.textContent += "\n\n[SUCCESS] Update Completed. Reloading...";
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            outputEl.textContent += "\n\n[FAILED] " + (data.message || 'Unknown error');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Retry Update';
                        }
                    })
                    .catch(err => {
                        outputEl.textContent += "\n[ERROR] Request failed: " + err;
                        btn.disabled = false;
                        btn.innerHTML = 'Retry Update';
                    });
            }
        </script>
@endsection