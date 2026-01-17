
// ============================================
// CASHIER JS BUNDLE (Consolidated)
// ============================================

// --- 1. CONFIGURATION (Read from DOM) ---
const configEl = document.getElementById('cashier-config');
const CONFIG = {
    pointsValue: Number(configEl?.dataset.pointsValue || 1),
    loyaltyEnabled: Number(configEl?.dataset.loyaltyEnabled || 0),
    paymongoEnabled: Number(configEl?.dataset.paymongoEnabled || 0),
    birEnabled: Number(configEl?.dataset.birEnabled || 0),
    taxType: configEl?.dataset.taxType || 'inclusive',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
    userRole: configEl?.dataset.userRole || 'cashier',
    registerLogsEnabled: configEl?.dataset.registerLogs || '0',
    isRegisterOpen: configEl?.dataset.registerOpen === '1'
};

// Global State
let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
let currentCustomer = { id: 'walk-in', points: 0, balance: 0 };
let activeDiscount = { type: 'na', name: '', card_no: '', amount: 0 };

let html5QrCode = null;
let isOffline = !navigator.onLine;
let isScanning = false;
let scanBuffer = "";
let scanTimeout = null;
let isProcessing = false; // Lock for payments

// Access Global Products (Injected via Window in Blade)
const ALL_PRODUCTS = window.ALL_PRODUCTS || [];

// --- 2. UTILS ---
window.playSuccessBeep = function () {
    const context = new (window.AudioContext || window.webkitAudioContext)();
    const osc = context.createOscillator();
    const gain = context.createGain();
    osc.connect(gain);
    gain.connect(context.destination);
    osc.type = "square";
    osc.frequency.value = 1500;
    gain.gain.value = 0.1;
    osc.start();
    osc.stop(context.currentTime + 0.1);
};

const soundError = new Audio("https://actions.google.com/sounds/v1/alarms/spaceship_alarm.ogg");

window.toggleViewMode = function () {
    const container = document.getElementById('product-list-container');
    const btn = document.getElementById('view-toggle-btn');
    const icon = btn?.querySelector('i');

    if (container.classList.contains('list-view-mode')) {
        container.classList.remove('list-view-mode');
        icon?.classList.remove('fa-th-large');
        icon?.classList.add('fa-list');
        localStorage.setItem('cashier_view_mode', 'grid');
    } else {
        container.classList.add('list-view-mode');
        icon?.classList.remove('fa-list');
        icon?.classList.add('fa-th-large');
        localStorage.setItem('cashier_view_mode', 'list');
    }
};

// Initialize View Mode
document.addEventListener('DOMContentLoaded', () => {
    const savedMode = localStorage.getItem('cashier_view_mode');
    if (savedMode === 'list') {
        document.getElementById('product-list-container')?.classList.add('list-view-mode');
        const btn = document.getElementById('view-toggle-btn');
        if (btn) {
            btn.querySelector('i').classList.remove('fa-list');
            btn.querySelector('i').classList.add('fa-th-large');
        }
    }
});

// --- 3. CART LOGIC ---
const PricingStrategies = {
    MultiBuy: (unitPrice, qty, tiers) => {
        if (!tiers || tiers.length === 0) return unitPrice * qty;
        const sortedTiers = [...tiers].sort((a, b) => b.quantity - a.quantity);
        let remainingQty = qty;
        let totalPrice = 0.0;
        for (const tier of sortedTiers) {
            if (remainingQty >= tier.quantity) {
                const numBundles = Math.floor(remainingQty / tier.quantity);
                totalPrice += numBundles * parseFloat(tier.price);
                remainingQty %= tier.quantity;
            }
        }
        if (remainingQty > 0) totalPrice += remainingQty * unitPrice;
        return totalPrice;
    }
};

window.addToCart = function (productOrId) {
    let product = productOrId;

    if (typeof productOrId !== 'object') {
        product = ALL_PRODUCTS.find(p => p.id == productOrId);
        if (!product) {
            console.error('AddToCart: Product not found via ID', productOrId);
            return;
        }
    } else if (productOrId.id) {
        const liveProduct = ALL_PRODUCTS.find(p => p.id === productOrId.id);
        if (liveProduct) product = liveProduct;
    }


    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        if (existing.qty < product.current_stock) {
            existing.qty++;
        } else {

            soundError.play();
            Swal.fire({
                toast: true,
                icon: 'warning',
                title: 'Max Stock Reached',
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
            return;
        }
    } else {
        if (product.current_stock > 0 || parseFloat(product.current_stock) > 0) {
            cart.push({
                ...product,
                qty: 1,
                max: product.current_stock
            });
        } else {

            soundError.play();
            Swal.fire({
                toast: true,
                icon: 'error',
                title: 'Out of Stock',
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
            return;
        }
    }
    updateCartUI();
};

window.modifyQty = function (index, change) {
    const item = cart[index];
    const newQty = item.qty + change;
    if (newQty <= 0) {
        cart.splice(index, 1);
    } else if (newQty <= item.max) {
        item.qty = newQty;
    }
    updateCartUI();
};

window.removeItem = function (index) {
    cart.splice(index, 1);
    updateCartUI();
};

window.clearCart = function () {
    if (cart.length === 0) return;
    Swal.fire({
        title: 'Clear Cart?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes'
    }).then((res) => {
        if (res.isConfirmed) {
            cart = [];
            updateCartUI();
        }
    });
};

window.overridePrice = function (index) {
    const item = cart[index];
    Swal.fire({
        title: 'Override Price',
        text: `Set new price for ${item.name}`,
        input: 'number',
        inputValue: item.price,
        showCancelButton: true,
        confirmButtonText: 'Update'
    }).then((result) => {
        if (result.isConfirmed) {
            const newPrice = parseFloat(result.value);
            if (newPrice >= 0) {
                item.price = newPrice;
                item.is_overridden = true;
                updateCartUI();
            } else {
                Swal.fire('Invalid Price', 'Price cannot be negative.', 'error');
            }
        }
    });
};

window.openDiscountModal = function () {
    new bootstrap.Modal(document.getElementById('discountModal')).show();
};

window.applyDiscount = function () {
    const type = document.querySelector('input[name="discount_type"]:checked').value;
    const name = document.getElementById('discount-name').value.trim();
    const id = document.getElementById('discount-id').value.trim();

    if (!name || !id) {
        Swal.fire('Error', 'Cardholder Name and ID Number are required.', 'error');
        return;
    }
    activeDiscount = { type: type, name: name, card_no: id, amount: 0 };
    const modalEl = document.getElementById('discountModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
    updateCartUI();
    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 2000, icon: 'success', title: 'Discount Applied' });
};

window.removeDiscount = function () {
    activeDiscount = { type: 'na', name: '', card_no: '', amount: 0 };
    updateCartUI();
};

window.getCartTotals = function () {
    let rawTotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
    let vatableSales = 0;
    let vatAmount = 0;
    let vatExemptSales = 0;
    let totalDiscount = 0;
    let finalTotal = 0;

    // Check if we are in Tax Exclusive or Inclusive mode
    const taxTypeRaw = CONFIG.taxType || 'inclusive';
    const isInclusive = (taxTypeRaw.toLowerCase() === 'inclusive');

    if (CONFIG.birEnabled === 1) {
        if (activeDiscount.type === 'senior' || activeDiscount.type === 'pwd') {
            // SENIOR/PWD LOGIC:
            if (isInclusive) {
                vatExemptSales = rawTotal / 1.12;
            } else {
                vatExemptSales = rawTotal;
            }

            // 2. Apply 20% Discount
            totalDiscount = vatExemptSales * 0.20;
            finalTotal = vatExemptSales - totalDiscount;

            vatAmount = 0;
            vatableSales = 0;

        } else {
            // REGULAR TRANSACTION
            if (isInclusive) {
                // Inclusive: Total 112 -> Vatable 100, VAT 12
                finalTotal = rawTotal;
                vatableSales = rawTotal / 1.12;
                vatAmount = rawTotal - vatableSales;
                vatExemptSales = 0;
            } else {
                // Exclusive: Shelf 100 -> Vatable 100, VAT 12, Total 112
                vatableSales = rawTotal;
                vatAmount = rawTotal * 0.12;
                finalTotal = rawTotal + vatAmount;
                vatExemptSales = 0;
            }
            totalDiscount = 0;
        }
    } else {
        // BIR Disabled - standard calculation
        finalTotal = rawTotal;
    }

    return {
        rawTotal,
        vatableSales,
        vatAmount,
        vatExemptSales,
        totalDiscount,
        finalTotal
    };
};

function updateCartUI() {
    localStorage.setItem('pos_cart', JSON.stringify(cart));

    let html = '';
    if (cart.length === 0) {
        html = `
        <div class="text-center py-5 text-muted empty-cart-msg">
            <i class="fas fa-shopping-basket fa-3x mb-3 opacity-25"></i>
            <p>Your cart is empty</p>
        </div>`;
    } else {
        html = cart.map((item, index) => {
            const lineTotal = item.price * item.qty;
            return `
        <div class="d-flex align-items-center justify-content-between p-2 mb-2 bg-white rounded-3 shadow-sm product-card-wrapper">
            <div class="d-flex align-items-center flex-grow-1" style="overflow:hidden;">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 me-3 bg-light" style="width: 50px; height: 50px;">
                    ${item.image ? `<img src="/storage/${item.image}" class="w-100 h-100 rounded-3 object-fit-cover" loading="lazy">` : '<i class="fas fa-box text-muted opacity-50"></i>'}
                </div>
                <div class="d-flex flex-column" style="min-width:0;">
                    <span class="fw-bold text-dark text-truncate" style="font-size:0.9rem;">${item.name}</span>
                    <small class="text-muted">₱${parseFloat(item.price).toFixed(2)} × ${item.qty}</small>
                </div>
            </div>
            <div class="text-end me-3">
                <div class="text-primary small fw-bold">₱${lineTotal.toFixed(2)}</div>
            </div>
            <div class="d-flex align-items-center bg-light rounded-pill border p-1">
                <button class="btn btn-sm btn-link text-dark fw-bold p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; text-decoration:none;" onclick="modifyQty(${index}, -1)">−</button>
                <span class="fw-bold text-dark text-center" style="width: 24px; font-size: 0.9rem;">${item.qty}</span>
                <button class="btn btn-sm btn-link text-dark fw-bold p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; text-decoration:none;" onclick="modifyQty(${index}, 1)">+</button>
            </div>
            <button class="btn btn-link text-danger p-0 ms-2" onclick="removeItem(${index})"><i class="fas fa-trash-alt"></i></button>
        </div>`;
        }).join('');
    }

    document.querySelectorAll('#cart-items, #cart-items-desktop').forEach(el => el.innerHTML = html);

    // USE HELPER FUNCTION
    const totals = getCartTotals();

    activeDiscount.amount = totals.totalDiscount;

    // --- UPDATE UI ---

    // Update Tax Rows
    const taxRows = document.querySelectorAll('.tax-row');
    if (CONFIG.birEnabled === 1) {
        taxRows.forEach(el => {
            el.classList.remove('d-none');
            el.classList.add('d-flex');
            el.style.removeProperty('display'); // Clean up inline style
        });

        const vatableEl = document.getElementById('vatable-sales-display');
        const exemptEl = document.getElementById('vat-exempt-display');
        const taxEls = document.querySelectorAll('.tax-display');

        if (vatableEl) vatableEl.innerText = totals.vatableSales.toFixed(2);
        if (exemptEl) exemptEl.innerText = totals.vatExemptSales.toFixed(2);
        taxEls.forEach(el => el.innerText = totals.vatAmount.toFixed(2));
    } else {
        taxRows.forEach(el => {
            el.classList.remove('d-flex');
            el.classList.add('d-none');
        });
    }

    // Update Totals
    document.querySelectorAll('.subtotal-display').forEach(el => el.innerText = totals.rawTotal.toFixed(2));
    // TARGET BOTH ID SETS
    document.querySelectorAll(
        '.total-amount-display, #mobile-total-display, #modal-total, #total-amount-display-mobile'
    ).forEach(el => el.innerText = totals.finalTotal.toFixed(2));

    if (document.getElementById('mobile-cart-count')) document.getElementById('mobile-cart-count').innerText = cart.length;

    // Update Discount UI (Desktop & Mobile)
    const discountRows = document.querySelectorAll('#discount-row, #discount-row-mobile');
    const btnAdd = document.getElementById('btn-add-discount');
    const btnRemove = document.getElementById('btn-remove-discount');

    if (activeDiscount.type !== 'na') {
        discountRows.forEach(el => {
            el.style.setProperty('display', 'flex', 'important');
            // Update label and amount inside relative to the row
            const label = el.querySelector('#discount-label') || el.querySelector('#discount-label-mobile');
            const amount = el.querySelector('#discount-amount-display') || el.querySelector('#discount-amount-display-mobile');

            if (label) label.innerText = activeDiscount.type.toUpperCase() + ' 20% (VAT Exempt)';
            if (amount) amount.innerText = totals.totalDiscount.toFixed(2);
        });
        if (btnAdd) btnAdd.classList.add('d-none');
        if (btnRemove) btnRemove.classList.remove('d-none');
    } else {
        discountRows.forEach(el => el.style.setProperty('display', 'none', 'important'));
        if (btnAdd) btnAdd.classList.remove('d-none');
        if (btnRemove) btnRemove.classList.add('d-none');
    }
}

// --- 4. CATEGORY FILTER ---
window.filterCategory = function (cat, btn) {
    if (btn) {
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const others = Array.from(document.querySelectorAll(`.category-btn`)).filter(b => b.innerText.trim() === btn.innerText.trim());
        others.forEach(b => b.classList.add('active'));
    }

    const q = (document.getElementById('product-search-desktop')?.value || document.getElementById('product-search-mobile')?.value || '').toLowerCase();
    let hasResults = false;

    document.querySelectorAll('.product-card-wrapper').forEach(el => {
        const pName = el.dataset.name;
        const pSku = el.dataset.sku ? el.dataset.sku.toString().toLowerCase() : '';
        const pCat = el.dataset.category;

        const matchesCat = (cat === 'all') || (pCat === cat);
        const matchesSearch = !q || pName.includes(q) || pSku.includes(q);

        if (matchesCat && matchesSearch) {
            el.style.display = 'block';
            hasResults = true;
        } else {
            el.style.display = 'none';
        }
    });

    const noRes = document.getElementById('no-results');
    if (noRes) noRes.className = hasResults ? 'd-none' : 'd-block text-center py-5';
};

function bindSearchEvents() {
    ['product-search-desktop', 'product-search-mobile'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('keyup', function () {
                filterCategory('all', document.querySelector('.category-btn'));
            });
        }
    });
}
window.bindSearchEvents = bindSearchEvents;

document.addEventListener('DOMContentLoaded', () => {
    bindSearchEvents();
    // Initial Cart Load
    updateCartUI();
});


// --- 5. CUSTOMER LOGIC ---
window.openCustomerModal = function () {
    new bootstrap.Modal(document.getElementById('customerSelectionModal')).show();
    setTimeout(() => document.getElementById('customer-modal-search').focus(), 500);
};

window.selectCustomer = function (id, name, balance) {
    currentCustomer = { id: id, balance: Number(balance), points: 0 };
    document.querySelectorAll('.selected-customer-name').forEach(el => el.innerText = name);

    // Sync Input Fields if any
    document.querySelectorAll('.customer-id-input').forEach(el => el.value = id);

    // SYNC DROPDOWNS (Desktop & Mobile)
    const desktopSelect = document.getElementById('customer-id');
    const mobileSelect = document.getElementById('customer-id-mobile');
    if (desktopSelect) desktopSelect.value = id;
    if (mobileSelect) mobileSelect.value = id;

    document.querySelectorAll('.header-check').forEach(el => el.classList.add('d-none'));
    const check = document.getElementById(`check-${id}`);
    if (check) check.classList.remove('d-none');
    const modalEl = document.getElementById('customerSelectionModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
};

document.getElementById('customer-modal-search')?.addEventListener('keyup', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.customer-item').forEach(item => {
        const match = item.dataset.name.includes(q);
        item.classList.toggle('d-none', !match);
        item.classList.toggle('d-flex', match);
    });
});

window.openDebtorList = function () {
    const el = document.getElementById('debtorListModal');
    if (el) {
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
    }
};

window.filterDebtors = function () {
    const q = document.getElementById('debtor-search').value.toLowerCase();
    document.querySelectorAll('#debtor-list tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
};

window.openDebtPaymentModal = function (id, name, balance) {
    document.getElementById('pay-debt-customer-id').value = id;
    document.getElementById('pay-debt-name').innerText = name;
    document.getElementById('pay-debt-balance').innerText = parseFloat(balance).toFixed(2);
    document.getElementById('pay-debt-amount').value = '';

    const listEl = document.getElementById('debtorListModal');
    if (listEl) bootstrap.Modal.getInstance(listEl).hide();

    const payEl = document.getElementById('debtPaymentModal');
    if (payEl) bootstrap.Modal.getOrCreateInstance(payEl).show();

    setTimeout(() => document.getElementById('pay-debt-amount').focus(), 500);
};

window.processDebtPayment = function () {
    const id = document.getElementById('pay-debt-customer-id').value;
    const amount = parseFloat(document.getElementById('pay-debt-amount').value);

    if (!amount || amount <= 0) return Swal.fire('Error', 'Invalid Amount', 'error');

    Swal.showLoading();
    fetch("/cashier/credit-payment", { // Corrected route
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CONFIG.csrfToken },
        body: JSON.stringify({ customer_id: id, amount: amount })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success', 'Payment Recorded', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Connection Failed', 'error'));
};


// --- 6. PAYMENT LOGIC ---
window.openPaymentModal = function () {
    if (cart.length === 0) return Swal.fire('Empty', 'Add items first', 'warning');
    document.getElementById('amount-paid').value = '';
    document.getElementById('change-display').innerText = '₱0.00';

    const cashRadio = document.getElementById('pm-cash');
    const creditRadio = document.getElementById('pm-credit');
    const digitalRadio = document.getElementById('pm-digital');

    // Reset Defaults
    if (cashRadio) cashRadio.disabled = false;
    if (creditRadio) creditRadio.disabled = false;
    if (digitalRadio) digitalRadio.disabled = false;

    // FAILSAFE: Re-check Dropdowns (Mobile Priority)
    let activeId = currentCustomer.id;

    // If currently walk-in, double check if Mobile Dropdown has a different value (e.g. 'new')
    // This handles cases where event listeners might have missed the change
    const mobileSelect = document.getElementById('customer-id-mobile');
    const desktopSelect = document.getElementById('customer-id');

    // Check if invisible mobile drawer selection is actually 'new'
    if (mobileSelect && mobileSelect.offsetParent !== null && mobileSelect.value === 'new') {
        activeId = 'new';
        currentCustomer.id = 'new'; // correct the state
    } else if (desktopSelect && desktopSelect.value === 'new') {
        activeId = 'new';
        currentCustomer.id = 'new';
    }


    if (activeId === 'walk-in') {
        if (creditRadio) creditRadio.disabled = true;
        if (cashRadio) cashRadio.checked = true;
    } else if (activeId === 'new') {
        // NEW REQUIREMENT: "New Customer Utang" -> CREDIT ONLY
        if (cashRadio) cashRadio.disabled = true;
        if (digitalRadio) digitalRadio.disabled = true;
        if (creditRadio) {
            creditRadio.disabled = false;
            creditRadio.checked = true;
        }
    } else {
        // Existing Customer: Allow All
        if (cashRadio) cashRadio.checked = true;
    }

    toggleFlow();
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
    setTimeout(() => document.getElementById('amount-paid').focus(), 500);
};

window.toggleFlow = function () {
    const method = document.querySelector('input[name="paymethod"]:checked').value;
    document.getElementById('flow-cash').style.display = method === 'cash' ? 'block' : 'none';
    document.getElementById('flow-digital').style.display = method === 'digital' ? 'block' : 'none';
    document.getElementById('flow-credit').style.display = method === 'credit' ? 'block' : 'none';

    // Show New Customer Fields ONLY if customer is 'new' (which implies Credit due to above logic, but safe to keep check)
    const newDebtorFields = document.getElementById('new-debtor-fields');
    if (newDebtorFields) {
        newDebtorFields.style.display = (currentCustomer.id === 'new') ? 'block' : 'none';
    }
};

window.calculateChange = function () {
    const totals = getCartTotals();
    const total = totals.finalTotal;
    const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
    const change = paid - total;
    const disp = document.getElementById('change-display');
    disp.innerText = change >= 0 ? '₱' + change.toFixed(2) : 'Invalid';
    disp.className = change >= 0 ? 'fw-bold text-success fs-5' : 'fw-bold text-danger fs-5';
};

window.processPayment = function () {
    if (isProcessing) return;

    const method = document.querySelector('input[name="paymethod"]:checked').value;
    const totals = getCartTotals();
    const total = totals.finalTotal;

    if (method === 'cash') {
        const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
        if (paid < total) return Swal.fire('Error', 'Insufficient Cash Payment', 'error');
    } else if (method === 'credit') {
        if (!document.getElementById('credit-due-date').value) return Swal.fire('Error', 'Due Date is required', 'warning');
    }

    // Validate New Customer Details for ANY method
    if (currentCustomer.id === 'new') {
        if (!document.getElementById('credit-name').value) return Swal.fire('Error', 'Customer Name is required', 'warning');
    }

    const payload = {
        cart: cart,
        total_amount: total,
        payment_method: method,
        customer_id: currentCustomer.id,
        amount_paid: method === 'cash' ? document.getElementById('amount-paid').value : 0,
        reference_number: document.getElementById('reference-number')?.value,
        discount: activeDiscount.type !== 'na' ? activeDiscount : null,
        // Send details if Credit OR New Customer
        credit_details: (method === 'credit' || currentCustomer.id === 'new') ? {
            name: document.getElementById('credit-name')?.value,
            due_date: document.getElementById('credit-due-date')?.value, // Only required if credit
            contact: document.getElementById('credit-contact')?.value,
            address: document.getElementById('credit-address')?.value
        } : null
    };

    if (isOffline) {
        saveToOfflineQueue(payload);
        return;
    }

    isProcessing = true;
    Swal.showLoading();
    fetch("/cashier/transaction", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": CONFIG.csrfToken
        },
        body: JSON.stringify(payload)
    })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Server Error');
            return data;
        })
        .then(data => {
            if (data.success) {
                Swal.close();
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                updateLocalStock(cart);
                const paidAmt = parseFloat(payload.amount_paid) || 0;
                const changeAmt = (payload.payment_method === 'cash') ? (paidAmt - payload.total_amount) : 0;
                const custName = document.querySelector(`#customer-id option[value="${currentCustomer.id}"]`)?.text || 'Walk-in Customer';
                showReceiptModal(data, payload.total_amount, changeAmt, custName);
            }
        })
        .catch(err => {
            if (err.message.toLowerCase().includes('fetch') || err.message.toLowerCase().includes('network')) {
                saveToOfflineQueue(payload);
            } else {
                Swal.fire('Validation Error', err.message, 'warning');
            }
        })
        .finally(() => {
            isProcessing = false;
        });
};


window.saveToOfflineQueue = function (data) {
    let queue = JSON.parse(localStorage.getItem('offline_queue_sales')) || [];
    data.offline_id = Date.now();
    queue.push(data);
    localStorage.setItem('offline_queue_sales', JSON.stringify(queue));
    cart = [];
    localStorage.removeItem('pos_cart');
    updateCartUI();
    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
    Swal.fire('Saved Offline', 'Transaction stored locally.', 'info');
};

window.updateConnectionStatus = function () {
    isOffline = !navigator.onLine;
    document.getElementById('connection-status').className = isOffline ? 'status-offline' : 'status-online';
};

window.syncOfflineData = async function () {
    if (isOffline) return;
    let queue = JSON.parse(localStorage.getItem('offline_queue_sales')) || [];
    if (queue.length === 0) return;

    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    Toast.fire({ icon: 'info', title: `Syncing ${queue.length} offline records...` });

    let newQueue = [];
    for (const saleData of queue) {
        try {
            const response = await fetch("/cashier/transaction", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": CONFIG.csrfToken
                },
                body: JSON.stringify(saleData)
            });
            if (!response.ok) throw new Error('Failed');
        } catch (e) {
            newQueue.push(saleData);
        }
    }
    localStorage.setItem('offline_queue_sales', JSON.stringify(newQueue));
    if (newQueue.length === 0) Swal.fire('Synced', 'All offline transactions uploaded.', 'success');
};


window.showReceiptModal = function (data, total, change, customerName) {
    document.getElementById('receipt-modal-amount').innerText = parseFloat(total).toLocaleString('en-US', { minimumFractionDigits: 2 });
    document.getElementById('receipt-modal-change').innerText = parseFloat(change).toLocaleString('en-US', { minimumFractionDigits: 2 });
    document.getElementById('receipt-modal-customer').innerText = customerName;
    document.getElementById('receipt-modal-ref').innerText = data.sale_id || '---';

    const modal = new bootstrap.Modal(document.getElementById('receiptSuccessModal'));
    modal.show();

    document.getElementById('btn-receipt-print').onclick = function () {
        window.open(`/cashier/receipt/${data.sale_id}`, '_blank', 'width=400,height=600');
    };

    document.getElementById('btn-receipt-new').onclick = function () {
        cart = [];
        localStorage.removeItem('pos_cart');
        updateCartUI();
        document.querySelectorAll('.customer-id-input').forEach(el => el.value = 'walk-in');
        document.querySelectorAll('.selected-customer-name').forEach(el => el.innerText = 'Walk-in Customer');
        currentCustomer = { id: 'walk-in', points: 0, balance: 0 };
        modal.hide();
    };
    cart = [];
    localStorage.removeItem('pos_cart');
    updateCartUI();
};


// --- 7. SCANNER LOGIC ---
window.openCameraModal = function () {
    new bootstrap.Modal(document.getElementById('cameraModal')).show();
    startCamera();
};

window.startCamera = function () {
    if (html5QrCode) return;
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    html5QrCode.start({ facingMode: "environment" }, config, onCashierScanSuccess)
        .catch(err => { /* Camera init failed, retrying... */ });
};

function onCashierScanSuccess(decodedText, decodedResult) {
    if (isScanning) return;
    isScanning = true;
    playSuccessBeep();
    setTimeout(() => { isScanning = false; }, 1500);
    handleBatchScan(decodedText);
}

window.stopCamera = function () {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
            html5QrCode = null;
        }).catch(err => console.error(err));
    }
};

document.getElementById('cameraModal')?.addEventListener('hidden.bs.modal', function () {
    window.stopCamera();
});
window.stopCameraAndClose = function () {
    window.stopCamera();
};

document.addEventListener('keydown', function (e) {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    if (e.key === 'Enter') {
        if (scanBuffer.length > 2) {
            handleBatchScan(scanBuffer);
            scanBuffer = "";
        }
    } else if (e.key.length === 1) {
        scanBuffer += e.key;
        if (scanTimeout) clearTimeout(scanTimeout);
        scanTimeout = setTimeout(() => { scanBuffer = ""; }, 100);
    }
});

function handleBatchScan(code) {
    if (!code) return;
    code = code.trim().toLowerCase();
    const product = ALL_PRODUCTS.find(p => (p.sku && p.sku.toLowerCase() === code) || (p.id.toString() === code));

    if (product) {
        addToCart(product);
        playSuccessBeep();
        const Toast = Swal.mixin({ toast: true, position: 'top', showConfirmButton: false, timer: 1000, timerProgressBar: false });
        Toast.fire({ icon: 'success', title: `${product.name} Added` });
    } else {
        playSuccessBeep();
        Swal.fire({ toast: true, position: 'top', icon: 'error', title: 'Item Not Found', timer: 1500, showConfirmButton: false });
    }
}


// --- 8. ADMIN AUTH ---
window.requestAdminAuth = async function (callback) {
    if (CONFIG.userRole === 'admin') {
        callback();
        return;
    }
    const { value: password } = await Swal.fire({
        title: 'Admin Authorization',
        input: 'password',
        inputLabel: 'Enter Admin/Manager Password',
        inputPlaceholder: 'Password',
        showCancelButton: true
    });

    if (password) {
        Swal.showLoading();
        fetch('/cashier/verify-admin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfToken },
            body: JSON.stringify({ password: password })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    callback();
                } else {
                    Swal.fire('Error', 'Invalid Password', 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Auth Failed', 'error'));
    }
};

// --- 9. RETURNS ---
window.openReturnModal = function () {
    const el = document.getElementById('returnModal');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
window.searchSaleForReturn = function () {
    const ref = document.getElementById('return-search').value;
    if (!ref) return Swal.fire('Error', 'Enter Reference No.', 'warning');
    Swal.showLoading();
    fetch(`/cashier/return/search?query=${ref}`, {
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            if (!isJson) {
                const text = await res.text();
                console.error("Non-JSON Response", text);
                throw new Error("Server returned non-JSON response");
            }
            return res.json();
        })
        .then(data => {
            Swal.close();
            if (data.success) {
                const sale = data.sale;
                const tbody = document.getElementById('return-items-body');
                tbody.innerHTML = '';
                document.getElementById('return-sale-id').value = sale.id;

                sale.items.forEach(item => {
                    tbody.innerHTML += `
                            <tr>
                                <td>${item.name}</td>
                                <td>${item.sold_qty}</td>
                                <td>₱${item.price}</td>
                                <td>
                                    <select class="form-select form-select-sm mb-2 return-condition" data-id="${item.product_id}">
                                        <option value="good">Good</option>
                                        <option value="damaged">Damaged</option>
                                    </select>
                                    <input type="number" class="form-control form-control-sm return-qty" 
                                           data-id="${item.product_id}" data-price="${item.price}" 
                                           data-max="${item.available_qty}" min="0" max="${item.available_qty}" 
                                           value="0" onchange="calcRefund()">
                                    <small class="text-muted">Max: ${item.available_qty}</small>
                                </td>
                            </tr>`;
                });
                document.getElementById('return-results').style.display = 'block';
            } else {
                Swal.fire('Error', 'Sale not found', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Search failed', 'error'));
};

window.calcRefund = function () {
    let total = 0;
    document.querySelectorAll('.return-qty').forEach(input => {
        total += input.value * input.dataset.price;
    });
    const el = document.getElementById('total-refund');
    if (el) el.innerText = total.toFixed(2);
};

window.submitReturn = function () {
    const saleId = document.getElementById('return-sale-id').value;
    const items = [];
    document.querySelectorAll('.return-qty').forEach(input => {
        if (input.value > 0) {
            const conditionEl = input.parentNode.querySelector('.return-condition');
            const condition = conditionEl ? conditionEl.value : 'good';

            items.push({
                product_id: input.dataset.id,
                quantity: input.value,
                condition: condition
            });
        }
    });
    if (items.length === 0) return Swal.fire('Error', 'No items selected', 'warning');

    Swal.showLoading();
    fetch('/cashier/return/process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CONFIG.csrfToken
        },
        body: JSON.stringify({ sale_id: saleId, items: items })
    })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            if (!isJson) {
                const text = await res.text();
                console.error("Non-JSON Response", text);
                throw new Error("Server returned non-JSON response");
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire('Success', 'Return processed', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Proccess failed. Check Console.', 'error'));
};


// --- 10. PAYMONGO ---
window.generatePaymentLink = function () {
    const total = parseFloat(document.getElementById('modal-total').innerText.replace(/,/g, ''));
    if (total <= 0) return Swal.fire('Error', 'Invalid Amount', 'error');

    const btn = document.getElementById('btn-gen-qr');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating...';

    fetch('/api/paymongo/create-link', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfToken },
        body: JSON.stringify({ amount: total, description: 'POS Purchase' })
    })
        .then(res => res.json())
        .then(data => {
            if (data.data && data.data.attributes) {
                const checkoutUrl = data.data.attributes.checkout_url;
                const refNum = data.data.id;
                document.getElementById('paymongo-qr').innerHTML = '';
                new QRCode(document.getElementById('paymongo-qr'), { text: checkoutUrl, width: 200, height: 200 });
                document.getElementById('paymongo-controls').style.display = 'none';
                document.getElementById('paymongo-qr-area').style.display = 'block';
                startPaymentPolling(refNum);
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Failed to generate QR', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-qrcode me-2"></i> Generate G-Cash QR';
        });
};

let paymentCheckInterval = null;
window.startPaymentPolling = function (id) {
    paymentCheckInterval = setInterval(() => {
        fetch(`/api/paymongo/status/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.attributes.status === 'paid' || data.attributes.payments.length > 0) {
                    clearInterval(paymentCheckInterval);
                    playSuccessBeep();
                    document.getElementById('reference-number').value = id;
                    processPayment();
                }
            })
            .catch(err => console.error("Polling error:", err));
    }, 3000);
};

window.resetPayMongoUI = function () {
    const btn = document.getElementById('btn-gen-qr');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-qrcode me-2"></i> Generate G-Cash QR';
    }
    const qrArea = document.getElementById('paymongo-qr-area');
    const controls = document.getElementById('paymongo-controls');
    if (qrArea) qrArea.style.display = 'none';
    if (controls) controls.style.display = 'block';
    if (paymentCheckInterval) clearInterval(paymentCheckInterval);
};


// --- 11. REGISTER ---
let currentSessionId = null;
document.addEventListener('DOMContentLoaded', function () {
    checkRegisterStatus();
});

window.checkRegisterStatus = function () {
    if (CONFIG.registerLogsEnabled != '1') {
        document.getElementById('btn-close-register-desktop')?.classList.add('d-none');
        document.getElementById('btn-close-register-mobile')?.classList.add('d-none');
        return;
    }
    fetch(`/cashier/register/status?t=${Date.now()}`)
        .then(res => res.json())
        .then(data => {
            CONFIG.isRegisterOpen = (data.status === 'open');
            const openBtnDesktop = document.getElementById('btn-close-register-desktop');
            const openBtnMobile = document.getElementById('btn-close-register-mobile');

            if (!CONFIG.isRegisterOpen) {
                const openModalEl = document.getElementById('openRegisterModal');
                if (!openModalEl) return;

                if (CONFIG.userRole === 'admin') {
                    const modal = new bootstrap.Modal(openModalEl, { backdrop: 'static', keyboard: false });
                    const modalHeader = openModalEl.querySelector('.modal-header');
                    if (modalHeader) modalHeader.style.display = 'block';
                    modal.show();
                } else {
                    const modalBody = openModalEl.querySelector('.modal-body');
                    const modalFooter = openModalEl.querySelector('.modal-footer');
                    const modalHeader = openModalEl.querySelector('.modal-header');

                    if (modalHeader) modalHeader.style.display = 'none';
                    if (modalFooter) modalFooter.style.display = 'none';

                    modalBody.innerHTML = `
                            <div class="text-center py-5">
                                <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                    <i class="fas fa-store-slash fa-3x opacity-50"></i>
                                </div>
                                <h4 class="fw-bold text-dark">Register is Closed</h4>
                                <p class="text-muted mb-4 px-4">There is no active session.</p>
                                <div class="d-flex justify-content-center gap-2">
                                     <button onclick="window.location.reload()" class="btn btn-primary rounded-pill px-4 fw-bold py-2 shadow-sm"><i class="fas fa-sync-alt me-2"></i> Check Status</button>
                                     <form action="/logout" method="POST" class="d-inline">
                                         <input type="hidden" name="_token" value="${CONFIG.csrfToken}">
                                         <button type="submit" class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm py-2"><i class="fas fa-sign-out-alt me-2"></i> Logout</button>
                                     </form>
                                </div>
                            </div>`;
                    const modal = new bootstrap.Modal(openModalEl, { backdrop: 'static', keyboard: false });
                    modal.show();
                }

                if (openBtnDesktop) openBtnDesktop.classList.add('d-none');
                if (openBtnMobile) openBtnMobile.classList.add('d-none');
            } else {
                currentSessionId = data.session.id;
                if (openBtnDesktop) openBtnDesktop.classList.remove('d-none');
                if (openBtnMobile) openBtnMobile.classList.remove('d-none');
                const openModalEl = document.getElementById('openRegisterModal');
                const existingModal = bootstrap.Modal.getInstance(openModalEl);
                if (existingModal) existingModal.hide();
            }
        })
        .catch(err => console.error('Register Status Check Failed:', err));
};

window.submitOpenRegister = function (e) {
    e.preventDefault();
    const amount = document.getElementById('opening_float').value;
    Swal.showLoading();
    fetch('/cashier/register/open', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfToken },
        body: JSON.stringify({ opening_amount: amount })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Register Opened', timer: 1500, showConfirmButton: false }).then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Failed', 'error');
            }
        });
};

window.showCloseRegisterModal = function () {
    if (!currentSessionId) { checkRegisterStatus(); return; }
    document.getElementById('close_session_id').value = currentSessionId;
    const el = document.getElementById('closeRegisterModal');
    if (el) {
        bootstrap.Modal.getOrCreateInstance(el).show();
    }
};

window.submitCloseRegister = function (e) {
    e.preventDefault();
    const amount = document.getElementById('closing_amount').value;
    const notes = document.getElementById('closing_notes').value;
    const sessionId = document.getElementById('close_session_id').value;

    if (!amount) return Swal.fire('Error', 'Enter cash count.', 'warning');

    Swal.fire({
        title: `Confirm Closing`,
        html: `Actual Cash: <h2 class="text-danger fw-bold">₱${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</h2>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Close'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.showLoading();
            fetch('/cashier/register/close', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrfToken },
                body: JSON.stringify({ session_id: sessionId, closing_amount: amount, notes: notes })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Closed', 'Session closed.', 'success').then(() => {
                            if (data.z_reading) window.open('/cashier/reading/z', '_blank', 'width=400,height=600');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
        }
    });
};


// --- 12. LIVE SYNC ---
window.startLiveStockSync = function () {
    setInterval(() => {
        if (isOffline) return;
        fetch('/cashier/inventory/sync')
            .then(res => res.json())
            .then(data => {
                data.forEach(item => {
                    const p = ALL_PRODUCTS.find(x => x.id === item.id);
                    if (p) {
                        // API returns "stock", JS uses "current_stock"
                        const newStock = item.stock !== undefined ? parseInt(item.stock) : p.current_stock;


                        p.current_stock = newStock;
                        // p.pricing_tiers = item.pricing_tiers; // API does not return pricing_tiers currently
                    }
                    const badge = document.getElementById(`product-stock-${item.id}`);
                    if (badge) {
                        if (p && p.current_stock <= (p.reorder_point || 10)) {
                            badge.style.display = 'inline-block';
                            badge.innerText = `${p.current_stock} Left`;
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                });
            })
            .catch(err => console.warn('Sync skipped'));
    }, 10000);
};

window.updateLocalStock = function (soldItems) {
    soldItems.forEach(sold => {
        const p = ALL_PRODUCTS.find(x => x.id === sold.id);
        if (p) {
            p.current_stock -= sold.qty;
            if (p.current_stock < 0) p.current_stock = 0;
            const badge = document.getElementById(`product-stock-${sold.id}`);
            if (badge) {
                if (p.current_stock <= (p.reorder_point || 10)) {
                    badge.style.display = 'inline-block';
                    badge.innerText = `${p.current_stock} Left`;
                }
            }
        }
    });
};


// --- 13. SHORTCUTS ---
document.addEventListener('keydown', function (e) {
    if (e.key === 'F2') {
        e.preventDefault();
        toggleViewMode();
    }
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
        e.preventDefault();
        const searchInput = document.getElementById('product-search-desktop') || document.getElementById('product-search-mobile');
        if (searchInput) searchInput.focus();
    }
    if (e.key === 'F4') {
        e.preventDefault();
        openPaymentModal();
    }
    if (e.key === 'F8') {
        e.preventDefault();
        clearCart();
    }
});

// START
startLiveStockSync();

document.addEventListener('DOMContentLoaded', () => {
    const btnNewSale = document.getElementById('btn-receipt-new');
    if (btnNewSale) {
        btnNewSale.addEventListener('click', () => {
            window.location.reload();
        });
    }

    // Event Delegation for Customer Dropdown (More Robust)
    document.addEventListener('change', function (e) {
        if (e.target && (e.target.id === 'customer-id' || e.target.id === 'customer-id-mobile')) {
            const target = e.target;
            const selectedOpt = target.options[target.selectedIndex];
            const val = target.value;

            // Update State
            if (val === 'new') {
                currentCustomer = { id: 'new', balance: 0, points: 0 };
            } else if (val === 'walk-in') {
                currentCustomer = { id: 'walk-in', balance: 0, points: 0 };
            } else {
                currentCustomer = {
                    id: val,
                    balance: parseFloat(selectedOpt.dataset.balance || 0),
                    points: parseFloat(selectedOpt.dataset.points || 0)
                };
            }

            // Sync other dropdown
            const desktopSelect = document.getElementById('customer-id');
            const mobileSelect = document.getElementById('customer-id-mobile');

            if (target === desktopSelect && mobileSelect) {
                mobileSelect.value = val;
            } else if (target === mobileSelect && desktopSelect) {
                desktopSelect.value = val;
            }
        }
    });
});
