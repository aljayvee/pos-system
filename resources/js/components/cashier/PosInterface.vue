<template>
  <div class="d-flex flex-column h-100 w-100 bg-light font-sans position-absolute top-0 start-0 overflow-hidden">
    
    <div class="px-3 py-2 text-white d-flex align-items-center justify-content-between shrink-0 shadow-sm" style="background-color: #1e1e2d; height: 64px;">
        
        <div class="d-flex align-items-center me-2 overflow-hidden">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white me-2 fw-bold flex-shrink-0" style="width: 38px; height: 38px;">
                <i class="fas fa-store"></i>
            </div>
            <div class="lh-1 overflow-hidden">
                <div class="fw-bold text-truncate" style="font-size: 0.9rem; max-width: 120px;">{{ storeName }}</div>
                <small class="text-white-50 d-block text-truncate" style="font-size: 0.7rem; max-width: 120px;">{{ cashierName }}</small>
            </div>
        </div>

        <div class="flex-fill mx-2 position-relative">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                <input 
                    v-model="searchQuery" 
                    type="text" 
                    class="form-control border-0 shadow-none" 
                    placeholder="Search / Scan..." 
                    id="barcodeInput"
                    @keydown.enter="handleBarcodeEnter"
                >
                <button class="btn btn-warning fw-bold text-dark" type="button" @click="toggleScanner">
                    <i class="fas fa-camera d-none d-sm-inline me-1"></i> Scan
                </button>
            </div>
        </div>

        <div class="d-flex align-items-center">
            
            <div class="d-none d-md-flex gap-2 align-items-center">
                <a href="/cashier/debtors" class="btn btn-outline-light btn-sm" title="Pay Debt"><i class="fas fa-book me-1"></i> Debt</a>
                <a href="/cashier/return/search" class="btn btn-outline-light btn-sm" title="Return"><i class="fas fa-undo me-1"></i> Return</a>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Reports</button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="/cashier/reading/x-reading">X-Reading (Shift)</a></li>
                        <li><a class="dropdown-item" href="/cashier/reading/z-reading">Z-Reading (End Day)</a></li>
                    </ul>
                </div>
                <form action="/logout" method="POST" class="ms-2">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <button class="btn btn-danger btn-sm rounded-circle" style="width: 32px; height: 32px;"><i class="fas fa-power-off"></i></button>
                </form>
            </div>

            <div class="dropdown d-md-none ms-1">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                    <li><h6 class="dropdown-header">Utilities</h6></li>
                    <li><a class="dropdown-item" href="/cashier/debtors"><i class="fas fa-book me-2 text-warning"></i>Pay Debt / Utang</a></li>
                    <li><a class="dropdown-item" href="/cashier/return/search"><i class="fas fa-undo me-2 text-danger"></i>Process Return</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Reports</h6></li>
                    <li><a class="dropdown-item" href="/cashier/reading/x-reading"><i class="fas fa-file-invoice me-2 text-info"></i>X-Reading</a></li>
                    <li><a class="dropdown-item" href="/cashier/reading/z-reading"><i class="fas fa-file-invoice-dollar me-2 text-success"></i>Z-Reading</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="/logout" method="POST" class="d-block w-100">
                            <input type="hidden" name="_token" :value="csrfToken">
                            <button class="dropdown-item text-danger fw-bold"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <div class="d-flex flex-fill overflow-hidden position-relative">
        
        <div class="d-flex flex-column flex-fill bg-light position-relative" :class="{ 'd-none d-md-flex': mobileTab === 'cart' }">
            <div class="d-flex gap-2 p-2 overflow-auto bg-white border-bottom custom-scrollbar shrink-0">
                <button class="btn btn-sm rounded-pill border px-3 fw-bold" :class="selectedCategory === '' ? 'btn-dark' : 'btn-light text-muted'" @click="selectedCategory = ''">All</button>
                <button v-for="cat in categories" :key="cat.id" class="btn btn-sm rounded-pill border px-3 fw-bold" :class="selectedCategory === cat.id ? 'btn-dark' : 'btn-light text-muted'" @click="selectedCategory = cat.id">{{ cat.name }}</button>
            </div>

            <div class="flex-fill overflow-auto p-2 p-md-3 custom-scrollbar">
                <div class="row g-2">
                    <div v-for="product in filteredProducts" :key="product.id" class="col-6 col-sm-4 col-md-3 col-xl-2" @click="addToCart(product)">
                        <div class="card h-100 border-0 shadow-sm product-card cursor-pointer">
                            <div class="card-body p-2 text-center d-flex flex-column">
                                <div class="mb-2 mx-auto rounded d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold fs-4" style="width: 50px; height: 50px;">
                                    {{ product.name.charAt(0) }}
                                </div>
                                <h6 class="card-title text-dark fw-bold mb-1 text-truncate w-100 small">{{ product.name }}</h6>
                                <div class="mt-auto">
                                    <span class="badge bg-danger mb-1" v-if="product.stock <= 5">Low: {{ product.stock }}</span>
                                    <div class="fw-bold text-primary">₱{{ formatPrice(product.price) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column bg-white border-start shadow-lg h-100 cart-panel" 
             :class="{ 'd-none d-md-flex': mobileTab === 'menu', 'w-100': mobileTab === 'cart' }"
             style="width: 400px; z-index: 100;">
            
            <div class="p-3 bg-light border-bottom">
                <div class="d-flex gap-2 mb-2">
                    <button class="btn btn-sm flex-fill fw-bold" :class="customerType === 'walkin' ? 'btn-primary' : 'btn-outline-secondary'" @click="setCustomerType('walkin')">Walk-In</button>
                    <button class="btn btn-sm flex-fill fw-bold" :class="customerType === 'credit' ? 'btn-warning' : 'btn-outline-secondary'" @click="setCustomerType('credit')">Credit / Utang</button>
                </div>
                <div v-if="customerType === 'credit'" class="input-group input-group-sm">
                    <select v-model="selectedCustomerId" class="form-select">
                        <option value="" disabled>Select Customer...</option>
                        <option v-for="cust in customers" :key="cust.id" :value="cust.id">{{ cust.name }}</option>
                    </select>
                    <button class="btn btn-outline-primary" @click="showNewCustomerModal = true"><i class="fas fa-plus"></i></button>
                </div>
            </div>

            <div class="flex-fill overflow-auto p-3 bg-white custom-scrollbar">
                <div v-if="cart.length === 0" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                    <i class="fas fa-cart-arrow-down fa-3x mb-2 opacity-25"></i>
                    <span class="small fw-bold">Empty Cart</span>
                </div>
                <div v-else class="d-flex flex-column gap-2">
                    <div v-for="(item, index) in cart" :key="index" class="d-flex justify-content-between align-items-center p-2 border rounded bg-light">
                        <div class="overflow-hidden me-2" style="flex: 1;">
                            <div class="fw-bold text-truncate">{{ item.name }}</div>
                            <div class="small text-muted">₱{{ formatPrice(item.price) }}</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-light border px-2" @click="updateQty(index, -1)">-</button>
                            <span class="mx-2 fw-bold">{{ item.qty }}</span>
                            <button class="btn btn-sm btn-light border px-2" @click="updateQty(index, 1)">+</button>
                        </div>
                        <div class="text-end ms-3" style="min-width: 60px;">
                            <div class="fw-bold">₱{{ formatPrice(item.price * item.qty) }}</div>
                            <i class="fas fa-trash text-danger small cursor-pointer" @click="removeFromCart(index)"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white border-top shadow-lg z-2">
                <div v-if="taxConfig.enabled == '1'" class="mb-2">
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Subtotal</span>
                        <span>₱{{ formatPrice(subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted border-bottom pb-2">
                        <span>{{ taxLabel }}</span>
                        <span>₱{{ formatPrice(vatAmount) }}</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold fs-5 text-dark">Total</span>
                    <span class="fw-bold fs-3 text-primary">₱{{ formatPrice(grandTotal) }}</span>
                </div>

                <button class="btn btn-success w-100 py-3 fw-bold fs-5 shadow-sm text-uppercase" :disabled="cart.length === 0" @click="openPayModal">
                    Pay Now
                </button>
            </div>
        </div>
    </div>

    <div v-if="showReceiptModal" class="modal-backdrop fade show" style="z-index: 1090;"></div>
    <div v-if="showReceiptModal" class="modal fade show d-block" style="z-index: 1100;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title small fw-bold">Official Receipt</h6>
                    <button class="btn-close btn-close-white btn-sm" @click="closeReceiptModal"></button>
                </div>
                <div class="modal-body p-0" style="height: 500px; background: #fff;">
                    <iframe :src="receiptUrl" frameborder="0" width="100%" height="100%"></iframe>
                </div>
                <div class="modal-footer p-1 bg-light justify-content-between">
                    <button class="btn btn-sm btn-outline-secondary" @click="closeReceiptModal">Close</button>
                    <button class="btn btn-sm btn-primary" @click="printReceiptFrame"><i class="fas fa-print me-1"></i> Print</button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="showPayModal" class="modal-backdrop fade show" style="z-index: 1050;"></div>
    <div v-if="showPayModal" class="modal fade show d-block" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Checkout</h5>
                    <button type="button" class="btn-close btn-close-white" @click="showPayModal = false"></button>
                </div>
                <div class="modal-body p-4">
                    <h1 class="display-4 fw-bold text-center text-success mb-4">₱{{ formatPrice(grandTotal) }}</h1>
                    
                    <div class="mb-3 text-center">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" id="cash" value="cash" v-model="paymentMethod">
                            <label class="btn btn-outline-success fw-bold" for="cash">Cash</label>

                            <input type="radio" class="btn-check" id="digital" value="digital" v-model="paymentMethod">
                            <label class="btn btn-outline-primary fw-bold" for="digital">E-Wallet</label>

                            <input type="radio" class="btn-check" id="credit" value="credit" v-model="paymentMethod" :disabled="customerType !== 'credit'">
                            <label class="btn btn-outline-warning fw-bold text-dark" for="credit">Utang</label>
                        </div>
                    </div>

                    <div v-if="paymentMethod === 'cash'" class="form-group mb-4">
                        <label class="fw-bold mb-1">Amount Tendered</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text fw-bold">₱</span>
                            <input type="number" class="form-control fw-bold" v-model.number="amountTendered" id="tenderInput" @keyup.enter="processPayment">
                        </div>
                        <div class="mt-2 d-flex justify-content-between fw-bold fs-5" :class="change < 0 ? 'text-danger' : 'text-success'">
                            <span>{{ change < 0 ? 'Balance:' : 'Change:' }}</span>
                            <span>₱{{ formatPrice(Math.abs(change)) }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button class="btn btn-lg btn-success w-100 fw-bold" :disabled="!canPay" @click="processPayment">
                        COMPLETE SALE
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-md-none bg-white border-top d-flex justify-content-around py-2 shadow-lg z-3">
        <button class="btn border-0 d-flex flex-column align-items-center" :class="mobileTab === 'menu' ? 'text-primary' : 'text-muted'" @click="mobileTab = 'menu'">
            <i class="fas fa-th-large fs-5"></i><span style="font-size: 0.65rem;">MENU</span>
        </button>
        <button class="btn border-0 d-flex flex-column align-items-center position-relative" :class="mobileTab === 'cart' ? 'text-primary' : 'text-muted'" @click="mobileTab = 'cart'">
            <i class="fas fa-shopping-cart fs-5"></i>
            <span v-if="cartCount > 0" class="position-absolute top-0 start-50 badge rounded-pill bg-danger border border-white" style="font-size: 0.6rem;">{{ cartCount }}</span>
            <span style="font-size: 0.65rem;">CART</span>
        </button>
    </div>

  </div>
</template>

<script>
// Install: npm install html5-qrcode axios
import { Html5QrcodeScanner } from "html5-qrcode";
import axios from 'axios';

export default {
  props: {
    initialProducts: Array,
    initialCategories: Array,
    initialCustomers: Array,
    taxConfig: { type: Object, default: () => ({ enabled: '0', type: 'inclusive', rate: 0.12 }) },
    storeName: String,
    cashierName: String,
    csrfToken: String
  },
  data() {
    return {
      products: this.initialProducts,
      categories: this.initialCategories,
      customers: this.initialCustomers,
      cart: [],
      searchQuery: '',
      selectedCategory: '',
      
      // UI State
      mobileTab: 'menu',
      showPayModal: false,
      showReceiptModal: false,
      receiptUrl: '',
      
      // Checkout
      customerType: 'walkin',
      selectedCustomerId: '',
      paymentMethod: 'cash',
      amountTendered: '',
      
      scanner: null
    };
  },
  computed: {
    filteredProducts() {
      return this.products.filter(p => {
        const matchesSearch = p.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                              (p.barcode && p.barcode.includes(this.searchQuery));
        const matchesCat = this.selectedCategory ? p.category_id == this.selectedCategory : true;
        return matchesSearch && matchesCat;
      });
    },
    // Raw Sum of Items (Price * Qty)
    rawTotal() {
        return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    },
    // VAT LOGIC
    vatAnalysis() {
        const rate = this.taxConfig.rate || 0.12;
        const type = this.taxConfig.type;
        const base = this.rawTotal;

        let total = base;
        let vat = 0;
        let subtotal = base;

        if (this.taxConfig.enabled == '1') {
            if (type === 'inclusive') {
                // Price includes VAT. Extract it.
                vat = base - (base / (1 + rate));
                subtotal = base - vat;
                total = base;
            } else if (type === 'exclusive') {
                // Price is raw. Add VAT on top.
                vat = base * rate;
                subtotal = base;
                total = base + vat;
            } else {
                // Non-VAT
                vat = 0;
                total = base;
            }
        }
        return { total, vat, subtotal };
    },
    grandTotal() { return this.vatAnalysis.total; },
    vatAmount() { return this.vatAnalysis.vat; },
    subtotal() { return this.vatAnalysis.subtotal; },
    taxLabel() { return this.taxConfig.type === 'non_vat' ? 'Tax (Exempt)' : 'VAT (' + (this.taxConfig.rate * 100) + '%)'; },
    
    cartCount() { return this.cart.reduce((sum, item) => sum + item.qty, 0); },
    change() { return (this.amountTendered || 0) - this.grandTotal; },
    canPay() {
        if (this.paymentMethod === 'credit') return !!this.selectedCustomerId;
        return (this.amountTendered >= this.grandTotal - 0.01); // Float tolerance
    }
  },
  methods: {
    formatPrice(val) { return Number(val).toFixed(2); },
    addToCart(product) {
        if(product.stock <= 0) { alert("Out of Stock!"); return; }
        const exist = this.cart.find(i => i.id === product.id);
        if(exist) {
            if(exist.qty < product.stock) exist.qty++;
            else alert("Max stock reached");
        } else {
            this.cart.push({ ...product, qty: 1 });
        }
    },
    updateQty(index, amount) {
        const item = this.cart[index];
        const product = this.products.find(p => p.id === item.id);
        const newQty = item.qty + amount;
        if(newQty > 0 && newQty <= product.stock) item.qty = newQty;
    },
    removeFromCart(index) { this.cart.splice(index, 1); },
    setCustomerType(type) {
        this.customerType = type;
        this.paymentMethod = type === 'walkin' ? 'cash' : 'credit';
    },
    openPayModal() {
        this.amountTendered = '';
        this.showPayModal = true;
        setTimeout(() => document.getElementById('tenderInput')?.focus(), 100);
    },
    processPayment() {
        const payload = {
            cart: this.cart,
            customer_id: this.customerType === 'credit' ? this.selectedCustomerId : 'walk-in',
            payment_method: this.paymentMethod,
            total_amount: this.grandTotal, // Send Final Total
            amount_paid: this.amountTendered
        };

        axios.post('/cashier/transaction', payload)
            .then(response => {
                if(response.data.success) {
                    this.showPayModal = false;
                    this.cart = [];
                    // Show Receipt Modal
                    this.receiptUrl = `/cashier/receipt/${response.data.sale_id}`;
                    this.showReceiptModal = true;
                }
            })
            .catch(err => {
                alert("Transaction Failed: " + (err.response?.data?.message || err.message));
            });
    },
    closeReceiptModal() {
        this.showReceiptModal = false;
        this.mobileTab = 'menu'; // Reset view
    },
    printReceiptFrame() {
        const iframe = document.querySelector('iframe');
        if(iframe) iframe.contentWindow.print();
    },
    handleBarcodeEnter() {
        const match = this.products.find(p => p.barcode === this.searchQuery);
        if(match) {
            this.addToCart(match);
            this.searchQuery = '';
        }
    },
    toggleScanner() {
        alert("Camera Scanner feature requires HTTPS in production. Using Keyboard Mode.");
    }
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 4px; }
.product-card { transition: all 0.15s ease; border: 1px solid rgba(0,0,0,0.05); }
.product-card:active { transform: scale(0.96); }
.cursor-pointer { cursor: pointer; }
/* Mobile Cart Panel Fix */
@media (max-width: 767px) {
    .cart-panel { position: absolute; top: 0; left: 0; width: 100% !important; height: 100%; }
}
</style>