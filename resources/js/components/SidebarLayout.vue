<template>
  <div class="app-wrapper" :class="{ 'sidebar-collapsed': !isOpen, 'mobile-open': isMobile && isOpen }">
    
    <div v-if="isMobile && isOpen" class="sidebar-backdrop" @click="toggleSidebar"></div>

    <aside class="sidebar">
      <div class="sidebar-header">
        <i class="fas fa-store text-primary fa-lg me-3"></i>
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
              <i class="fas fa-tachometer-alt"></i>
              <span class="label" v-show="isOpen || isMobile">Dashboard</span>
            </a>

            <div class="menu-label" v-show="isOpen || isMobile">Inventory</div>
            <a href="/admin/products" class="menu-item" :class="{ 'active': currentPath.includes('/products') }">
              <i class="fas fa-box"></i> <span class="label" v-show="isOpen || isMobile">Products</span>
            </a>
            <a href="/admin/inventory" class="menu-item" :class="{ 'active': currentPath.includes('/inventory') }">
              <i class="fas fa-warehouse"></i> <span class="label" v-show="isOpen || isMobile">Stock Level</span>
            </a>
             <a href="/admin/purchases" class="menu-item" :class="{ 'active': currentPath.includes('/purchases') }">
              <i class="fas fa-truck-loading"></i> <span class="label" v-show="isOpen || isMobile">Restocking</span>
            </a>

            <div class="menu-label" v-show="isOpen || isMobile">Finance</div>
            <a href="/admin/customers" class="menu-item" :class="{ 'active': currentPath.includes('/customers') }">
              <i class="fas fa-users"></i> <span class="label" v-show="isOpen || isMobile">Customers</span>
            </a>
            <a href="/admin/credits" class="menu-item" :class="{ 'active': currentPath.includes('/credits') }">
              <i class="fas fa-wallet"></i> <span class="label" v-show="isOpen || isMobile">Credits</span>
            </a>
             <a href="/admin/suppliers" class="menu-item" :class="{ 'active': currentPath.includes('/suppliers') }">
              <i class="fas fa-dolly"></i> <span class="label" v-show="isOpen || isMobile">Suppliers</span>
            </a>

            <div class="menu-label" v-show="isOpen || isMobile">System</div>
            <a href="/admin/transactions" class="menu-item" :class="{ 'active': currentPath.includes('/transactions') }">
              <i class="fas fa-history"></i> <span class="label" v-show="isOpen || isMobile">Transactions</span>
            </a>
            <a href="/admin/reports" class="menu-item" :class="{ 'active': currentPath.includes('/reports') }">
              <i class="fas fa-chart-pie"></i> <span class="label" v-show="isOpen || isMobile">Reports</span>
            </a>
            <a href="/admin/users" class="menu-item" :class="{ 'active': currentPath.includes('/users') }">
              <i class="fas fa-user-shield"></i> <span class="label" v-show="isOpen || isMobile">Users</span>
            </a>
            <a href="/admin/logs" class="menu-item" :class="{ 'active': currentPath.includes('/logs') }">
              <i class="fas fa-file-signature"></i> <span class="label" v-show="isOpen || isMobile">Logs</span>
            </a>
            <a href="/admin/settings" class="menu-item" :class="{ 'active': currentPath.includes('/settings') }">
              <i class="fas fa-cog"></i> <span class="label" v-show="isOpen || isMobile">Settings</span>
            </a>

          </div>
        </nav>
      </div>

      <div class="sidebar-footer">
        <div class="user-card" v-show="isOpen || isMobile">
          <div class="user-avatar">{{ userName.charAt(0) }}</div>
          <div class="user-info">
            <div class="user-name">{{ userName }}</div>
            <div class="user-role">{{ userRole }}</div>
          </div>
        </div>
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
        <button class="toggle-btn" @click="toggleSidebar">
          <i class="fas fa-bars"></i>
        </button>
        
        <h5 class="page-title d-none d-lg-block">{{ pageTitle }}</h5>

        <div class="top-right">
           <div class="notification-wrapper" v-click-outside="closeNotif">
                <div class="bell-icon" @click="toggleNotif">
                    <i class="fas fa-bell"></i>
                    <span v-if="totalAlerts > 0" class="badge-counter">{{ totalAlerts }}</span>
                </div>

                <div class="notif-dropdown" v-if="notifOpen">
                    <div class="notif-header">Notifications</div>
                    <div class="notif-body">
                         <a href="/admin/products" v-if="outOfStock > 0" class="notif-item">
                            <i class="fas fa-exclamation-circle text-danger"></i>
                            <div>
                                <strong>Out of Stock</strong>
                                <p>{{ outOfStock }} products need restocking</p>
                            </div>
                        </a>
                        <a href="/admin/products" v-if="lowStock > 0" class="notif-item">
                            <i class="fas fa-box-open text-warning"></i>
                            <div>
                                <strong>Running Low</strong>
                                <p>{{ lowStock }} items running low</p>
                            </div>
                        </a>
                        <div v-if="totalAlerts === 0" class="no-notif">No new notifications</div>
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
  props: {
    userName: String,
    userRole: String,
    pageTitle: { type: String, default: 'Dashboard' },
    csrfToken: String,
    // Notification props passed from Blade
    outOfStock: { type: Number, default: 0 },
    lowStock: { type: Number, default: 0 }
  },
  data() {
    return {
      isOpen: window.innerWidth >= 992,
      isMobile: window.innerWidth < 992,
      notifOpen: false,
      currentPath: window.location.pathname
    };
  },
  computed: {
    totalAlerts() {
        return this.outOfStock + this.lowStock;
    }
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
        // Auto-show sidebar on desktop, hide on mobile
        this.isOpen = !this.isMobile;
      }
    }
  }
};
</script>

<style scoped>
/* COLORS */
:root {
    --primary-bg: #1e1e2d;
    --secondary-bg: #151521;
    --text-muted: #9899ac;
    --text-light: #ffffff;
    --active-blue: #3699ff;
    --active-bg: rgba(54, 153, 255, 0.15);
}

.app-wrapper {
    display: flex;
    min-height: 100vh;
    background-color: #f3f4f6;
    overflow-x: hidden;
}

/* SIDEBAR */
.sidebar {
    width: 260px;
    background-color: #1e1e2d;
    color: #9899ac;
    display: flex;
    flex-direction: column;
    transition: width 0.3s ease, transform 0.3s ease;
    position: fixed;
    height: 100vh;
    z-index: 1000;
}

/* Header */
.sidebar-header {
    height: 70px;
    display: flex;
    align-items: center;
    padding: 0 24px;
    background-color: #151521;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    font-weight: bold;
    color: white;
    font-size: 1.2rem;
    white-space: nowrap;
    overflow: hidden;
}

/* Menu */
.sidebar-content {
    flex-grow: 1;
    overflow-y: auto;
    padding: 20px 0;
}

.menu-label {
    padding: 20px 24px 10px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    color: #5d5f75;
    white-space: nowrap;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 12px 24px;
    color: #9899ac;
    text-decoration: none;
    transition: all 0.2s;
    border-left: 3px solid transparent;
    white-space: nowrap;
    overflow: hidden;
}

.menu-item:hover, .menu-item.active {
    background-color: rgba(255,255,255,0.04);
    color: #ffffff;
}

.menu-item.active {
    background-color: rgba(54, 153, 255, 0.1);
    color: #3699ff;
    border-left-color: #3699ff;
}

.menu-item i {
    width: 25px;
    text-align: center;
    font-size: 1.1rem;
    margin-right: 10px;
    flex-shrink: 0;
}

/* Footer */
.sidebar-footer {
    padding: 15px;
    background-color: #151521;
    border-top: 1px solid rgba(255,255,255,0.05);
}
.user-card {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    white-space: nowrap;
    overflow: hidden;
}
.user-avatar {
    width: 35px; height: 35px;
    background: #3699ff; color: white;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-weight: bold;
}
.user-info { font-size: 0.85rem; }
.user-name { color: white; font-weight: bold; }
.user-role { font-size: 0.7rem; text-transform: uppercase; }

.btn-logout {
    width: 100%;
    padding: 8px;
    background: rgba(246, 78, 96, 0.15);
    color: #f64e60;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    font-weight: 600;
}
.btn-logout:hover { background: #f64e60; color: white; }
.btn-logout.collapsed span { display: none; }

/* COLLAPSED STATE (Desktop) */
.sidebar-collapsed .sidebar { width: 70px; }
.sidebar-collapsed .menu-item span, 
.sidebar-collapsed .menu-label,
.sidebar-collapsed .logo-text,
.sidebar-collapsed .user-card { display: none; }
.sidebar-collapsed .menu-item i { margin-right: 0; }
.sidebar-collapsed .sidebar-header { justify-content: center; padding: 0; }

/* MAIN CONTENT */
.main-wrapper {
    flex-grow: 1;
    margin-left: 260px; /* Width of open sidebar */
    transition: margin-left 0.3s ease;
    display: flex;
    flex-direction: column;
}
.sidebar-collapsed .main-wrapper { margin-left: 70px; }

/* TOP NAVBAR */
.top-navbar {
    height: 70px;
    background: white;
    display: flex;
    align-items: center;
    padding: 0 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    justify-content: space-between;
    position: sticky; top: 0; z-index: 900;
}
.toggle-btn { background: none; border: 1px solid #eee; padding: 6px 12px; border-radius: 4px; color: #555; cursor: pointer; }

/* NOTIFICATIONS */
.notification-wrapper { position: relative; }
.bell-icon { cursor: pointer; position: relative; padding: 5px; color: #7e8299; }
.badge-counter {
    position: absolute; top: 0; right: 0;
    background: #f64e60; color: white;
    font-size: 10px; padding: 2px 4px; border-radius: 50%;
}
.notif-dropdown {
    position: absolute; right: 0; top: 120%;
    width: 300px;
    background: white;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    border-radius: 8px;
    overflow: hidden;
    z-index: 1050;
}
.notif-header { padding: 12px 15px; font-weight: bold; border-bottom: 1px solid #eee; }
.notif-item { display: flex; gap: 12px; padding: 12px 15px; text-decoration: none; color: #333; border-bottom: 1px solid #f9f9f9; }
.notif-item:hover { background: #f8f9fa; }
.notif-item p { margin: 0; font-size: 0.8rem; color: #888; }
.no-notif { padding: 20px; text-align: center; color: #aaa; font-size: 0.9rem; }

/* MOBILE RESPONSIVE */
@media (max-width: 991.98px) {
    .sidebar { transform: translateX(-100%); width: 260px; }
    .main-wrapper { margin-left: 0 !important; }
    
    .mobile-open .sidebar { transform: translateX(0); }
    
    .sidebar-backdrop {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 950;
        backdrop-filter: blur(2px);
    }
}
</style>