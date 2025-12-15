<template>
  <div class="d-flex vh-100 w-100 overflow-hidden bg-light font-sans">
    
    <div 
        v-if="isMobile && isOpen" 
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 fade-in"
        style="z-index: 1040;"
        @click="toggleSidebar"
    ></div>

    <aside 
      class="text-white d-flex flex-column shadow-lg sidebar-transition"
      :class="sidebarClasses"
      style="z-index: 1050; background-color: #1e1e2d;"
    >
      <div class="d-flex align-items-center px-3 border-bottom border-secondary border-opacity-10" 
           style="height: 70px; min-height: 70px; background-color: #151521;"
           :class="(!isMobile && !isOpen) ? 'justify-content-center' : 'justify-content-between'"
      >
         <div class="d-flex align-items-center overflow-hidden" :class="{ 'w-100 justify-content-center': !isOpen && !isMobile }">
             <button v-if="isMobile" @click="toggleSidebar" class="btn btn-link text-white p-0 me-3 text-decoration-none">
                 <i class="fas fa-arrow-left fa-lg"></i>
             </button>

             <div class="d-flex align-items-center text-nowrap">
                 <div class="rounded d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px;">
                    <i class="fas fa-store fa-lg text-primary"></i>
                 </div>
                 <span class="ms-2 fw-bold fs-5 tracking-wide fade-text" v-show="isOpen || isMobile">SariPOS</span>
             </div>
         </div>

         <button 
            v-if="!isMobile && isOpen" 
            @click="toggleSidebar" 
            class="btn btn-sm btn-link text-muted p-0 text-decoration-none hover-white"
         >
             <i class="fas fa-bars fa-lg"></i>
         </button>
      </div>

      <div class="flex-fill overflow-auto py-3 custom-scrollbar">
         
         <div class="px-3 mb-4">
             <a href="/cashier" class="btn btn-primary w-100 d-flex align-items-center justify-content-center py-2 shadow-sm text-uppercase fw-bold" style="letter-spacing: 0.5px;">
                 <i class="fas fa-cash-register"></i>
                 <span class="ms-2" v-show="isOpen || isMobile">Cashier POS</span>
             </a>
         </div>

         <ul class="nav nav-pills flex-column px-2 gap-1">
            <template v-if="userRole === 'admin'">
                
                <li class="nav-header px-3 mt-2 mb-1 text-muted small fw-bold text-uppercase text-nowrap overflow-hidden" style="font-size: 0.75rem; letter-spacing: 1px;" v-show="isOpen || isMobile">
                    Overview
                </li>
                <li class="nav-item">
                    <a href="/admin/dashboard" class="nav-link d-flex align-items-center" 
                       :class="{ 'active': currentPath === '/admin/dashboard' }">
                       <div class="icon-wrapper"><i class="fas fa-tachometer-alt"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Dashboard</span>
                    </a>
                </li>

                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase text-nowrap overflow-hidden" style="font-size: 0.75rem; letter-spacing: 1px;" v-show="isOpen || isMobile">
                    Inventory
                </li>
                <li class="nav-item">
                    <a href="/admin/products" class="nav-link d-flex align-items-center"
                       :class="{ 'active': currentPath.includes('/products') }">
                       <div class="icon-wrapper"><i class="fas fa-box-open"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/inventory" class="nav-link d-flex align-items-center"
                       :class="{ 'active': currentPath.includes('/inventory') && !currentPath.includes('history') }">
                       <div class="icon-wrapper"><i class="fas fa-boxes"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Stock Level</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/purchases" class="nav-link d-flex align-items-center"
                       :class="{ 'active': currentPath.includes('/purchases') }">
                       <div class="icon-wrapper"><i class="fas fa-truck-loading"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Restocking</span>
                    </a>
                </li>

                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase text-nowrap overflow-hidden" style="font-size: 0.75rem; letter-spacing: 1px;" v-show="isOpen || isMobile">
                    People
                </li>
                <li class="nav-item">
                    <a href="/admin/customers" class="nav-link d-flex align-items-center"
                       :class="{ 'active': currentPath.includes('/customers') }">
                       <div class="icon-wrapper"><i class="fas fa-users"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Customers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/suppliers" class="nav-link d-flex align-items-center"
                       :class="{ 'active': currentPath.includes('/suppliers') }">
                       <div class="icon-wrapper"><i class="fas fa-truck"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Suppliers</span>
                    </a>
                </li>

                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase text-nowrap overflow-hidden" style="font-size: 0.75rem; letter-spacing: 1px;" v-show="isOpen || isMobile">
                    System
                </li>
                <li class="nav-item">
                    <a href="/admin/transactions" class="nav-link d-flex align-items-center"
                       :class="{ 'active': currentPath.includes('/transactions') }">
                       <div class="icon-wrapper"><i class="fas fa-history"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Transactions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/reports" class="nav-link d-flex align-items-center"
                       :class="{ 'active': currentPath.includes('/reports') }">
                       <div class="icon-wrapper"><i class="fas fa-chart-pie"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/users" class="nav-link d-flex align-items-center"
                       :class="{ 'active': currentPath.includes('/users') }">
                       <div class="icon-wrapper"><i class="fas fa-user-shield"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/settings" class="nav-link d-flex align-items-center"
                       :class="{ 'active': currentPath.includes('/settings') }">
                       <div class="icon-wrapper"><i class="fas fa-cog"></i></div>
                       <span class="text-nowrap fade-text" v-show="isOpen || isMobile">Settings</span>
                    </a>
                </li>

            </template>
         </ul>
      </div>

      <div class="p-3 border-top border-secondary border-opacity-10" style="background-color: #151521;">
          <form action="/logout" method="POST">
             <input type="hidden" name="_token" :value="csrfToken">
             <button class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center border-0 hover-bg-danger" style="background-color: rgba(246, 78, 96, 0.1); color: #F64E60;">
                 <div class="icon-wrapper"><i class="fas fa-sign-out-alt"></i></div>
                 <span class="fw-semibold ms-1 text-nowrap fade-text" v-show="isOpen || isMobile">Log Out</span>
             </button>
          </form>
      </div>
    </aside>

    <div class="d-flex flex-column flex-fill overflow-hidden position-relative w-100 bg-light">
      
      <header class="navbar navbar-expand bg-white border-bottom shadow-sm px-4" style="height: 70px; min-height: 70px;">
        
        <button 
            v-if="!isOpen && !isMobile" 
            @click="toggleSidebar" 
            class="btn btn-light border me-3 text-secondary"
        >
            <i class="fas fa-bars"></i>
        </button>

        <button 
            v-if="isMobile" 
            @click="toggleSidebar" 
            class="btn btn-light border me-3 text-secondary"
        >
            <i class="fas fa-bars"></i>
        </button>

        <h1 class="h5 mb-0 fw-bold text-dark">{{ pageTitle }}</h1>

        <div class="ms-auto position-relative" v-click-outside="closeNotif">
             <button @click="toggleNotif" class="btn btn-light rounded-circle position-relative p-2 text-secondary hover-bg-light" style="width: 42px; height: 42px;">
                 <i class="far fa-bell fa-lg"></i>
                 <span v-if="totalAlerts > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                    {{ totalAlerts }}
                 </span>
             </button>

             <div v-if="notifOpen" class="dropdown-menu dropdown-menu-end show shadow-lg border-0 mt-3 p-0 overflow-hidden" style="width: 320px; position: absolute; right: 0; z-index: 1060;">
                 <div class="px-3 py-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                     <span class="fw-bold">Notifications</span>
                     <span v-if="totalAlerts > 0" class="badge bg-danger rounded-pill">{{ totalAlerts }}</span>
                 </div>
                 <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                     <div v-if="totalAlerts === 0" class="p-4 text-center text-muted small">
                         <i class="fas fa-check-circle fa-2x mb-2 d-block text-success opacity-50"></i>
                         All caught up!
                     </div>
                     <template v-else>
                         <a href="/admin/products" v-if="outOfStock > 0" class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3 border-0 border-bottom">
                             <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                                 <i class="fas fa-exclamation"></i>
                             </div>
                             <div>
                                 <p class="mb-0 fw-bold small text-dark">Out of Stock</p>
                                 <p class="mb-0 small text-muted">{{ outOfStock }} products have 0 stock.</p>
                             </div>
                         </a>
                         <a href="/admin/products" v-if="lowStock > 0" class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3 border-0">
                             <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                                 <i class="fas fa-box"></i>
                             </div>
                             <div>
                                 <p class="mb-0 fw-bold small text-dark">Low Stock</p>
                                 <p class="mb-0 small text-muted">{{ lowStock }} items running low.</p>
                             </div>
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
            this.isMobile ? 'position-fixed h-100 z-3' : 'position-relative',
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
/* SIDEBAR TRANSITIONS */
.sidebar-transition {
    transition: width 0.3s ease, transform 0.3s ease;
    white-space: nowrap;
}

.sidebar-open { width: 265px; transform: translateX(0); }
.sidebar-closed-desktop { width: 75px; }
.sidebar-closed-mobile { width: 265px; transform: translateX(-100%); }

/* NAVIGATION LINKS */
.nav-link {
    color: #a2a3b7; /* Muted Light Blue-Grey */
    padding: 0.8rem 1rem;
    border-radius: 0.4rem;
    transition: all 0.2s ease;
}

.nav-link:hover {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.05);
}

.nav-link.active {
    background-color: #1b1b28 !important; /* Slightly Darker */
    color: #3699ff !important; /* Premium Blue */
    position: relative;
}
/* Left Border Accent for Active Item */
.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 10%;
    height: 80%;
    width: 4px;
    background-color: #3699ff;
    border-radius: 0 4px 4px 0;
}

/* HOVER UTILS */
.hover-white:hover { color: #ffffff !important; }
.hover-bg-danger:hover { background-color: #F64E60 !important; color: white !important; }

/* ICON WRAPPER */
.icon-wrapper {
    width: 35px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

/* SCROLLBAR */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #3f4254; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #5e6278; }

/* ANIMATIONS */
.fade-in { animation: fadeIn 0.2s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>