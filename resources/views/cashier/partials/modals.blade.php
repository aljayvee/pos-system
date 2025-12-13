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
                <div id="flow-digital" style="display:none;">
                    @if(\App\Models\Setting::where('key', 'enable_paymongo')->value('value') == '1')
                        <button class="btn btn-primary w-100 py-3" onclick="generatePaymentLink()"><i class="fas fa-qrcode me-2"></i> Generate QR</button>
                    @else
                        <input type="text" id="reference-number" class="form-control form-control-lg" placeholder="Enter Ref #">
                    @endif
                </div>

                {{-- Credit Flow --}}
                <div id="flow-credit" style="display:none;">
                    <input type="text" id="credit-name" class="form-control mb-2" placeholder="Debtor Name">
                    <input type="date" id="credit-due-date" class="form-control">
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