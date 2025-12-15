<template>
  <div class="app-wrapper" :class="{ 'sidebar-collapsed': !isOpen, 'mobile-open': isMobile && isOpen }">
    
    <div v-if="isMobile && isOpen" class="sidebar-backdrop" @click="toggleSidebar"></div>

    <aside class="sidebar">
      <div class="sidebar-header">
        
        <button v-if="isMobile" class="mobile-toggle-btn" @click="toggleSidebar">
             <i class="fas fa-bars"></i>
        </button>

        <i class="fas fa-store text-primary fa-lg me-2"></i>
        <span class="logo-text" v-show="isOpen || isMobile">SariPOS</span>
      </div>

      <div class="sidebar-content">
        <nav class="menu">
          <a href="/cashier" class="menu-item" :class="{ 'active': currentPath.includes('/cashier') }">
            <i class="fas fa-cash-register text-success"></i>
            <span class="label" v-show="isOpen || isMobile">Cashier POS</span>
          </a>

          <div v-if="userRole === 'admin'">
             <div class="menu-label" v-show="isOpen || isMobile">Overview</div>
             <a href="/admin/dashboard" class="menu-item" :class="{ 'active': currentPath === '/admin/dashboard' }">
               <i class="fas fa-tachometer-alt"></i><span class="label" v-show="isOpen || isMobile">Dashboard</span>
             </a>

             <div class="menu-label" v-show="isOpen || isMobile">Inventory</div>
             <a href="/admin/products" class="menu-item" :class="{ 'active': currentPath.includes('/products') }">
               <i class="fas fa-box"></i><span class="label" v-show="isOpen || isMobile">Products</span>
             </a>
             <a href="/admin/inventory" class="menu-item" :class="{ 'active': currentPath.includes('/inventory') }">
               <i class="fas fa-warehouse"></i><span class="label" v-show="isOpen || isMobile">Stock Level</span>
             </a>
             <a href="/admin/purchases" class="menu-item" :class="{ 'active': currentPath.includes('/purchases') }">
              <i class="fas fa-truck-loading"></i> <span class="label" v-show="isOpen || isMobile">Restocking</span>
            </a>

             <div class="menu-label" v-show="isOpen || isMobile">Finance</div>
             <a href="/admin/customers" class="menu-item" :class="{ 'active': currentPath.includes('/customers') }">
               <i class="fas fa-users"></i><span class="label" v-show="isOpen || isMobile">Customers</span>
             </a>
             <a href="/admin/credits" class="menu-item" :class="{ 'active': currentPath.includes('/credits') }">
               <i class="fas fa-wallet"></i><span class="label" v-show="isOpen || isMobile">Credits</span>
             </a>
             <a href="/admin/suppliers" class="menu-item" :class="{ 'active': currentPath.includes('/suppliers') }">
              <i class="fas fa-dolly"></i> <span class="label" v-show="isOpen || isMobile">Suppliers</span>
            </a>

             <div class="menu-label" v-show="isOpen || isMobile">System</div>
             <a href="/admin/transactions" class="menu-item" :class="{ 'active': currentPath.includes('/transactions') }">
               <i class="fas fa-history"></i><span class="label" v-show="isOpen || isMobile">Transactions</span>
             </a>
             <a href="/admin/reports" class="menu-item" :class="{ 'active': currentPath.includes('/reports') }">
               <i class="fas fa-chart-pie"></i><span class="label" v-show="isOpen || isMobile">Reports</span>
             </a>
             <a href="/admin/users" class="menu-item" :class="{ 'active': currentPath.includes('/users') }">
               <i class="fas fa-user-shield"></i><span class="label" v-show="isOpen || isMobile">Users</span>
             </a>
             <a href="/admin/logs" class="menu-item" :class="{ 'active': currentPath.includes('/logs') }">
               <i class="fas fa-file-signature"></i><span class="label" v-show="isOpen || isMobile">Logs</span>
             </a>
             <a href="/admin/settings" class="menu-item" :class="{ 'active': currentPath.includes('/settings') }">
               <i class="fas fa-cog"></i><span class="label" v-show="isOpen || isMobile">Settings</span>
             </a>
          </div>
        </nav>
      </div>

      <div class="sidebar-footer">
        <form action="/logout" method="POST" class="w-100">
           <input type="hidden" name="_token" :value="csrfToken">
           <button class="btn-logout" :class="{ 'collapsed': !isOpen && !isMobile }">
             <i class="fas fa-sign-out-alt"></i>
             <span v-show="isOpen || isMobile">LOGOUT</span>
           </button>
        </form>
      </div>
    </aside>

    <div class="main-wrapper">
      <header class="top-navbar">
        <button class="nav-btn toggle-btn" @click="toggleSidebar">
          <i class="fas fa-bars"></i>
        </button>
        
        <h5 class="page-title" v-if="!isMobile">{{ pageTitle }}</h5>

        <div class="top-right">
            <div class="notification-wrapper" v-click-outside="closeNotif">
                <button class="nav-btn bell-btn" @click="toggleNotif">
                    <i class="fas fa-bell"></i>
                    <span v-if="totalAlerts > 0" class="badge-counter">{{ totalAlerts }}</span>
                </button>

                <div class="notif-dropdown" v-if="notifOpen">
                    <div class="notif-header">Notifications</div>
                    <div class="notif-body">
                         <div v-if="totalAlerts === 0" class="no-notif">No new notifications</div>
                         <div v-else class="p-2">
                             <a href="/admin/products" v-if="outOfStock > 0" class="notif-item">
                                <i class="fas fa-exclamation-circle text-danger"></i>
                                <div><strong>Out of Stock</strong><p>{{ outOfStock }} products need restocking</p></div>
                             </a>
                             <a href="/admin/products" v-if="lowStock > 0" class="notif-item">
                                <i class="fas fa-box-open text-warning"></i>
                                <div><strong>Running Low</strong><p>{{ lowStock }} items running low</p></div>
                             </a>
                         </div>
                    </div>
                </div>
            </div>
        </div>
      </header>

      <main class="content-area">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script>
export default {
  props: ['userName', 'userRole', 'pageTitle', 'csrfToken', 'outOfStock', 'lowStock'],
  data() {
    return {
      isOpen: window.innerWidth >= 992,
      isMobile: window.innerWidth < 992,
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
/* --- LAYOUT RESET --- */
.app-wrapper {
    display: flex;
    min-height: 100vh;
    background-color: #f3f4f6;
    overflow-x: hidden;
}

/* --- SIDEBAR STYLES --- */
.sidebar {
    width: 260px;
    background-color: #1e1e2d;
    color: #9899ac;
    display: flex;
    flex-direction: column;
    position: fixed;
    height: 100vh;
    z-index: 1000;
    transition: all 0.3s ease;
    left: 0; top: 0;
}
.sidebar-header {
    height: 70px;
    display: flex; 
    align-items: center; 
    /* Changed justify-content to start so items align left */
    justify-content: flex-start; 
    padding: 0 20px;
    background-color: #151521;
    color: white; font-weight: bold; font-size: 1.2rem;
}

/* NEW STYLE: Mobile Toggle Button inside Sidebar */
.mobile-toggle-btn {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 1.2rem;
    cursor: pointer;
    margin-right: 15px; /* Space between button and icon */
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.mobile-toggle-btn:hover {
    color: #3699ff;
}

.sidebar-content { flex-grow: 1; overflow-y: auto; padding: 20px 0; }
.sidebar-footer { padding: 15px; background-color: #151521; }

/* Menu Items */
.menu-label { padding: 20px 24px 10px; font-size: 0.75rem; text-transform: uppercase; font-weight: 600; color: #5d5f75; }
.menu-item {
    display: flex; align-items: center; padding: 12px 24px;
    color: #9899ac; text-decoration: none; border-left: 3px solid transparent;
    transition: all 0.2s; white-space: nowrap;
}
.menu-item:hover, .menu-item.active { background: rgba(255,255,255,0.04); color: white; }
.menu-item.active { border-left-color: #3699ff; color: #3699ff; background: rgba(54, 153, 255, 0.1); }
.menu-item i { width: 25px; text-align: center; margin-right: 10px; font-size: 1.1rem; }
.btn-logout {
    width: 100%; padding: 8px; background: rgba(246, 78, 96, 0.15); color: #f64e60;
    border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600;
}
.btn-logout:hover { background: #f64e60; color: white; }
.btn-logout.collapsed span { display: none; }

/* --- MAIN CONTENT & NAVBAR --- */
.main-wrapper {
    flex-grow: 1;
    margin-left: 260px;
    transition: margin-left 0.3s ease;
    display: flex; flex-direction: column;
    width: 100%;
}

.top-navbar {
    height: 70px;
    background: white;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    position: sticky; top: 0; z-index: 900;
}
.nav-btn {
    background: transparent !important;
    border: 1px solid #e4e6ef;
    border-radius: 4px; padding: 8px 12px; cursor: pointer;
    color: #5e6278; font-size: 1.1rem;
    display: flex; align-items: center; justify-content: center;
    min-width: 40px; min-height: 40px;
}
.nav-btn:hover { background-color: #f5f8fa !important; color: #3699ff; }
.page-title { margin: 0; font-weight: 600; color: #181c32; }

/* Notifications */
.notification-wrapper { position: relative; }
.badge-counter {
    position: absolute; top: -5px; right: -5px;
    background: #f64e60; color: white; font-size: 10px; padding: 2px 5px; border-radius: 50%;
}
.notif-dropdown {
    position: absolute; right: 0; top: 50px; width: 280px; background: white;
    box-shadow: 0 0 20px rgba(0,0,0,0.1); border-radius: 6px; padding: 10px; z-index: 1100;
}
.notif-item { display: flex; gap: 12px; padding: 12px 15px; text-decoration: none; color: #333; border-bottom: 1px solid #f9f9f9; }
.notif-item:hover { background: #f8f9fa; }
.notif-item p { margin: 0; font-size: 0.8rem; color: #888; }

/* --- MOBILE RESPONSIVE --- */
@media (max-width: 991.98px) {
    .main-wrapper { margin-left: 0 !important; }
    .sidebar { transform: translateX(-100%); }
    .mobile-open .sidebar { transform: translateX(0); box-shadow: 5px 0 20px rgba(0,0,0,0.2); }
    .sidebar-backdrop {
        position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 950; backdrop-filter: blur(1px);
    }
}

/* --- DESKTOP COLLAPSED STATE --- */
@media (min-width: 992px) {
    .sidebar-collapsed .sidebar { width: 70px; }
    .sidebar-collapsed .main-wrapper { margin-left: 70px; }
    .sidebar-collapsed .logo-text, .sidebar-collapsed .label, .sidebar-collapsed .menu-label, .sidebar-collapsed .sidebar-footer span { display: none; }
    .sidebar-collapsed .sidebar-header { justify-content: center; padding: 0; }
    .sidebar-collapsed .sidebar-header i { margin-right: 0; }
}
</style>