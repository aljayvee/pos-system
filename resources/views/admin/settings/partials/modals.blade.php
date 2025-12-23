{{-- SECURITY VERIFICATION MODAL --}}
<div class="modal fade" id="securityModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="fas fa-lock me-2"></i>Admin Verification</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle" style="width: 60px; height: 60px;">
                        <i class="fas fa-fingerprint fa-2x"></i>
                    </div>
                </div>
                <h6 class="fw-bold text-dark mb-1">Authentication Required</h6>
                <p class="small text-muted mb-4">Enter your admin password to reveal this sensitive data.</p>
                
                <input type="hidden" id="target-field-id">
                <div class="form-floating mb-3">
                    <input type="password" id="admin-password" class="form-control border-light bg-light fw-bold text-center" placeholder="Password">
                    <label class="w-100 text-center text-muted">Enter Password</label>
                </div>
                
                <div id="password-error" class="text-danger small fw-bold mb-3" style="display:none;"></div>
                
                <button type="button" class="btn btn-danger w-100 shadow-sm rounded-pill fw-bold py-2" onclick="verifyAndReveal()">
                    Reveal Data
                </button>
            </div>
        </div>
    </div>
</div>

{{-- DISABLE VERIFICATION MODAL --}}
<div class="modal fade" id="disableVerificationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="fas fa-shield-alt me-2"></i>Security Check</h6>
                <button type="button" class="btn-close btn-close-white" onclick="cancelDisable()"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning small mb-4 border-0 bg-warning bg-opacity-10 text-warning-emphasis rounded-4 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
                    <div>To disable <strong>Tax Compliance</strong>, you must verify your identity using current credentials.</div>
                </div>
                
                <div class="mb-3">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Admin Password</label>
                    <input type="password" id="verify-pass" class="form-control form-control-lg bg-light border-light shadow-sm">
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="small fw-bold text-muted text-uppercase mb-1">Current TIN</label>
                        <input type="text" id="verify-tin" class="form-control bg-light border-light shadow-sm" placeholder="Saved TIN">
                    </div>
                    <div class="col-6">
                        <label class="small fw-bold text-muted text-uppercase mb-1">Current Permit</label>
                        <input type="text" id="verify-permit" class="form-control bg-light border-light shadow-sm" placeholder="Saved Permit">
                    </div>
                </div>

                <div id="verify-error" class="text-danger small fw-bold text-center mb-3"></div>
                
                <div class="d-grid">
                    <button type="button" class="btn btn-danger fw-bold py-3 shadow-lg rounded-4" onclick="processDisable()">
                        CONFIRM DISABLE
                    </button>
                    <button type="button" class="btn btn-light fw-bold py-2 mt-2 rounded-4 text-muted" onclick="cancelDisable()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>