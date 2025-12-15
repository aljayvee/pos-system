<template>
  <div class="d-flex flex-column h-100 w-100 bg-light font-sans position-absolute top-0 start-0 overflow-hidden">
    
    <div class="px-3 py-2 text-white d-flex align-items-center justify-content-between shrink-0 shadow-sm" style="background-color: #1e1e2d; height: 60px;">
        <div class="d-flex align-items-center overflow-hidden" style="min-width: 0;">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white me-2 flex-shrink-0" style="width: 35px; height: 35px;">
                <i class="fas fa-store"></i>
            </div>
            <div class="lh-1 text-truncate">
                <div class="fw-bold text-truncate" style="font-size: 0.85rem;">{{ storeName }}</div>
                <small class="text-white-50 d-block text-truncate" style="font-size: 0.7rem;">{{ cashierName }}</small>
            </div>
        </div>

        <div class="mx-2 flex-fill" style="max-width: 400px;">
            <div class="input-group input-group-sm">
                <input v-model="searchQuery" type="text" class="form-control border-0 shadow-none rounded-start" placeholder="Search / Scan..." @keydown.enter="handleBarcodeEnter" id="barcodeInput">
                <button class="btn btn-warning text-dark fw-bold" @click="focusSearch"><i class="fas fa-barcode"></i></button>
            </div>
        </div>

        <div class="dropdown">
            <button class="btn btn-link text-white p-0 d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                <i class="fas fa-cog fa-lg me-1 text-white-50 hover-white"></i>
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold border border-secondary" style="width: 35px; height: 35px;">
                    {{ cashierName.charAt(0).toUpperCase() }}
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                <li><a class="dropdown-item py-2" href="/profile"><i class="fas fa-user-circle me-2 text-primary"></i>My Profile</a></li>
                <li><a class="dropdown-item py-2" href="/admin/settings"><i class="fas fa-sliders-h me-2 text-secondary"></i>System Settings</a></li>
                <li class="d-md-none"><hr class="dropdown-divider"></li>
                <li class="d-md-none px-3 py-1 text-muted small fw-bold">ACTIONS</li>
                <li class="d-md-none"><a class="dropdown-item py-2" href="/cashier/debtors">Pay Debt</a></li>
                <li class="d-md-none"><a class="dropdown-item py-2" href="/cashier/return/search">Return Items</a></li>
                <li class="d-md-none"><a class="dropdown-item py-2" href="/cashier/reading/z-reading">Z-Reading</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="/logout" method="POST" class="d-block w-100">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <button class="dropdown-item text-danger fw-bold py-2">Log Out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="d-flex flex-fill overflow-hidden position-relative w-100">
        
        <div class="d-flex flex-column flex-fill bg-light position-relative h-100 w-100" :class="{ 'd-none d-md-flex': mobileTab === 'cart' }">
            <div class="w-100 bg-white border-bottom py-2 px-2 flex-shrink-0 overflow-x-auto">
                <div class="d-flex gap-2">
                    <button class="btn btn-sm rounded-pill border px-3 fw-bold flex-shrink-0" :class="selectedCategory === '' ? 'btn-dark' : 'btn-light text-secondary'" @click="selectedCategory = ''">All</button>
                    <button v-for="cat in categories" :key="cat.id" class="btn btn-sm rounded-pill border px-3 fw-bold flex-shrink-0" :class="selectedCategory === cat.id ? 'btn-dark' : 'btn-light text-secondary'" @click="selectedCategory = cat.id">{{ cat.name }}</button>
                </div>
            </div>
            <div class="flex-fill overflow-auto p-2 bg-light custom-scrollbar">
                <div class="row g-2 align-content-start">
                    <div v-for="product in filteredProducts" :key="product.id" class="col-6 col-sm-4 col-lg-3 col-xl-2" @click="addToCart(product)">
                        <div class="card h-100 border-0 shadow-sm product-card cursor-pointer position-relative bg-white">
                            <span v-if="product.stock <= 5" class="position-absolute top-0 end-0 badge bg-danger m-1 rounded-pill" style="font-size: 0.6rem;">{{ product.stock }} left</span>
                            <div class="card-body p-2 text-center d-flex flex-column justify-content-between h-100">
                                <div class="mb-2 mx-auto rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold" style="width: 50px; height: 50px; font-size: 1.2rem;">{{ product.name.charAt(0) }}</div>
                                <div class="w-100">
                                    <h6 class="card-title text-dark fw-bold mb-1 text-truncate small">{{ product.name }}</h6>
                                    <div class="fw-bold text-primary small">₱{{ formatPrice(product.price) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-md-none" style="height: 70px;"></div>
            </div>
        </div>

        <div class="d-flex flex-column bg-white border-start shadow-lg h-100 cart-panel" 
             :class="{ 'd-none d-md-flex': mobileTab === 'menu', 'w-100': mobileTab === 'cart' }"
             style="width: 380px; z-index: 900;">
            
            <div class="p-3 bg-light border-bottom d-flex align-items-center shadow-sm z-1">
                <button class="btn btn-sm btn-white border d-md-none me-2 shadow-sm" @click="mobileTab = 'menu'"><i class="fas fa-arrow-left"></i></button>
                <div class="fw-bold text-dark flex-fill"><i class="fas fa-receipt me-2"></i>Order Summary</div>
                <button v-if="cart.length > 0" class="btn btn-sm btn-outline-danger border-0" @click="clearCart">Clear</button>
            </div>

            <div class="px-3 py-2 border-bottom bg-white">
                <label class="small fw-bold text-muted mb-1">Customer Type</label>
                <div class="btn-group w-100 btn-group-sm mb-2">
                    <button class="btn fw-bold" :class="customerType === 'walkin' ? 'btn-secondary' : 'btn-outline-secondary'" @click="setCustomerType('walkin')">WALK-IN</button>
                    <button class="btn fw-bold" :class="customerType === 'existing' ? 'btn-primary' : 'btn-outline-primary'" @click="setCustomerType('existing')">Existing</button>
                    <button class="btn fw-bold" :class="customerType === 'new_credit' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'" @click="setCustomerType('new_credit')">CREDIT/UTANG</button>
                </div>

                <div v-if="customerType === 'existing'" class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                    <select v-model="selectedCustomerId" class="form-select">
                        <option value="" disabled>Select Customer...</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div v-if="customerType === 'new_credit'">
                    <div v-if="!tempNewCustomer.name">
                        <button class="btn btn-sm btn-warning w-100 fw-bold border-dark" @click="showNewCustomerModal = true">
                            <i class="fas fa-plus me-1"></i> Add Customer Details
                        </button>
                    </div>
                    <div v-else class="alert alert-warning p-2 mb-0 small d-flex justify-content-between align-items-center">
                        <span><strong>{{ tempNewCustomer.name }}</strong> (New)</span>
                        <button class="btn btn-xs text-danger" @click="clearNewCustomer"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </div>

            <div class="flex-fill overflow-auto p-3 bg-white custom-scrollbar">
                <div v-if="cart.length === 0" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted opacity-50">
                    <i class="fas fa-shopping-basket fa-3x mb-2"></i><small>Cart is empty</small>
                </div>
                <div v-else class="d-flex flex-column gap-2">
                    <div v-for="(item, index) in cart" :key="index" class="d-flex align-items-center justify-content-between p-2 border rounded bg-light">
                        <div class="flex-fill overflow-hidden me-2">
                            <div class="fw-bold text-truncate small">{{ item.name }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">₱{{ formatPrice(item.price) }}</div>
                        </div>
                        <div class="d-flex align-items-center border bg-white rounded flex-shrink-0">
                            <button class="btn btn-sm px-2 py-0 text-secondary" @click="updateQty(index, -1)">-</button>
                            <span class="mx-2 fw-bold small">{{ item.qty }}</span>
                            <button class="btn btn-sm px-2 py-0 text-secondary" @click="updateQty(index, 1)">+</button>
                        </div>
                        <div class="text-end ms-2 fw-bold small" style="min-width: 50px;">₱{{ formatPrice(item.price * item.qty) }}</div>
                        <button class="btn btn-link text-danger p-0 ms-1" @click="removeFromCart(index)"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="d-md-none" style="height: 60px;"></div>
            </div>

            <div class="p-3 bg-white border-top shadow-lg z-2">
                <div v-if="taxConfig.enabled == '1'" class="mb-2 small">
                    <div class="d-flex justify-content-between text-muted"><span>Subtotal</span><span>₱{{ formatPrice(subtotal) }}</span></div>
                    <div class="d-flex justify-content-between text-muted border-bottom pb-1 mb-1"><span>VAT</span><span>₱{{ formatPrice(vatAmount) }}</span></div>
                </div>
                <button class="btn btn-success w-100 py-3 fw-bold d-flex justify-content-between align-items-center shadow-sm" :disabled="cart.length === 0" @click="openPayModal">
                    <span class="text-uppercase small">Checkout</span>
                    <span class="fs-5">₱{{ formatPrice(grandTotal) }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="d-md-none bg-white border-top d-flex justify-content-around align-items-center shadow-lg position-absolute bottom-0 w-100 z-3" style="height: 60px;">
        <button class="btn border-0 w-50 h-100 d-flex flex-column justify-content-center align-items-center" :class="mobileTab === 'menu' ? 'text-primary' : 'text-muted'" @click="mobileTab = 'menu'">
            <i class="fas fa-th-large mb-1"></i><span style="font-size: 0.65rem; font-weight: bold;">MENU</span>
        </button>
        <button class="btn border-0 w-50 h-100 d-flex flex-column justify-content-center align-items-center position-relative" :class="mobileTab === 'cart' ? 'text-primary' : 'text-muted'" @click="mobileTab = 'cart'">
            <i class="fas fa-shopping-basket mb-1"></i>
            <span v-if="cartCount > 0" class="position-absolute top-0 start-50 translate-middle-x mt-1 badge rounded-pill bg-danger border border-white" style="font-size: 0.6rem;">{{ cartCount }}</span>
            <span style="font-size: 0.65rem; font-weight: bold;">CART</span>
        </button>
    </div>

    <div v-if="showNewCustomerModal" class="modal-backdrop fade show" style="z-index: 1070;"></div>
    <div v-if="showNewCustomerModal" class="modal fade show d-block" style="z-index: 1080;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark py-2">
                    <h6 class="modal-title fw-bold">New Credit Customer</h6>
                    <button class="btn-close btn-sm" @click="showNewCustomerModal = false"></button>
                </div>
                <div class="modal-body p-3">
                    <label class="small fw-bold">Full Name</label>
                    <input type="text" class="form-control mb-2" v-model="tempNewCustomer.name">
                    <label class="small fw-bold">Address</label>
                    <input type="text" class="form-control mb-2" v-model="tempNewCustomer.address">
                    <label class="small fw-bold">Contact #</label>
                    <input type="text" class="form-control mb-3" v-model="tempNewCustomer.contact">
                    <button class="btn btn-dark w-100 btn-sm" :disabled="!tempNewCustomer.name" @click="showNewCustomerModal = false">Confirm & Close</button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="showPayModal" class="modal-backdrop fade show" style="z-index: 1050;"></div>
    <div v-if="showPayModal" class="modal fade show d-block" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title fw-bold">Checkout: {{ customerTypeLabel }}</h6>
                    <button type="button" class="btn-close btn-close-white" @click="showPayModal = false"></button>
                </div>
                <div class="modal-body p-4">
                    <h2 class="fw-bold text-success text-center mb-4">₱{{ formatPrice(grandTotal) }}</h2>

                    <label class="small fw-bold text-muted mb-2">Payment Method</label>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <button class="btn w-100 border position-relative fw-bold" 
                                :class="paymentMethod === 'cash' ? 'btn-success text-white' : 'btn-light text-muted'"
                                :disabled="customerType === 'new_credit'" 
                                @click="paymentMethod = 'cash'">
                                <i class="fas fa-money-bill-wave d-block mb-1"></i> Cash
                                <i v-if="paymentMethod === 'cash'" class="fas fa-check-circle position-absolute top-0 end-0 m-1 small"></i>
                            </button>
                        </div>
                        <div v-if="paymongoEnabled" class="col-4">
                            <button class="btn w-100 border position-relative fw-bold" 
                                :class="paymentMethod === 'digital' ? 'btn-primary text-white' : 'btn-light text-muted'"
                                :disabled="customerType === 'new_credit'"
                                @click="paymentMethod = 'digital'">
                                <i class="fas fa-qrcode d-block mb-1"></i> E-Wallet
                                <i v-if="paymentMethod === 'digital'" class="fas fa-check-circle position-absolute top-0 end-0 m-1 small"></i>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn w-100 border position-relative fw-bold" 
                                :class="paymentMethod === 'credit' ? 'btn-warning text-dark' : 'btn-light text-muted'"
                                :disabled="customerType === 'walkin'"
                                @click="paymentMethod = 'credit'">
                                <i class="fas fa-user-tag d-block mb-1"></i> Utang
                                <i v-if="paymentMethod === 'credit'" class="fas fa-check-circle position-absolute top-0 end-0 m-1 small"></i>
                            </button>
                        </div>
                    </div>

                    <div v-if="paymentMethod === 'cash'">
                        <label class="small fw-bold">Amount Tendered</label>
                        <input type="number" class="form-control fw-bold fs-5" v-model.number="amountTendered" id="tenderInput" placeholder="0.00" @keyup.enter="processPayment">
                        <div class="d-flex justify-content-between mt-2 fw-bold" :class="change < 0 ? 'text-danger' : 'text-success'">
                            <span>Change:</span><span>₱{{ formatPrice(Math.abs(change)) }}</span>
                        </div>
                    </div>

                    <div v-if="paymentMethod === 'digital'" class="text-center py-3">
                        <div v-if="!qrCodeGenerated">
                            <button class="btn btn-primary w-100" @click="generateQr"><i class="fas fa-qrcode me-2"></i>Generate GCash/Maya QR</button>
                        </div>
                        <div v-else>
                            <div class="bg-light p-3 border mb-2 d-inline-block"><i class="fas fa-qrcode fa-4x text-dark"></i></div>
                            <p class="small text-muted">Scan to Pay (Simulator)</p>
                            <button class="btn btn-sm btn-outline-secondary" @click="qrCodeGenerated = false">Cancel</button>
                        </div>
                    </div>

                    <div v-if="paymentMethod === 'credit'">
                        <div class="alert alert-warning small p-2 mb-2"><i class="fas fa-info-circle me-1"></i> Charge to: <strong>{{ creditCustomerName }}</strong></div>
                        <label class="small fw-bold">Due Date (Required)</label>
                        <input type="date" class="form-control mb-2" v-model="creditDueDate">
                        <div v-if="customerType === 'new_credit'" class="small text-muted border-top pt-2">
                            <div>Address: {{ tempNewCustomer.address || 'N/A' }}</div>
                            <div>Contact: {{ tempNewCustomer.contact || 'N/A' }}</div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer p-2 border-0 bg-light">
                    <button class="btn btn-outline-secondary px-3" @click="showPayModal = false">Back</button>
                    <button class="btn btn-success flex-fill fw-bold" :disabled="!canPay" @click="processPayment">COMPLETE TRANSACTION</button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="showReceiptModal" class="modal-backdrop fade show" style="z-index: 1090;"></div>
    <div v-if="showReceiptModal" class="modal fade show d-block" style="z-index: 1100;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title small fw-bold">Receipt</h6>
                    <button class="btn-close btn-close-white btn-sm" @click="closeReceiptModal"></button>
                </div>
                <div class="modal-body p-0" style="height: 400px; background: #fff;">
                    <iframe :src="receiptUrl" frameborder="0" width="100%" height="100%"></iframe>
                </div>
                <div class="modal-footer p-1 bg-light justify-content-between">
                    <button class="btn btn-sm btn-outline-secondary" @click="closeReceiptModal">Close</button>
                    <button class="btn btn-sm btn-primary" @click="printReceiptFrame"><i class="fas fa-print me-1"></i> Print</button>
                </div>
            </div>
        </div>
    </div>

  </div>
</template>

<script>
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
      
      // UI States
      mobileTab: 'menu',
      showPayModal: false,
      showNewCustomerModal: false,
      
      // Transaction Data
      customerType: 'walkin',
      selectedCustomerId: '',
      amountTendered: '',
      newCustomer: { name: '', phone: '' }
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
    rawTotal() { return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0); },
    vatAnalysis() {
        const rate = this.taxConfig.rate || 0.12;
        const type = this.taxConfig.type;
        const base = this.rawTotal;
        let total = base, vat = 0, subtotal = base;

        if (this.taxConfig.enabled == '1') {
            if (type === 'inclusive') {
                vat = base - (base / (1 + rate));
                subtotal = base - vat;
            } else if (type === 'exclusive') {
                vat = base * rate;
                total = base + vat;
            }
        }
        return { total, vat, subtotal };
    },
    grandTotal() { return this.vatAnalysis.total; },
    vatAmount() { return this.vatAnalysis.vat; },
    subtotal() { return this.vatAnalysis.subtotal; },
    cartCount() { return this.cart.reduce((sum, item) => sum + item.qty, 0); },
    change() { return (this.amountTendered || 0) - this.grandTotal; },
    canPay() { 
        if(this.customerType === 'credit') return !!this.selectedCustomerId;
        return (this.amountTendered >= this.grandTotal - 0.01); 
    }
  },
  methods: {
    formatPrice(val) { return Number(val).toFixed(2); },
    addToCart(product) {
        if(product.stock <= 0) { alert("Out of Stock"); return; }
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
    // 1. Updated setCustomerType to reset payment method
    setCustomerType(type) {
        this.customerType = type;
        if(type === 'walkin') {
            this.selectedCustomerId = '';
            this.paymentMethod = 'cash'; // <--- FIX: Force reset to cash
        }
    },
    // 2. Updated openPayModal to ensure valid method is selected
    openPayModal() {
        // Safety check: If we somehow have 'credit' selected but are 'walkin', fix it.
        if (this.customerType === 'walkin' && this.paymentMethod === 'credit') {
            this.paymentMethod = 'cash';
        }

        this.amountTendered = '';
        this.showPayModal = true;
        setTimeout(() => document.getElementById('tenderInput')?.focus(), 100);
    },
    processPayment() {
        // Logic to send to backend
        const payload = {
            cart: this.cart,
            customer_id: this.customerType === 'credit' ? this.selectedCustomerId : null,
            payment_method: this.paymentMethod,
            amount_tendered: this.paymentMethod === 'credit' ? 0 : this.amountTendered,
            total_amount: this.grandTotal
        };

        axios.post('/cashier/transaction', payload)
            .then(response => {
                if(response.data.success) {
                    // Show Receipt logic here (omitted for brevity, keep your existing logic)
                    alert("Payment Successful!"); 
                    this.cart = [];
                    this.showPayModal = false;
                    this.mobileTab = 'menu';
                    this.amountTendered = '';
                    this.paymentMethod = 'cash'; // Reset
                }
            })
            .catch(error => {
                alert("Transaction Failed: " + (error.response?.data?.message || "Unknown Error"));
            });
    },
    toggleScanner() { alert("Camera requires HTTPS."); },
    handleBarcodeEnter() {
        const match = this.products.find(p => p.barcode === this.searchQuery);
        if(match) { this.addToCart(match); this.searchQuery = ''; }
    },
    clearCart() { if(confirm("Clear cart?")) this.cart = []; },
    saveNewCustomer() {
        // Mock save for now
        const id = Date.now();
        this.customers.push({ id, name: this.newCustomer.name });
        this.selectedCustomerId = id;
        this.showNewCustomerModal = false;
        this.newCustomer = { name: '', phone: '' };
    }
  }
};
</script>

<style scoped>
/* Scrollbar Styling */
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 4px; }
.hide-arrow::after { display: none !important; }

/* Grid Interaction */
.product-card { transition: all 0.15s ease; border: 1px solid rgba(0,0,0,0.05); }
.product-card:active { transform: scale(0.96); }
.cursor-pointer { cursor: pointer; }
.hover-white:hover { color: #fff !important; }

/* Mobile Cart Positioning */
@media (max-width: 767px) {
    .cart-panel { position: absolute; top: 0; left: 0; width: 100% !important; height: 100%; padding-bottom: 60px; }
}
</style>