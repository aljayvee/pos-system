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
                 <span class="ms-2 fw-bold fs-5 tracking-wide text-white fade-text" v-show="isOpen || isMobile">VERAPOS</span>
             </div>
         </div>

         <button v-if="!isMobile && isOpen" @click="toggleSidebar" class="btn btn-sm btn-link text-muted p-0 text-decoration-none hover-white">
             <i class="fas fa-bars fa-lg"></i>
         </button>
      </div>

      <div class="flex-fill overflow-auto py-3 custom-scrollbar" :class="{ 'opacity-50 pe-none': isNavigating }" @click.capture="handleNavClick">
         
         <div class="px-3 mb-4">
             <a href="/cashier/pos" class="btn btn-primary w-100 d-flex align-items-center justify-content-center py-2 shadow-sm text-uppercase fw-bold" style="letter-spacing: 0.5px;">
                 <i class="fas fa-cash-register"></i>
                 <span class="ms-2" v-show="isOpen || isMobile">Go to Cashier</span>
             </a>
         </div>

          <ul class="nav nav-pills flex-column px-2 gap-1">
             <!-- DASHBOARD -->
             <li class="nav-item">
                 <a href="/admin/dashboard" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath === '/admin/dashboard' }">
                    <div class="icon-wrapper"><i class="fas fa-tachometer-alt"></i></div>
                    <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Dashboard</span>
                 </a>
             </li>

             <!-- INVENTORY -->
             <template v-if="can('inventory.view')">
                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">Inventory</li>

                <li class="nav-item">
                    <a href="/admin/categories" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/categories') }">
                       <div class="icon-wrapper"><i class="fas fa-tags"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Categories</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/admin/products" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/products') }">
                       <div class="icon-wrapper"><i class="fas fa-box-open"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Product Management</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/admin/purchases" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/purchases') }">
                       <div class="icon-wrapper"><i class="fas fa-truck-loading"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Stock In History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/inventory" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/inventory') }">
                       <div class="icon-wrapper"><i class="fas fa-boxes"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Inventory Management</span>
                    </a>
                </li>
             </template>

             <!-- FINANCE -->
             <template v-if="can('sales.view') || can('reports.view')">
                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">Finance</li>
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
                <li class="nav-item">
                    <a href="/admin/credits" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/credits') }">
                       <div class="icon-wrapper"><i class="fas fa-file-invoice-dollar"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Credits (Utang)</span>
                    </a>
                </li>
             </template>

             <!-- ANALYTICS -->
             <template v-if="can('reports.view')">
                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">Analytics</li>
                <li class="nav-item">
                    <a href="/admin/reports" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/reports') }">
                       <div class="icon-wrapper"><i class="fas fa-chart-pie"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Reports & Analytics</span>
                    </a>
                </li>
             </template>

             <!-- ACCOUNTS -->
             <template v-if="can('user.manage')">
                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">Accounts Management</li>
                <li class="nav-item">
                    <a href="/admin/users" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/users') }">
                       <div class="icon-wrapper"><i class="fas fa-user-shield"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">User Management</span>
                    </a>
                </li>
             </template>

             <!-- LOGS -->
             <template v-if="can('logs.view') || can('sales.view')">
                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">Logs and Transactions</li>
                <li class="nav-item" v-if="can('sales.view')">
                    <a href="/admin/transactions" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/transactions') }">
                       <div class="icon-wrapper"><i class="fas fa-history"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Transactions</span>
                    </a>
                </li>
                <li class="nav-item" v-if="can('logs.view')">
                    <a href="/admin/logs" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/logs') }">
                       <div class="icon-wrapper"><i class="fas fa-clipboard-list"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Audit Logs</span>
                    </a>
                </li>
             </template>

             <!-- SETTINGS -->
             <template v-if="can('settings.manage')">
                <li class="nav-header px-3 mt-3 mb-1 text-muted small fw-bold text-uppercase overflow-hidden" v-show="isOpen || isMobile">More Features</li>
                <li class="nav-item">
                    <a href="/admin/settings" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/settings') }">
                       <div class="icon-wrapper"><i class="fas fa-cog"></i></div>
                       <span class="text-nowrap fade-text ms-2" v-show="isOpen || isMobile">Settings</span>
                    </a>
                </li>
             </template>
          </ul>
      </div>

      <!-- Footer Removed -->
    </aside>

    <div class="d-flex flex-column flex-fill overflow-hidden position-relative w-100 bg-light">
      
      <header class="navbar navbar-expand bg-white border-bottom shadow-sm px-4" style="height: 70px; min-height: 70px;">
        <button v-if="!isOpen && !isMobile" @click="toggleSidebar" class="btn btn-light border me-3 text-secondary"><i class="fas fa-bars"></i></button>
        <button v-if="isMobile" @click="toggleSidebar" class="btn btn-light border me-3 text-secondary"><i class="fas fa-bars"></i></button>

        <h5 class="h9 mb-0 text-dark"></h5>

      <div class="ms-auto d-flex align-items-center gap-3">
             
             <!-- NOTIFICATIONS -->
             <div class="position-relative" v-click-outside="closeNotif">
                 <button @click="toggleNotif" class="btn btn-light rounded-circle position-relative p-2 text-secondary hover-bg-light transition-all" :class="{ 'bg-light text-primary': notifOpen }" style="width: 42px; height: 42px;">
                     <i class="far fa-bell fa-lg"></i>
                     <span v-if="totalAlerts > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">{{ totalAlerts }}</span>
                 </button>
    
                 <div v-if="notifOpen" class="dropdown-menu dropdown-menu-end show shadow-lg border-0 mt-3 p-0 overflow-hidden rounded-4 notification-dropdown" style="z-index: 1060;">
                     <div class="px-4 py-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                         <h6 class="mb-0 fw-bold text-dark">Notifications</h6>
                         <span v-if="totalAlerts > 0" class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">{{ totalAlerts }} New</span>
                     </div>
                     <div class="list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
                         <div v-if="totalAlerts === 0" class="p-5 text-center text-muted">
                            <i class="far fa-bell-slash fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">No new notifications</p>
                         </div>
                         <template v-else>
                             <a href="/admin/products?filter=out_of_stock" v-if="outOfStock > 0" class="list-group-item list-group-item-action p-3 border-start border-4 border-danger">
                                 <div class="d-flex align-items-start">
                                     <div class="bg-danger bg-opacity-10 rounded p-2 me-3">
                                         <i class="fas fa-exclamation-triangle text-danger"></i>
                                     </div>
                                     <div>
                                         <small class="fw-bold text-dark d-block mb-1">Out of Stock Alert</small>
                                         <small class="text-muted d-block lh-sm">{{ outOfStock }} products have hit 0 stock.</small>
                                     </div>
                                 </div>
                             </a>
                             <a href="/admin/products?filter=low_stock" v-if="lowStock > 0" class="list-group-item list-group-item-action p-3 border-start border-4 border-warning">
                                 <div class="d-flex align-items-start">
                                     <div class="bg-warning bg-opacity-10 rounded p-2 me-3">
                                         <i class="fas fa-box-open text-warning"></i>
                                     </div>
                                     <div>
                                         <small class="fw-bold text-dark d-block mb-1">Low Stock Warning</small>
                                         <small class="text-muted d-block lh-sm">{{ lowStock }} items are running low.</small>
                                     </div>
                                 </div>
                             </a>
                         </template>
                     </div>
                     <div class="p-2 bg-light text-center border-top">
                        <a href="/admin/inventory" class="text-decoration-none small fw-bold text-primary">View Inventory</a>
                     </div>
                 </div>
            </div>

            <!-- MY ACCOUNT DROPDOWN (PREMIUM) -->
            <div class="position-relative" v-click-outside="closeAccountDropdown">
                <button @click="toggleAccountDropdown" class="btn btn-light d-flex align-items-center gap-2 rounded-pill px-1 pe-3 py-1 border shadow-sm hover-bg-light transition-all">
                    <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                         style="width: 32px; height: 32px; font-size: 0.9rem; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                        {{ userName.charAt(0).toUpperCase() }}
                    </div>
                    <span class="d-none d-sm-block fw-bold text-dark small">{{ userName }}</span>
                    <i class="fas fa-chevron-down text-muted small ms-1 transition-transform" :class="{ 'rotate-180': accountDropdownOpen }"></i>
                </button>

                <transition name="dropdown-slide">
                    <div v-if="accountDropdownOpen" class="dropdown-menu dropdown-menu-end show shadow-xl border-0 mt-3 p-0 overflow-hidden rounded-4 account-dropdown" style="z-index: 1060;">
                        
                        <!-- Premium Header with Pattern -->
                        <div class="position-relative bg-primary text-white p-4 text-center overflow-hidden" 
                             style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                            <!-- Decoding Pattern Overlay -->
                            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" 
                                 style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 10px 10px;"></div>
                            
                            <div class="position-relative z-10">
                                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow-lg mx-auto mb-3" 
                                     style="width: 70px; height: 70px; font-size: 2rem; font-family: monospace;">
                                    {{ userName.charAt(0).toUpperCase() }}
                                </div>
                                <h6 class="fw-bold mb-1">{{ userName }}</h6>
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 small fw-normal">{{ userRole.toUpperCase() }}</span>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="p-2">
                            <a href="/profile" class="dropdown-item p-3 rounded-3 d-flex align-items-center gap-3 fw-bold text-secondary hover-bg-light transition-all mb-1">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div>
                                    <div class="text-dark">Profile Settings</div>
                                    <small class="text-muted d-block fw-normal" style="font-size: 0.75rem">Manage your personal info</small>
                                </div>
                            </a>

                            <div class="dropdown-divider my-1 opacity-50"></div>

                            <form action="/logout" method="POST">
                                <input type="hidden" name="_token" :value="csrfToken">
                                <button class="dropdown-item p-3 rounded-3 d-flex align-items-center gap-3 fw-bold text-danger hover-bg-danger-soft transition-all w-100 text-start">
                                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-sign-out-alt ps-1"></i>
                                    </div>
                                    <div>
                                        <div>Sign Out</div>
                                        <small class="text-secondary d-block fw-normal" style="font-size: 0.75rem">End your session safely</small>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </transition>
            </div>

        </div>
      </header>

      <main class="flex-fill overflow-auto p-4">
          <div class="container-fluid p-0" style="max-width: 1600px;">
              <slot></slot>
          </div>
      </main>
    </div>
    <toast-manager />
  </div>
</template>

<script>
export default {
  props: ['userName', 'userRole', 'userPermissions', 'pageTitle', 'csrfToken', 'outOfStock', 'lowStock'],
  data() {
    const isMobile = window.innerWidth < 992;
    // Default open on desktop, closed on mobile. 
    // If desktop, check localStorage for user preference.
    let initialOpen = !isMobile;
    
    if (!isMobile) {
        const savedState = localStorage.getItem('sidebar_state');
        if (savedState !== null) {
            initialOpen = savedState === 'true';
        }
    }

    return {
      isMobile: isMobile, 
      isOpen: initialOpen,
      notifOpen: false,
      accountDropdownOpen: false,
      currentPath: window.location.pathname,
      isNavigating: false 
    };
  },
  methods: {
    can(permission) {
        // Admin role always has access (optional fallback, but better to be explicit)
        // If we want Strict RBAC, just check the permissions array.
        // For now, let's strictly check permissions array to respect overrides.
        // But if userPermissions is undefined (old session), default to role checks??
        if (!this.userPermissions) return this.userRole === 'admin';
        
        return this.userPermissions.includes(permission);
    },
    toggleSidebar() { 
        this.isOpen = !this.isOpen; 
        // Save preference only on desktop
        if (!this.isMobile) {
            localStorage.setItem('sidebar_state', this.isOpen);
        }
    },
    toggleNotif() { 
        this.notifOpen = !this.notifOpen; 
        if(this.notifOpen) this.accountDropdownOpen = false;
    },
    closeNotif() { this.notifOpen = false; },
    
    toggleAccountDropdown() { 
        this.accountDropdownOpen = !this.accountDropdownOpen; 
        if(this.accountDropdownOpen) this.notifOpen = false;
    },
    closeAccountDropdown() { this.accountDropdownOpen = false; },

    handleResize() {
      const mobile = window.innerWidth < 992;
      if (this.isMobile !== mobile) {
        this.isMobile = mobile;
        
        if (this.isMobile) {
            this.isOpen = false; // Always close when entering mobile view
        } else {
            // Restore saved state when returning to desktop
            const savedState = localStorage.getItem('sidebar_state');
            this.isOpen = savedState !== null ? savedState === 'true' : true;
        }
      }
    },
    
    // NEW: Handle sidebar clicks to prevent double-click / multiple activation
    handleNavClick(e) {
        // If already navigating, stop this click
        if (this.isNavigating) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        const link = e.target.closest('a');
        // Only lock if it's a real navigation link
        if (link && link.href && link.href !== '#' && !link.href.startsWith('javascript')) {
            this.isNavigating = true;
            // Failsafe: unlock after 5s if network is stuck
            setTimeout(() => { this.isNavigating = false; }, 5000);
        }
    }
  },
  directives: {
    clickOutside: {
      beforeMount(el, binding) {
        el.clickOutsideEvent = function(event) {
          if (!(el === event.target || el.contains(event.target))) {
            binding.value(event);
          }
        };
        document.body.addEventListener('click', el.clickOutsideEvent);
      },
      unmounted(el) {
        document.body.removeEventListener('click', el.clickOutsideEvent);
      }
    }
  }
};
</script>

<style scoped>
/* GLOBAL PREMIUM RESET */
.font-sans { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
.text-white { color: #ffffff !important; }
.text-muted { color: #94a3b8 !important; } /* Lighter slate for better contrast on dark */
.rotate-180 { transform: rotate(180deg); }
.transition-all { transition: all 0.2s ease-in-out; }
.hover-scale:hover { transform: scale(1.05); }

/* TRANSITIONS */
.sidebar-transition { transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); white-space: nowrap; }
.sidebar-open { width: 280px; transform: translateX(0); }
.sidebar-closed-desktop { width: 88px; }
.sidebar-closed-mobile { width: 280px; transform: translateX(-100%); }

/* SIDEBAR PREMIUM */
.sidebar-backdrop { backdrop-filter: blur(4px); }
.nav-link { 
    color: #94a3b8; 
    padding: 0.9rem 1.2rem; 
    border-radius: 0.75rem; 
    transition: all 0.3s ease; 
    margin-bottom: 4px; 
    font-weight: 500;
}
.nav-link:hover { 
    color: #ffffff; 
    background-color: rgba(255, 255, 255, 0.08); 
    transform: translateX(4px);
}
.nav-link.active { 
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.05) 100%); 
    color: #60a5fa !important; 
    position: relative; 
    box-shadow: inset 3px 0 0 0 #3b82f6; /* Modern left border */
}
/* Icon styling */
.icon-wrapper { 
    width: 24px; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    font-size: 1.2rem; 
    flex-shrink: 0; 
    transition: transform 0.2s;
}
.nav-link:hover .icon-wrapper, .nav-link.active .icon-wrapper {
    transform: scale(1.1);
}

/* HEADER GLASSMORPHISM */
header.navbar {
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
    position: relative;
    z-index: 1020; /* Ensure header is above content cards */
}

/* NOTIFICATION & ACCOUNT DROPDOWN PREMIUM */
.notification-dropdown, .account-dropdown { 
    width: 380px; 
    position: absolute; 
    right: 0; 
    border: 1px solid rgba(0,0,0,0.05); 
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
}

.account-dropdown { min-width: 260px; width: auto; } /* Specific tweak for account */

/* MOBILE OPTIMIZATIONS */
@media (max-width: 576px) {
    .notification-dropdown { 
        position: fixed; /* Center relative to screen */
        top: 80px;
        left: 50%;
        right: auto;
        transform: translateX(-50%); /* Perfect Center */
        width: 350px; /* Slightly wider for better content fit */
        max-width: 92vw; /* Ensure no overflow */
        margin-top: 0;
        border-radius: 1rem;
        max-height: 80vh;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .account-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        left: auto;
        width: auto;
        min-width: 250px;
        max-width: 300px; /* Prevent it from being too wide */
        margin-top: 0.5rem;
        border-radius: 1rem;
    }
}

/* DROPDOWN ANIMATION */
.dropdown-slide-enter-active,
.dropdown-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.dropdown-slide-enter-from,
.dropdown-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}

.dropdown-slide-enter-to,
.dropdown-slide-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* UTILS */
.hover-white:hover { color: #ffffff !important; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
.fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>