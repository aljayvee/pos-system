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
                        <input type="radio" class="btn-check" name="paymethod" id="pm-cash" value="cash" checked onchange="toggleFlow()">
                        <label class="btn btn-outline-secondary w-100 py-3 rounded-3 fw-bold" for="pm-cash">
                            <i class="fas fa-money-bill-wave d-block mb-1 fs-4"></i> Cash
                        </label>
                    </div>
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="paymethod" id="pm-digital" value="digital" onchange="toggleFlow()">
                        <label class="btn btn-outline-secondary w-100 py-3 rounded-3 fw-bold" for="pm-digital">
                            <i class="fas fa-qrcode d-block mb-1 fs-4"></i> G-Cash
                        </label>
                    </div>
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="paymethod" id="pm-credit" value="credit" disabled onchange="toggleFlow()">
                        <label class="btn btn-outline-secondary w-100 py-3 rounded-3 fw-bold" for="pm-credit">
                            <i class="fas fa-user-clock d-block mb-1 fs-4"></i> Credit
                        </label>
                    </div>
                </div>

                {{-- Cash Flow --}}
                <div id="flow-cash">
                    <div class="input-group input-group-lg mb-2">
                        <span class="input-group-text bg-light border-0">₱</span>
                        <input type="number" id="amount-paid" class="form-control border-0 bg-light fw-bold" placeholder="0.00" oninput="calculateChange()" autofocus>
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
                            <button id="btn-gen-qr" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" onclick="generatePaymentLink()">
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
                        <input type="text" id="reference-number" class="form-control form-control-lg fw-bold text-center" placeholder="Enter Ref #">
                    @endif
                </div>

                {{-- FIND THIS SECTION IN modals.blade.php --}}
<div id="flow-credit" style="display:none;">
    <div class="bg-light p-3 rounded-3 border">
        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-plus me-2"></i>New Debtor Details</h6>
        
        <div class="form-floating mb-2">
            <input type="text" id="credit-name" class="form-control fw-bold" placeholder="Customer Name">
            <label>Full Name <span class="text-danger">*</span></label>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-6">
                <div class="form-floating">
                    <input type="text" id="credit-contact" class="form-control" placeholder="Mobile No.">
                    <label>Mobile Number</label>
                </div>
            </div>
            <div class="col-6">
                <div class="form-floating">
                    <input type="date" id="credit-due-date" class="form-control" placeholder="Due Date">
                    <label>Due Date <span class="text-danger">*</span></label>
                </div>
            </div>
        </div>

        <div class="form-floating">
            <textarea id="credit-address" class="form-control" placeholder="Address" style="height: 80px"></textarea>
            <label>Full Address</label>
        </div>
    </div>
</div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-dark w-100 py-3 rounded-3 fw-bold fs-5" onclick="processPayment()">COMPLETE</button>
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
                </div>
                <div id="return-results" style="display:none;">
                    <table class="table align-middle"><tbody id="return-items-body"></tbody></table>
                    <div class="text-end fw-bold">Refund: <span class="text-danger">₱<span id="total-refund">0.00</span></span></div>
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
            <div class="modal-header border-0"><h5 class="fw-bold">Scan Barcode</h5><button class="btn-close" data-bs-dismiss="modal" onclick="stopCamera()"></button></div>
            <div class="modal-body p-0"><div id="reader" style="width: 100%;"></div></div>
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
                <input type="text" id="debtor-search" class="form-control mb-3" placeholder="Search debtor..." onkeyup="filterDebtors()">
                <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                    @foreach($customers as $c)
                        @if(($c->balance ?? 0) > 0)
                        <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center debtor-row" 
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