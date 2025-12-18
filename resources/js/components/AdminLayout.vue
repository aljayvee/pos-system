<template>
  <div class="d-flex vh-100 w-100 overflow-hidden bg-light font-sans">
    
    <div 
        v-if="isMobile && isOpen" 
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 fade-in"
        style="z-index: 1040;"
        @click="toggleSidebar"
    ></div>

    <aside 
      class="d-flex flex-column shadow-lg sidebar-transition"
      :class="sidebarClasses"
      style="z-index: 1050; background-color: #1e1e2d;"
    >
      <div class="d-flex align-items-center px-3 border-bottom border-secondary border-opacity-10" 
           style="height: 70px; min-height: 70px; background-color: #151521;"
           :class="(!isMobile && !isOpen) ? 'justify-content-center' : 'justify-content-between'"
      >
         <div class="d-flex align-items-center overflow-hidden" :class="{ 'w-100 justify-content-center': !isOpen && !isMobile }">
             <button v-if="isMobile" @click="toggleSidebar" class="btn btn-link text-white p-0 me-3 text-decoration-none">
                 <i class="fas fa-times fa-lg"></i>
             </button>

             <div class="d-flex align-items-center text-nowrap">
                 <div class="rounded d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px;">
                    <i class="fas fa-store fa-lg text-primary"></i>
                 </div>
                 <span class="ms-2 fw-bold fs-5 tracking-wide text-white fade-text" v-show="isOpen || isMobile">POS System</span>
             </div>
         </div>

         <button v-if="!isMobile && isOpen" @click="toggleSidebar" class="btn btn-sm btn-link text-muted p-0 text-decoration-none hover-white">
             <i class="fas fa-bars fa-lg"></i>
         </button>
      </div>

      <div class="flex-fill overflow-auto py-3 custom-scrollbar">
         
         <div class="px-3 mb-4">
             <a href="/cashier/pos" class="btn btn-primary w-100 d-flex align-items-center justify-content-center py-2 shadow-sm text-uppercase fw-bold" style="letter-spacing: 0.5px;">
                 <i class="fas fa-cash-register"></i>
                 <span class="ms-2" v-show="isOpen || isMobile">Go to Cashier</span>
             </a>
         </div>

         <ul class="nav nav-pills flex-column px-2 gap-1">
            <template v-if="userRole === 'admin'">
                
                <li class="nav-header px-3 mt-2 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">Core</li>
                <li class="nav-item">
                    <a href="/admin/dashboard" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath === '/admin/dashboard' }">
                       <div class="icon-wrapper"><i class="fas fa-tachometer-alt"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Dashboard</span>
                    </a>
                </li>

                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">Inventory</li>
                <li class="nav-item">
                    <a href="/admin/inventory" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/inventory') }">
                       <div class="icon-wrapper"><i class="fas fa-boxes"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Inventory Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/products" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/products') }">
                       <div class="icon-wrapper"><i class="fas fa-box-open"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Product Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/categories" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/categories') }">
                       <div class="icon-wrapper"><i class="fas fa-tags"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Categories</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/purchases" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/purchases') }">
                       <div class="icon-wrapper"><i class="fas fa-truck-loading"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Stock In History</span>
                    </a>
                </li>

                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">Finance</li>
                <li class="nav-item">
                    <a href="/admin/credits" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/credits') }">
                       <div class="icon-wrapper"><i class="fas fa-file-invoice-dollar"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Credits (Utang)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/customers" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/customers') }">
                       <div class="icon-wrapper"><i class="fas fa-users"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Customer Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/suppliers" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/suppliers') }">
                       <div class="icon-wrapper"><i class="fas fa-people-carry"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Supplier Management</span>
                    </a>
                </li>

                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">Analytics</li>
                <li class="nav-item">
                    <a href="/admin/reports" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/reports') }">
                       <div class="icon-wrapper"><i class="fas fa-chart-pie"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Reports</span>
                    </a>
                </li>

                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">System</li>
                <li class="nav-item">
                    <a href="/admin/transactions" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/transactions') }">
                       <div class="icon-wrapper"><i class="fas fa-history"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Transactions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/logs" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/logs') }">
                       <div class="icon-wrapper"><i class="fas fa-clipboard-list"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Audit Logs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/users" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/users') }">
                       <div class="icon-wrapper"><i class="fas fa-user-shield"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">User Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/settings" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/settings') }">
                       <div class="icon-wrapper"><i class="fas fa-cog"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">More Features</span>
                    </a>
                </li>

            </template>
         </ul>
      </div>

      <div class="p-3 border-top border-secondary border-opacity-10" style="background-color: #151521;">
          <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center overflow-hidden" v-show="isOpen || isMobile">
                  <!----><div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold me-2" style="width: 38px; height: 38px;">
                      {{ userName.charAt(0).toUpperCase() }}
                  </div>-->
                  <div class="d-flex flex-column text-nowrap">
                      <small class="fw-bold text-white lh-1">{{ userName }}</small>
                      <small class="text-muted" style="font-size: 0.7rem;">{{ userRole.toUpperCase() }}</small>
                  </div>
              </div>
              
              <form action="/logout" method="POST" :class="{ 'w-100': !isOpen && !isMobile }">
                 <input type="hidden" name="_token" :value="csrfToken">
                 <button class="btn btn-link text-danger p-0 d-flex align-items-center justify-content-center" :class="{ 'mx-auto': !isOpen && !isMobile }" style="width: 38px; height: 38px;" title="Logout">
                     <i class="fas fa-sign-out-alt fa-lg"></i>
                 </button>
              </form>
          </div>
      </div>
    </aside>

    <div class="d-flex flex-column flex-fill overflow-hidden position-relative w-100 bg-light">
      
      <header class="navbar navbar-expand bg-white border-bottom shadow-sm px-4" style="height: 70px; min-height: 70px;">
        <button v-if="!isOpen && !isMobile" @click="toggleSidebar" class="btn btn-light border me-3 text-secondary"><i class="fas fa-bars"></i></button>
        <button v-if="isMobile" @click="toggleSidebar" class="btn btn-light border me-3 text-secondary"><i class="fas fa-bars"></i></button>

        <h5 class="h9 mb-0 text-dark">POS System</h5>

        <div class="ms-auto position-relative" v-click-outside="closeNotif">
             <button @click="toggleNotif" class="btn btn-light rounded-circle position-relative p-2 text-secondary hover-bg-light" style="width: 42px; height: 42px;">
                 <i class="far fa-bell fa-lg"></i>
                 <span v-if="totalAlerts > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">{{ totalAlerts }}</span>
             </button>

             <div v-if="notifOpen" class="dropdown-menu dropdown-menu-end show shadow-lg border-0 mt-3 p-0 overflow-hidden" style="width: 320px; position: absolute; right: 0; z-index: 1060;">
                 <div class="px-3 py-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                     <span class="fw-bold">Notifications</span>
                     <span v-if="totalAlerts > 0" class="badge bg-danger rounded-pill">{{ totalAlerts }}</span>
                 </div>
                 <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                     <div v-if="totalAlerts === 0" class="p-4 text-center text-muted small">No new notifications</div>
                     <template v-else>
                         <a href="/admin/products" v-if="outOfStock > 0" class="list-group-item list-group-item-action p-3">
                             <small class="fw-bold text-danger d-block">Out of Stock</small>
                             <small class="text-muted">{{ outOfStock }} products have 0 stock.</small>
                         </a>
                         <a href="/admin/products" v-if="lowStock > 0" class="list-group-item list-group-item-action p-3">
                             <small class="fw-bold text-warning d-block">Low Stock</small>
                             <small class="text-muted">{{ lowStock }} items running low.</small>
                         </a>
                     </template>
                 </div>
             </div>
        </div>
      </header>

      <main class="flex-fill overflow-auto p-4">
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
      isMobile: window.innerWidth < 992, 
      isOpen: window.innerWidth >= 992,
      notifOpen: false,
      currentPath: window.location.pathname
    };
  },
  computed: {
    sidebarClasses() {
        return [
            this.isMobile ? 'position-fixed h-100' : 'position-relative',
            this.isOpen ? 'sidebar-open' : (this.isMobile ? 'sidebar-closed-mobile' : 'sidebar-closed-desktop')
        ];
    },
    totalAlerts() { return (this.outOfStock || 0) + (this.lowStock || 0); }
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);
  },
  unmounted() {
    window.removeEventListener('resize', this.handleResize);
  },
  methods: {
    toggleSidebar() { this.isOpen = !this.isOpen; },
    toggleNotif() { this.notifOpen = !this.notifOpen; },
    closeNotif() { this.notifOpen = false; },
    handleResize() {
      const mobile = window.innerWidth < 992;
      if (this.isMobile !== mobile) {
        this.isMobile = mobile;
        this.isOpen = !this.isMobile; 
      }
    }
  }
};
</script>

<style scoped>
/* FORCE TEXT COLORS */
.text-white { color: #ffffff !important; }
.text-muted { color: #6c757d !important; }

/* TRANSITIONS */
.sidebar-transition { transition: all 0.3s ease; white-space: nowrap; }
.sidebar-open { width: 260px; transform: translateX(0); }
.sidebar-closed-desktop { width: 80px; }
.sidebar-closed-mobile { width: 260px; transform: translateX(-100%); }

/* LINKS */
.nav-link { color: #a2a3b7; padding: 0.8rem 1rem; border-radius: 0.4rem; transition: all 0.2s ease; margin-bottom: 2px; }
.nav-link:hover { color: #ffffff; background-color: rgba(255, 255, 255, 0.05); }
.nav-link.active { background-color: #1b1b28 !important; color: #3699ff !important; position: relative; }
.nav-link.active::before { content: ''; position: absolute; left: 0; top: 10%; height: 80%; width: 4px; background-color: #3699ff; border-radius: 0 4px 4px 0; }

/* UTILS */
.hover-white:hover { color: #ffffff !important; }
.icon-wrapper { width: 35px; display: flex; justify-content: center; align-items: center; font-size: 1.15rem; flex-shrink: 0; }
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #3f4254; border-radius: 10px; }
.fade-in { animation: fadeIn 0.2s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>