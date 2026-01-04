{{-- 1. PAYMENT MODAL --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Checkout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="text-center mb-4">
                    <small class="text-muted text-uppercase fw-bold">Amount Due</small>
                    <h1 class="text-primary fw-extrabold display-4">₱<span id="modal-total">0.00</span></h1>
                </div>

                {{-- Payment Methods --}}
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="paymethod" id="pm-cash" value="cash" checked
                            onchange="toggleFlow()">
                        <label class="btn btn-outline-secondary w-100 py-3 rounded-3 fw-bold" for="pm-cash">
                            <i class="fas fa-money-bill-wave d-block mb-1 fs-4"></i> Cash
                        </label>
                    </div>
                    @if(config('safety_flag_features.online_payment'))
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="paymethod" id="pm-digital" value="digital"
                                onchange="toggleFlow()">
                            <label class="btn btn-outline-secondary w-100 py-3 rounded-3 fw-bold" for="pm-digital">
                                <i class="fas fa-qrcode d-block mb-1 fs-4"></i> E-Wallet
                            </label>
                        </div>
                    @endif
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="paymethod" id="pm-credit" value="credit" disabled
                            onchange="toggleFlow()">
                        <label class="btn btn-outline-secondary w-100 py-3 rounded-3 fw-bold" for="pm-credit">
                            <i class="fas fa-user-clock d-block mb-1 fs-4"></i> Utang
                        </label>
                    </div>
                </div>

                {{-- Cash Flow --}}
                <div id="flow-cash">
                    <div class="input-group input-group-lg mb-2">
                        <span class="input-group-text bg-light border-0">₱</span>
                        <input type="number" id="amount-paid" class="form-control border-0 bg-light fw-bold"
                            placeholder="0.00" oninput="calculateChange()" autofocus>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Change:</span>
                        <span class="fw-bold text-success fs-5" id="change-display">₱0.00</span>
                    </div>
                </div>

                {{-- Digital Flow --}}
                {{-- Digital Flow (G-Cash / PayMongo) --}}
                {{-- Digital Flow --}}
                <div id="flow-digital" style="display:none;">
                    @if(\App\Models\Setting::where('key', 'enable_paymongo')->value('value') == '1')
                        {{-- PAYMONGO MODE --}}
                        <div id="paymongo-controls">
                            <button id="btn-gen-qr" class="btn btn-primary w-100 py-3 fw-bold shadow-sm"
                                onclick="generatePaymentLink()">
                                <i class="fas fa-qrcode me-2"></i> Generate QR Code
                            </button>
                        </div>

                        {{-- QR Display Area --}}
                        <div id="paymongo-qr-area" class="text-center mt-3" style="display:none;">
                            <p class="mb-2 fw-bold text-primary animate-pulse">Scan to Pay</p>
                            <div class="d-flex justify-content-center mb-3">
                                <div id="qrcode-container" class="p-3 bg-white border rounded shadow-sm"></div>
                            </div>
                            <p class="small text-muted mb-0">Waiting for payment confirmation...</p>
                            <div class="spinner-border text-primary spinner-border-sm mt-2" role="status"></div>
                        </div>

                        <input type="hidden" id="reference-number">
                    @else
                        {{-- MANUAL MODE --}}
                        <label class="form-label fw-bold small text-muted">Reference Number</label>
                        <input type="text" id="reference-number" class="form-control form-control-lg fw-bold text-center"
                            placeholder="Enter Ref #">
                    @endif
                </div>

                {{-- FIND THIS SECTION IN modals.blade.php --}}
                <div id="flow-credit" style="display:none;">
                    <div class="bg-light p-3 rounded-3 border">

                        {{-- Conditional Fields for New Profile --}}
                        <div id="new-debtor-fields">
                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-plus me-2"></i>New Debtor
                                Details</h6>

                            <div class="form-floating mb-2">
                                <input type="text" id="credit-name" class="form-control fw-bold"
                                    placeholder="Customer Name">
                                <label>Full Name <span class="text-danger">*</span></label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="text" id="credit-contact" class="form-control" placeholder="Mobile No.">
                                <label>Mobile Number</label>
                            </div>

                            <div class="form-floating mb-2">
                                <textarea id="credit-address" class="form-control" placeholder="Address"
                                    style="height: 60px"></textarea>
                                <label>Full Address</label>
                            </div>

                            <hr class="my-3">
                        </div>

                        {{-- Always Visible Due Date --}}
                        <div class="form-floating">
                            <input type="date" id="credit-due-date" class="form-control fw-bold" placeholder="Due Date">
                            <label class="fw-bold text-dark">Due Date <span class="text-danger">*</span></label>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-dark w-100 py-3 rounded-3 fw-bold fs-5"
                    onclick="processPayment()">COMPLETE</button>
            </div>
        </div>
    </div>
</div>

{{-- 2. RETURN MODAL --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0 rounded-top-4">
                <h5 class="modal-title fw-bold">Process Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-4">
                    <input type="text" id="return-search" class="form-control" placeholder="Enter Sale ID">
                    <button class="btn btn-dark" onclick="searchSaleForReturn()">Search</button>
                    <input type="hidden" id="return-sale-id">
                </div>
                <div id="return-results" style="display:none;">
                    <table class="table align-middle">
                        <tbody id="return-items-body"></tbody>
                    </table>
                    <div class="text-end fw-bold">Refund: <span class="text-danger">₱<span
                                id="total-refund">0.00</span></span></div>
                    <button class="btn btn-warning w-100 mt-3 fw-bold" onclick="submitReturn()">Confirm Refund</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. CAMERA MODAL --}}
<div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0">
                <h5 class="fw-bold">Scan Barcode</h5><button class="btn-close" data-bs-dismiss="modal"
                    onclick="stopCamera()"></button>
            </div>
            <div class="modal-body p-0">
                <div id="reader" style="width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

{{-- 4. DEBTOR LIST MODAL (NEW) --}}
<div class="modal fade" id="debtorListModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Customers with Debt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="debtor-search" class="form-control mb-3" placeholder="Search debtor..."
                    onkeyup="filterDebtors()">
                <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                    @foreach($customers as $c)
                        @if(($c->balance ?? 0) > 0)
                            <button
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center debtor-row"
                                data-name="{{ strtolower($c->name) }}"
                                onclick="openDebtPaymentModal('{{ $c->id }}', '{{ $c->name }}', '{{ $c->balance }}')">
                                <span class="fw-bold">{{ $c->name }}</span>
                                <span class="badge bg-danger rounded-pill">₱{{ number_format($c->balance, 2) }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 5. PAY DEBT MODAL (NEW) --}}
<div class="modal fade" id="debtPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h6 class="modal-title fw-bold">Collect Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <input type="hidden" id="pay-debt-customer-id">
                <h5 id="pay-debt-name" class="fw-bold mb-1">Customer</h5>
                <small class="text-danger fw-bold text-uppercase">Current Debt</small>
                <h2 class="text-danger fw-extrabold mb-3">₱<span id="pay-debt-balance">0.00</span></h2>

                <div class="form-floating mb-2">
                    <input type="number" id="pay-debt-amount" class="form-control fw-bold" placeholder="Amount">
                    <label>Payment Amount</label>
                </div>
                <button class="btn btn-danger w-100 fw-bold py-2" onclick="processDebtPayment()">
                    CONFIRM PAYMENT
                </button>
            </div>
        </div>
    </div>
</div>
{{-- [PHASE 11] DISCOUNT MODAL --}}
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-success"><i class="fas fa-percent me-2"></i>Apply Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 small fw-bold mb-3">
                    <i class="fas fa-info-circle me-1"></i> BIR Requirement: SC/PWD Name & ID are mandatory.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Discount Type</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="discount_type" id="dt-sc" value="sc" checked>
                            <label class="btn btn-outline-success w-100 py-2 fw-bold" for="dt-sc">
                                Senior Citizen
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="discount_type" id="dt-pwd" value="pwd">
                            <label class="btn btn-outline-success w-100 py-2 fw-bold" for="dt-pwd">
                                PWD
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="text" id="discount-name" class="form-control fw-bold" placeholder="Name">
                    <label>Cardholder Name <span class="text-danger">*</span></label>
                </div>

                <div class="form-floating mb-4">
                    <input type="text" id="discount-id" class="form-control fw-bold" placeholder="ID Number">
                    <label>ID Number <span class="text-danger">*</span></label>
                </div>

                <button class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow-sm" onclick="applyDiscount()">
                    APPLY 20% DISCOUNT
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 7. OPEN REGISTER MODAL --}}
<div class="modal fade" id="openRegisterModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                    style="width: 80px; height: 80px;">
                    <i class="fas fa-cash-register fa-3x"></i>
                </div>
                <h4 class="fw-bold mb-2">Open Register</h4>
                <p class="text-muted mb-4">Please enter the starting cash amount to begin.</p>

                <div class="form-floating mb-4">
                    <input type="number" id="opening-amount"
                        class="form-control form-control-lg fw-bold text-center fs-2" placeholder="0.00" value="0.00">
                    <label class="d-flex w-100 justify-content-center">Starting Cash (₱)</label>
                </div>

                <button class="btn btn-primary w-100 py-3 fw-bold rounded-3 fs-5 shadow-sm"
                    onclick="submitOpenRegister()">
                    OPEN REGISTER
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 8. CLOSE REGISTER MODAL --}}
<div class="modal fade" id="closeRegisterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Close Register</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="close_session_id">
                <div class="alert alert-warning border-0 d-flex align-items-center gap-3 mb-4">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                    <div class="small lh-sm">This will end the current session. Please verify the actual cash in drawer.
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    {{-- BLIND BALANCING: Hidden Expected Cash --}}
                    {{--
                    <div class="col-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Expected
                            Cash</small>
                        <h4 class="fw-bold text-dark">₱<span id="expected-cash-display">0.00</span></h4>
                    </div>
                    --}}
                    <div class="col-12">
                        <div class="alert alert-info py-2 small fw-bold">
                            <i class="fas fa-info-circle me-1"></i> Blind Audit: Count physical cash.
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-4">
                    <input type="number" id="closing-actual-cash" class="form-control fw-bold text-center fs-3"
                        placeholder="0.00">
                    <label>Actual Cash in Drawer (₱)</label>
                </div>

                <div class="form-floating mb-4">
                    <textarea id="closing-notes" class="form-control" placeholder="Notes"
                        style="height: 80px"></textarea>
                    <label>Notes (Optional)</label>
                </div>

                <button class="btn btn-danger w-100 py-3 fw-bold rounded-3 shadow-sm" onclick="submitCloseRegister()">
                    CLOSE SESSION
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 6. CUSTOMER SELECTION MODAL (Bottom Sheet Style) --}}
<div class="modal fade" id="customerSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-bottom modal-dialog-scrollable" style="z-index: 1060;">
        <div class="modal-content rounded-top-4 border-0 shadow-lg" style="max-height: 85vh;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Select Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 sticky-top bg-white border-bottom shadow-sm">
                    <div class="position-relative">
                        <i
                            class="fas fa-search text-muted position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                        <input type="text" id="customer-modal-search"
                            class="form-control form-control-lg bg-light border-0 ps-5 rounded-pill"
                            placeholder="Search customer...">
                    </div>
                </div>

                <div class="p-3 bg-light">
                    <label class="small fw-bold text-uppercase text-muted mb-2 ms-1">Quick Actions</label>
                    <div class="row g-2">
                        {{-- Walk-in Button --}}
                        <div class="col-6">
                            <div class="card border-0 shadow-sm h-100"
                                onclick="selectCustomer('walk-in', 'Walk-in Customer', 0)" style="cursor: pointer;">
                                <div
                                    class="card-body p-3 text-center d-flex flex-column align-items-center justify-content-center">
                                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center mb-2"
                                        style="width: 48px; height: 48px;">
                                        <i class="fas fa-walking fa-lg"></i>
                                    </div>
                                    <span class="fw-bold text-dark small">Walk-in</span>
                                    <small class="text-muted" style="font-size: 0.65rem;">Default</small>
                                </div>
                                <div class="position-absolute top-0 end-0 p-2 d-none header-check" id="check-walk-in">
                                    <i class="fas fa-check-circle text-primary"></i>
                                </div>
                            </div>
                        </div>

                        {{-- New Profile Button --}}
                        <div class="col-6">
                            <div class="card border-0 shadow-sm h-100 bg-primary text-white"
                                onclick="selectCustomer('new', '+ Create New Profile', 0)"
                                style="cursor: pointer; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                                <div
                                    class="card-body p-3 text-center d-flex flex-column align-items-center justify-content-center">
                                    <div class="bg-white bg-opacity-25 text-white rounded-circle d-flex align-items-center justify-content-center mb-2"
                                        style="width: 48px; height: 48px;">
                                        <i class="fas fa-user-plus fa-lg"></i>
                                    </div>
                                    <span class="fw-bold small">+ New Customer Utang</span>
                                    <small class="text-white text-opacity-75" style="font-size: 0.65rem;">Credit
                                        Only</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list-group list-group-flush border-top" id="customer-modal-list">
                    <div class="px-4 py-3 bg-white border-bottom">
                        <label class="small fw-bold text-uppercase text-muted">Registered Database</label>
                    </div>

                    {{-- Dynamic List --}}
                    @foreach($customers as $c)
                        <button
                            class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center justify-content-between customer-item border-bottom"
                            data-name="{{ strtolower($c->name) }}"
                            onclick="selectCustomer('{{ $c->id }}', '{{ $c->name }}', {{ $c->balance ?? 0 }})">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold small"
                                    style="width: 40px; height: 40px; font-size: 0.9rem;">
                                    {{ substr($c->name, 0, 1) }}
                                </div>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="fw-bold text-dark">{{ $c->name }}</span>
                                    @if(($c->balance ?? 0) > 0)
                                        <small class="text-danger fw-bold" style="font-size: 0.75rem;"><i
                                                class="fas fa-exclamation-circle me-1"></i>Due:
                                            ₱{{ number_format($c->balance, 2) }}</small>
                                    @else
                                        <small class="text-muted" style="font-size: 0.75rem;">No active debt</small>
                                    @endif
                                </div>
                            </div>
                            <i class="fas fa-check-circle text-primary fa-lg d-none header-check"
                                id="check-{{ $c->id }}"></i>
                        </button>
                    @endforeach

                    @if($customers->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="far fa-folder-open fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No customers found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Bottom Sheet Animation */
    .modal-dialog-bottom {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        margin: 0;
        width: 100%;
        max-width: 100%;
        transform: translate3d(0, 100%, 0);
        transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog-bottom {
        transform: translate3d(0, 0, 0);
    }
</style>