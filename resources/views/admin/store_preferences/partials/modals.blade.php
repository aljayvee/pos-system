{{-- SECURITY VERIFICATION MODAL --}}
<div class="modal fade" id="securityModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm" style="border-radius: 16px;"> <!-- Matches --radius-lg -->
            <div class="modal-header border-0 pb-0 pt-4 px-4 justify-content-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-lock fa-lg"></i>
                </div>
            </div>
            <div class="modal-body text-center px-4 pt-3 pb-4">
                <h6 class="fw-bold text-dark mb-1">Restricted Access</h6>
                <p class="small text-muted mb-3" style="font-size: 0.85rem;">Verification required.</p>
                
                <input type="hidden" id="target-field-id">
                <div class="form-group mb-3">
                    <input type="password" id="admin-password" class="form-control text-center bg-light border-0 fw-bold" 
                           placeholder="Enter Password" style="border-radius: 10px; height: 44px; font-size: 0.9rem;">
                </div>
                
                <div id="password-error" class="text-danger small fw-bold mb-3" style="display:none;">
                    <i class="fas fa-exclamation-circle me-1"></i> Incorrect
                </div>
                
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger btn-sm fw-bold py-2" 
                            style="border-radius: 10px;" onclick="verifyAndReveal()">
                        Verify
                    </button>
                    <button type="button" class="btn btn-light btn-sm fw-bold text-muted" style="border-radius: 10px;" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DISABLE VERIFICATION MODAL --}}
<div class="modal fade" id="disableVerificationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 align-items-center">
                 <div>
                     <h6 class="modal-title fw-bold text-danger">Safety Check</h6>
                </div>
                <button type="button" class="btn-close small" data-bs-dismiss="modal" onclick="cancelDisable()"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning-emphasis rounded-3 d-flex align-items-start mb-4 p-3">
                    <i class="fas fa-exclamation-triangle mt-1 me-3 opacity-50"></i>
                    <div class="small fw-semibold line-height-sm">Disabling Tax Compliance removes VAT information from all receipts.</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Admin Password</label>
                    <input type="password" id="verify-pass" class="form-control bg-light border-0" style="border-radius: 10px;" placeholder="••••••">
                </div>
                
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Store TIN</label>
                        <input type="text" id="verify-tin" class="form-control bg-light border-0" style="border-radius: 10px;" placeholder="Current">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Permit #</label>
                        <input type="text" id="verify-permit" class="form-control bg-light border-0" style="border-radius: 10px;" placeholder="Current">
                    </div>
                </div>

                <div id="verify-error" class="text-danger small fw-bold text-center mb-3"></div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light fw-bold w-50 text-muted" style="border-radius: 10px;" onclick="cancelDisable()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger fw-bold w-50" style="border-radius: 10px;" onclick="processDisable()">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>