{{-- SECURITY VERIFICATION MODAL (For revealing keys) --}}
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
                
                <div id="password-error" class="text-danger small fw-bold mb-2" style="display:none;"></div>
                
                <button type="button" class="btn btn-danger w-100 mt-2" onclick="verifyAndReveal()">Reveal Data</button>
            </div>
        </div>
    </div>
</div>

{{-- DISABLE VERIFICATION MODAL (For turning off BIR/Tax) --}}
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