<template>
  <div class="d-flex flex-column h-100 w-100 bg-light font-sans position-absolute top-0 start-0 overflow-hidden">
    
    <div class="px-3 py-2 text-white d-flex align-items-center justify-content-between shrink-0 shadow-sm" style="background-color: #1e1e2d; height: 64px;">
        
        <div class="d-flex align-items-center me-3">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white me-2 fw-bold" style="width: 40px; height: 40px;">
                <i class="fas fa-store"></i>
            </div>
            <div class="d-none d-sm-block lh-1">
                <div class="fw-bold fs-6">{{ storeName }}</div>
                <small class="text-white-50" style="font-size: 0.75rem;">Cashier: {{ cashierName }}</small>
            </div>
        </div>

        <div class="flex-fill mx-3 mw-100 position-relative" style="max-width: 500px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-0 text-muted ps-3"><i class="fas fa-search"></i></span>
                <input 
                    v-model="searchQuery" 
                    type="text" 
                    class="form-control border-0 shadow-none" 
                    placeholder="Search Item or Scan Barcode..." 
                    id="barcodeInput"
                    autofocus
                    @keydown.enter="handleBarcodeEnter"
                >
                <button class="btn btn-warning px-3 fw-bold" type="button" @click="toggleScanner" title="Open Camera Scanner">
                    <i class="fas fa-camera me-1"></i> Scan
                </button>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="d-none d-md-flex gap-2">
                <a href="/cashier/debtors" class="btn btn-outline-light btn-sm d-flex align-items-center" title="Pay Debt / Utang">
                    <i class="fas fa-book me-1"></i> Pay Debt
                </a>
                <a href="/cashier/return/search" class="btn btn-outline-light btn-sm d-flex align-items-center" title="Process Return">
                    <i class="fas fa-undo me-1"></i> Return
                </a>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-line me-1"></i> Reports
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="/cashier/reading/x-reading"><i class="fas fa-file-invoice me-2"></i>X-Reading (Shift)</a></li>
                        <li><a class="dropdown-item" href="/cashier/reading/z-reading"><i class="fas fa-file-invoice-dollar me-2"></i>Z-Reading (End Day)</a></li>
                    </ul>
                </div>
            </div>

            <form action="/logout" method="POST" class="ms-2">
                <input type="hidden" name="_token" :value="csrfToken">
                <button class="btn btn-danger btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="Logout">
                    <i class="fas fa-power-off"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="d-flex flex-fill overflow-hidden position-relative">
        
        <div class="d-flex flex-column flex-fill bg-light position-relative" :class="{ 'd-none d-lg-flex': mobileTab === 'cart' }">
            <div class="d-flex gap-2 p-2 overflow-auto bg-white border-bottom custom-scrollbar shrink-0">
                <button class="btn btn-sm rounded-pill border px-3 fw-bold" :class="selectedCategory === '' ? 'btn-dark' : 'btn-light text-muted'" @click="selectedCategory = ''">All</button>
                <button v-for="cat in categories" :key="cat.id" class="btn btn-sm rounded-pill border px-3 fw-bold" :class="selectedCategory === cat.id ? 'btn-dark' : 'btn-light text-muted'" @click="selectedCategory = cat.id">{{ cat.name }}</button>
            </div>

            <div class="flex-fill overflow-auto p-3 custom-scrollbar">
                <div class="row g-2">
                    <div v-for="product in filteredProducts" :key="product.id" class="col-6 col-sm-4 col-md-3 col-xl-2" @click="addToCart(product)">
                        <div class="card h-100 border-0 shadow-sm product-card cursor-pointer">
                            <div class="card-body p-2 text-center d-flex flex-column">
                                <div class="mb-2 mx-auto rounded d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold fs-3" style="width: 60px; height: 60px;">
                                    {{ product.name.charAt(0) }}
                                </div>
                                <h6 class="card-title text-dark fw-bold mb-1 text-truncate w-100 small">{{ product.name }}</h6>
                                <div class="mt-auto">
                                    <span class="badge bg-secondary mb-1" v-if="product.stock <= 5">Low: {{ product.stock }}</span>
                                    <div class="fw-bold text-primary">₱{{ formatPrice(product.price) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column bg-white border-start shadow-lg h-100 cart-panel" 
             :class="{ 'd-none d-lg-flex': mobileTab === 'menu', 'w-100': mobileTab === 'cart' }"
             style="width: 420px; z-index: 100;">
            
            <div class="p-3 bg-light border-bottom">
                <div class="d-flex gap-2 mb-2">
                    <button class="btn btn-sm flex-fill fw-bold" :class="customerType === 'walkin' ? 'btn-primary' : 'btn-outline-secondary'" @click="setCustomerType('walkin')">
                        <i class="fas fa-walking me-1"></i> Walk-In
                    </button>
                    <button class="btn btn-sm flex-fill fw-bold" :class="customerType === 'credit' ? 'btn-warning' : 'btn-outline-secondary'" @click="setCustomerType('credit')">
                        <i class="fas fa-user-tag me-1"></i> Credit / Utang
                    </button>
                </div>

                <div v-if="customerType === 'credit'" class="input-group input-group-sm">
                    <select v-model="selectedCustomerId" class="form-select">
                        <option value="" disabled>Select Customer...</option>
                        <option v-for="cust in customers" :key="cust.id" :value="cust.id">{{ cust.name }}</option>
                    </select>
                    <button class="btn btn-outline-primary" @click="showNewCustomerModal = true">
                        <i class="fas fa-plus"></i> New
                    </button>
                </div>
            </div>

            <div class="flex-fill overflow-auto p-3 bg-white custom-scrollbar">
                <div v-if="cart.length === 0" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                    <i class="fas fa-shopping-basket fa-3x mb-2 opacity-25"></i>
                    <span class="small fw-bold">No Items</span>
                </div>
                <div v-else class="d-flex flex-column gap-2">
                    <div v-for="(item, index) in cart" :key="index" class="d-flex justify-content-between align-items-center p-2 border rounded bg-light">
                        <div class="overflow-hidden me-2" style="flex: 1;">
                            <div class="fw-bold text-truncate">{{ item.name }}</div>
                            <div class="small text-muted">₱{{ formatPrice(item.price) }}</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-light border px-2 py-0" @click="updateQty(index, -1)">-</button>
                            <span class="mx-2 fw-bold" style="min-width: 20px; text-align: center;">{{ item.qty }}</span>
                            <button class="btn btn-sm btn-light border px-2 py-0" @click="updateQty(index, 1)">+</button>
                        </div>
                        <div class="text-end ms-3" style="width: 70px;">
                            <div class="fw-bold">₱{{ formatPrice(item.price * item.qty) }}</div>
                            <i class="fas fa-times text-danger small cursor-pointer" @click="removeFromCart(index)"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white border-top shadow-lg z-2">
                <div class="d-flex justify-content-between mb-1 small text-muted">
                    <span>VATable Sales</span>
                    <span>₱{{ formatPrice(vatableSales) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted border-bottom pb-2">
                    <span>VAT (12%)</span>
                    <span>₱{{ formatPrice(vatAmount) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold fs-5 text-dark">Total Due</span>
                    <span class="fw-bold fs-4 text-primary">₱{{ formatPrice(cartTotal) }}</span>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="printReceipt" v-model="printReceipt">
                    <label class="form-check-label small fw-bold text-muted" for="printReceipt">Print Receipt (Thermal)</label>
                </div>

                <button class="btn btn-success w-100 py-3 fw-bold fs-5 shadow-sm text-uppercase" :disabled="cart.length === 0" @click="openPayModal">
                    <i class="fas fa-money-bill-wave me-2"></i> Pay Now
                </button>
            </div>
        </div>
    </div>

    <div class="d-lg-none bg-white border-top d-flex justify-content-around py-2 shadow-lg z-3">
        <button class="btn border-0 d-flex flex-column align-items-center" :class="mobileTab === 'menu' ? 'text-primary' : 'text-muted'" @click="mobileTab = 'menu'">
            <i class="fas fa-th-large fs-5"></i>
            <span style="font-size: 0.65rem;" class="fw-bold mt-1">MENU</span>
        </button>
        <button class="btn border-0 d-flex flex-column align-items-center position-relative" :class="mobileTab === 'cart' ? 'text-primary' : 'text-muted'" @click="mobileTab = 'cart'">
            <i class="fas fa-shopping-cart fs-5"></i>
            <span v-if="cartCount > 0" class="position-absolute top-0 start-50 badge rounded-pill bg-danger border border-white" style="font-size: 0.6rem;">{{ cartCount }}</span>
            <span style="font-size: 0.65rem;" class="fw-bold mt-1">CART</span>
        </button>
    </div>

    <div v-if="showPayModal" class="modal-backdrop fade show"></div>
    <div v-if="showPayModal" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-cash-register me-2"></i>Checkout</h5>
                    <button type="button" class="btn-close btn-close-white" @click="showPayModal = false"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <small class="text-uppercase text-muted fw-bold">Total Amount</small>
                        <h1 class="display-4 fw-bold text-success mb-0">₱{{ formatPrice(cartTotal) }}</h1>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small text-muted mb-2">Payment Method</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <button class="btn w-100 fw-bold border" :class="paymentMethod === 'cash' ? 'btn-success text-white' : 'btn-light'" @click="paymentMethod = 'cash'">
                                    <i class="fas fa-money-bill-wave d-block mb-1"></i> Cash
                                </button>
                            </div>
                            <div class="col-4">
                                <button class="btn w-100 fw-bold border" :class="paymentMethod === 'digital' ? 'btn-primary text-white' : 'btn-light'" @click="paymentMethod = 'digital'">
                                    <i class="fas fa-qrcode d-block mb-1"></i> E-Wallet
                                </button>
                            </div>
                            <div class="col-4">
                                <button class="btn w-100 fw-bold border" :class="paymentMethod === 'credit' ? 'btn-warning text-dark' : 'btn-light'" @click="paymentMethod = 'credit'" :disabled="customerType !== 'credit'">
                                    <i class="fas fa-user-tag d-block mb-1"></i> Utang
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="paymentMethod !== 'credit'" class="form-group mb-4">
                        <label class="fw-bold mb-1">Amount Tendered</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light fw-bold">₱</span>
                            <input type="number" class="form-control fw-bold" v-model.number="amountTendered" id="tenderInput" placeholder="0.00" @keyup.enter="processPayment">
                        </div>
                        <div class="mt-2 d-flex justify-content-between fw-bold" :class="change < 0 ? 'text-danger' : 'text-success'">
                            <span>{{ change < 0 ? 'Balance:' : 'Change:' }}</span>
                            <span>₱{{ formatPrice(Math.abs(change)) }}</span>
                        </div>
                    </div>

                    <div v-else class="alert alert-warning small">
                        <i class="fas fa-info-circle me-1"></i> This amount will be added to <strong>{{ getCustomerName(selectedCustomerId) }}</strong>'s credit balance.
                    </div>

                </div>
                <div class="modal-footer border-0 bg-light">
                    <button class="btn btn-lg btn-success w-100 fw-bold" :disabled="!canPay" @click="processPayment">
                        COMPLETE TRANSACTION <i class="fas fa-chevron-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="showNewCustomerModal" class="modal-backdrop fade show" style="z-index: 1055;"></div>
    <div v-if="showNewCustomerModal" class="modal fade show d-block" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold">Add New Credit Customer</h6>
                    <button class="btn-close btn-close-white" @click="showNewCustomerModal = false"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small fw-bold">Full Name</label>
                        <input type="text" class="form-control" v-model="newCustomer.name">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Contact Number</label>
                        <input type="text" class="form-control" v-model="newCustomer.phone">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Credit Limit (Optional)</label>
                        <input type="number" class="form-control" v-model="newCustomer.limit" placeholder="5000">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary w-100" @click="saveNewCustomer">Save Customer</button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="showScanner" class="modal-backdrop fade show" style="z-index: 1070;"></div>
    <div v-if="showScanner" class="modal fade show d-block" style="z-index: 1080;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-body p-0 position-relative">
                    <button class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" @click="toggleScanner"></button>
                    <div id="reader" style="width: 100%; height: 300px; background: black;"></div>
                    <div class="text-center p-3">
                        <small>Point camera at barcode</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

  </div>
</template>

<script>
// NOTE: Install html5-qrcode via npm: npm install html5-qrcode
import { Html5QrcodeScanner } from "html5-qrcode";

export default {
  props: {
    initialProducts: Array,
    initialCategories: Array,
    initialCustomers: Array,
    storeName: String,
    cashierName: String,
    csrfToken: String
  },
  data() {
    return {
      products: this.initialProducts,
      categories: this.initialCategories,
      customers: this.initialCustomers,
      
      // Cart & Search
      cart: [],
      searchQuery: '',
      selectedCategory: '',
      
      // UI States
      mobileTab: 'menu',
      showPayModal: false,
      showNewCustomerModal: false,
      showScanner: false,
      
      // Checkout Logic
      customerType: 'walkin', // 'walkin' or 'credit'
      selectedCustomerId: '',
      paymentMethod: 'cash', // 'cash', 'digital', 'credit'
      amountTendered: '',
      printReceipt: true, // Default to print
      
      // New Customer Form
      newCustomer: { name: '', phone: '', limit: '' },
      
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
    cartTotal() {
      return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    },
    cartCount() {
        return this.cart.reduce((sum, item) => sum + item.qty, 0);
    },
    vatableSales() {
        return this.cartTotal / 1.12;
    },
    vatAmount() {
        return this.cartTotal - this.vatableSales;
    },
    change() {
        return (this.amountTendered || 0) - this.cartTotal;
    },
    canPay() {
        if (this.paymentMethod === 'credit') return !!this.selectedCustomerId;
        return (this.amountTendered >= this.cartTotal);
    }
  },
  methods: {
    formatPrice(val) { return Number(val).toFixed(2); },
    
    addToCart(product) {
        if(product.stock <= 0) { alert("Out of stock!"); return; }
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
        if(type === 'walkin') {
            this.selectedCustomerId = '';
            this.paymentMethod = 'cash';
        } else {
            this.paymentMethod = 'credit';
        }
    },

    openPayModal() {
        this.amountTendered = '';
        this.showPayModal = true;
        // Auto-focus input
        setTimeout(() => document.getElementById('tenderInput')?.focus(), 100);
    },

    processPayment() {
        const payload = {
            cart: this.cart,
            customer_id: this.customerType === 'credit' ? this.selectedCustomerId : null,
            payment_method: this.paymentMethod,
            amount_tendered: this.amountTendered,
            print_receipt: this.printReceipt,
            vat_amount: this.vatAmount,
            total: this.cartTotal
        };

        console.log("Processing Payload:", payload);
        // TODO: Replace with axios.post('/cashier/transaction', payload)
        
        alert("Transaction Complete!");
        this.cart = [];
        this.showPayModal = false;
        this.mobileTab = 'menu';
    },

    // Camera Scanner Logic
    toggleScanner() {
        this.showScanner = !this.showScanner;
        if(this.showScanner) {
            this.$nextTick(() => {
                this.scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
                this.scanner.render(this.onScanSuccess);
            });
        } else {
            if(this.scanner) this.scanner.clear();
        }
    },
    onScanSuccess(decodedText) {
        this.searchQuery = decodedText;
        this.toggleScanner(); // Close scanner
        // Auto-add first match
        const match = this.products.find(p => p.barcode === decodedText);
        if(match) this.addToCart(match);
    },

    saveNewCustomer() {
        // Mock Save
        const newId = Date.now();
        this.customers.push({ id: newId, name: this.newCustomer.name });
        this.selectedCustomerId = newId;
        this.showNewCustomerModal = false;
        alert("Customer Added!");
    },
    
    getCustomerName(id) {
        const c = this.customers.find(x => x.id == id);
        return c ? c.name : 'Unknown';
    }
  }
};
</script>

<style scoped>
/* Scoped Styles for Scrollbar & Layout */
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 4px; }
.product-card { transition: all 0.1s; border: 1px solid rgba(0,0,0,0.05); }
.product-card:active { transform: scale(0.96); }
.cursor-pointer { cursor: pointer; }

/* Mobile Optimizations */
@media (max-width: 991px) {
    .cart-panel {
        position: absolute; top: 0; left: 0; width: 100% !important; height: 100%;
    }
}
</style>