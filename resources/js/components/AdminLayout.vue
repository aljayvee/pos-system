<template>
  <div class="d-flex vh-100 w-100 overflow-hidden bg-light">
    
    <div 
        v-if="isMobile && isOpen" 
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50"
        style="z-index: 1040;"
        @click="toggleSidebar"
    ></div>

    <aside 
      class="bg-dark text-secondary d-flex flex-column shadow-lg"
      :class="[
        'sidebar-panel',
        isMobile ? 'position-fixed h-100 z-3' : 'position-relative',
        isOpen ? 'sidebar-open' : (isMobile ? 'sidebar-closed-mobile' : 'sidebar-closed-desktop')
      ]"
    >
      <div class="sidebar-header d-flex align-items-center px-3 border-bottom border-secondary border-opacity-25"
           :class="(isOpen && !isMobile) ? 'justify-content-between' : 'justify-content-center'"
           style="height: 64px; min-height: 64px;"
      >
         <div class="d-flex align-items-center">
             <button v-if="isMobile" @click="toggleSidebar" class="btn btn-link text-light p-0 me-3 text-decoration-none">
                 <i class="fas fa-bars fa-lg"></i>
             </button>

             <div class="d-flex align-items-center fw-bold fs-5 text-white text-nowrap overflow-hidden">
                 <i class="fas fa-store text-primary fa-lg me-2" :class="{ 'me-0': !isOpen && !isMobile }"></i>
                 <span v-show="isOpen || isMobile">SariPOS</span>
             </div>
         </div>

         <button v-if="!isMobile && isOpen" @click="toggleSidebar" class="btn btn-link text-secondary p-0 text-decoration-none hover-white">
             <i class="fas fa-bars fa-lg"></i>
         </button>
      </div>

      <nav class="flex-fill overflow-auto py-3 custom-scrollbar">
         <ul class="nav flex-column px-2">
            
            <li class="nav-item mb-1">
                <a href="/cashier" class="nav-link d-flex align-items-center rounded" 
                   :class="currentPath.includes('/cashier') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                   <i class="fas fa-cash-register fa-lg text-center" style="width: 30px;"></i>
                   <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Cashier POS</span>
                </a>
            </li>

            <template v-if="userRole === 'admin'">
                
                <div class="small fw-bold text-uppercase text-muted mt-3 mb-2 px-3" v-show="isOpen || isMobile">Overview</div>
                <li class="nav-item mb-1">
                    <a href="/admin/dashboard" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath === '/admin/dashboard' ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-tachometer-alt fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Dashboard</span>
                    </a>
                </li>

                <div class="small fw-bold text-uppercase text-muted mt-3 mb-2 px-3" v-show="isOpen || isMobile">Inventory</div>
                <li class="nav-item mb-1">
                    <a href="/admin/inventory" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/inventory') && !currentPath.includes('/history') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-boxes fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Inventory Overview</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/products" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/products') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-box-open fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Product</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/categories" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/categories') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-tags fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Category</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/inventory/history" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/inventory/history') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-layer-group fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Stock History</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/purchases" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/purchases') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-truck-loading fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Restocking</span>
                    </a>
                </li>

                <div class="small fw-bold text-uppercase text-muted mt-3 mb-2 px-3" v-show="isOpen || isMobile">People</div>
                <li class="nav-item mb-1">
                    <a href="/admin/customers" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/customers') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-users fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Customers</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/credits" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/credits') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-file-invoice-dollar fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Credits</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/suppliers" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/suppliers') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-truck fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Suppliers</span>
                    </a>
                </li>

                <div class="small fw-bold text-uppercase text-muted mt-3 mb-2 px-3" v-show="isOpen || isMobile">System</div>
                <li class="nav-item mb-1">
                    <a href="/admin/transactions" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/transactions') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-history fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Transaction History</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/logs" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/logs') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-clipboard-list fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Audit Logs</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/users" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/users') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-user-shield fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">User Management</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="/admin/settings" class="nav-link d-flex align-items-center rounded"
                       :class="currentPath.includes('/settings') ? 'active bg-primary text-white' : 'text-secondary hover-bg'">
                       <i class="fas fa-cog fa-lg text-center" style="width: 30px;"></i>
                       <span class="ms-2 text-nowrap" v-show="isOpen || isMobile">Settings</span>
                    </a>
                </li>

            </template>
         </ul>
      </nav>

      <div class="p-3 border-top border-secondary border-opacity-25 bg-dark">
          <form action="/logout" method="POST" class="d-grid">
             <input type="hidden" name="_token" :value="csrfToken">
             <button class="btn btn-outline-danger d-flex align-items-center justify-content-center">
                 <i class="fas fa-sign-out-alt"></i>
                 <span class="ms-2 fw-semibold" v-show="isOpen || isMobile">LOGOUT</span>
             </button>
          </form>
      </div>
    </aside>

    <div class="d-flex flex-column flex-fill overflow-hidden position-relative w-100">
      
      <header class="navbar navbar-light bg-white border-bottom shadow-sm px-3 px-lg-4" style="height: 64px; min-height: 64px;">
        <div class="d-flex justify-content-between align-items-center w-100">
            
            <button @click="toggleSidebar" class="btn btn-light border shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="fas fa-bars text-secondary"></i>
            </button>

            <h1 class="h5 m-0 fw-bold text-dark text-truncate mx-3 flex-fill text-center text-lg-start">
                {{ pageTitle }}
            </h1>

            <div class="position-relative" v-click-outside="closeNotif">
                 <button @click="toggleNotif" class="btn btn-light position-relative border-0 p-2">
                     <i class="fas fa-bell fa-lg text-secondary"></i>
                     <span v-if="totalAlerts > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                        {{ totalAlerts }}
                     </span>
                 </button>

                 <div v-if="notifOpen" class="dropdown-menu dropdown-menu-end show shadow-lg border-0 mt-2 p-0" style="width: 320px; position: absolute; right: 0;">
                     <div class="px-3 py-2 bg-light border-bottom fw-bold text-dark d-flex justify-content-between align-items-center">
                         <span>Notifications</span>
                         <span class="badge bg-primary rounded-pill">{{ totalAlerts }}</span>
                     </div>
                     <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                         <div v-if="totalAlerts === 0" class="p-4 text-center text-muted small">No new notifications</div>
                         <template v-else>
                             <a href="/admin/products" v-if="outOfStock > 0" class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3">
                                 <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                     <i class="fas fa-exclamation-circle"></i>
                                 </div>
                                 <div class="flex-fill">
                                     <h6 class="mb-0 small fw-bold text-dark">Out of Stock</h6>
                                     <small class="text-muted d-block">{{ outOfStock }} products need restocking.</small>
                                 </div>
                             </a>
                             <a href="/admin/products" v-if="lowStock > 0" class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3">
                                 <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                     <i class="fas fa-box-open"></i>
                                 </div>
                                 <div class="flex-fill">
                                     <h6 class="mb-0 small fw-bold text-dark">Low Stock</h6>
                                     <small class="text-muted d-block">{{ lowStock }} items running low.</small>
                                 </div>
                             </a>
                         </template>
                     </div>
                 </div>
            </div>
        </div>
      </header>

      <main class="flex-fill overflow-auto p-3 p-lg-4 bg-light">
          <div class="container-fluid p-0" style="max-width: 1600px;">
              <slot></slot>
          </div>
      </main>

    </div>
  </div>
</template>

<script>
export default {
  props: ['userName', 'userRole', 'pageTitle', 'csrfToken', 'outOfStock', 'lowStock'],
  data() {
    return {
      isMobile: window.innerWidth < 992, // Bootstrap "lg" breakpoint
      isOpen: window.innerWidth >= 992,  // Default open on desktop
      notifOpen: false,
      currentPath: window.location.pathname
    };
  },
  computed: {
    totalAlerts() { return (this.outOfStock || 0) + (this.lowStock || 0); }
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);
  },
  unmounted() {
    window.removeEventListener('resize', this.handleResize);
  },
  methods: {
    toggleSidebar() {
      this.isOpen = !this.isOpen;
    },
    toggleNotif() {
      this.notifOpen = !this.notifOpen;
    },
    closeNotif() {
      this.notifOpen = false;
    },
    handleResize() {
      // Use Bootstrap breakpoint 992px (lg)
      const mobile = window.innerWidth < 992;
      if (this.isMobile !== mobile) {
        this.isMobile = mobile;
        // Auto-reset state: Desktop = Open, Mobile = Closed
        this.isOpen = !this.isMobile; 
      }
    }
  }
};
</script>

<style scoped>
/* CUSTOM SIDEBAR CSS 
   Bootstrap doesn't support width transitions natively, so we add them here.
*/
.sidebar-panel {
    transition: width 0.3s ease, transform 0.3s ease;
    white-space: nowrap; /* Prevents text wrapping during transition */
    border-right: 1px solid rgba(255,255,255,0.1);
}

/* State: Open (Desktop & Mobile) */
.sidebar-open {
    width: 260px;
    transform: translateX(0);
}

/* State: Closed (Desktop - Mini Mode) */
.sidebar-closed-desktop {
    width: 70px;
}

/* State: Closed (Mobile - Off Screen) */
.sidebar-closed-mobile {
    width: 260px;
    transform: translateX(-100%);
}

/* Sidebar Link Styling */
.hover-bg:hover {
    background-color: rgba(255,255,255,0.1);
    color: #fff !important;
}

.hover-white:hover {
    color: #fff !important;
}

/* Custom Scrollbar for Sidebar Navigation */
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #1e1e2d; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #495057; 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #6c757d; 
}
</style>