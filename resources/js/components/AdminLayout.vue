<template>
  <div class="d-flex w-100 overflow-hidden bg-body font-sans flex-fill" style="height: 100%;">
    
    <!-- MOBILE OVERLAY -->
    <div 
        v-if="isMobile && isOpen" 
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 fade-in"
        style="z-index: 1040;"
        @click="toggleSidebar"
    ></div>

    <!-- MOBILE BACKDROP FOR DROPDOWNS -->
    <div 
        v-if="isMobile && (notifOpen || accountDropdownOpen)" 
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 fade-in"
        style="z-index: 1055;"
        @click="closeAllDropdowns"
    ></div>

      <!-- SIDEBAR (Floating Glass Panel) -->
      <aside 
        class="d-flex flex-column sidebar-transition border border-white border-opacity-50 shadow-lg position-relative"
        :class="[sidebarClasses, { 'mt-3 mx-3 rounded-4': !isMobile }]"
        style="z-index: 1050; background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(16px);"
      >
        <!-- DECORATIVE BLUR -->
        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25 pointer-events-none" 
             style="background: linear-gradient(180deg, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 100%); mix-blend-mode: overlay;"></div>

        <!-- SIDEBAR HEADER -->
        <div class="d-flex align-items-center px-3 position-relative z-10" 
             style="height: 80px; min-height: 80px;"
             :class="(!isMobile && !isOpen) ? 'justify-content-center' : 'justify-content-between'"
        >
           <!-- BRAND -->
           <div class="d-flex align-items-center overflow-hidden" :class="{ 'w-100 justify-content-center': !isOpen && !isMobile }">
               <button v-if="isMobile" @click="toggleSidebar" class="btn btn-link text-dark p-0 me-3 text-decoration-none opacity-50 hover-opacity-100">
                   <i class="fas fa-arrow-left fa-lg"></i>
               </button>

               <div class="d-flex align-items-center text-nowrap">
                   <div v-if="!isMobile || !userPhoto" class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm hover-scale transition-all" 
                        style="width: 42px; height: 42px; background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);">
                      <i class="fas fa-layer-group fa-lg"></i>
                   </div>
                   <!-- Mobile Photo Brand -->
                   <img v-else :src="userPhoto" class="rounded-circle shadow-sm border" style="width: 42px; height: 42px; object-fit: cover;">

                   <!-- TITLE -->
                   <div class="ms-3 d-flex flex-column justify-content-center fade-text" v-show="isOpen || isMobile">
                       <span class="fw-bold fs-5 tracking-tight text-dark" v-if="!isMobile" style="font-family: 'Inter', sans-serif;">VERAPOS</span>
                       
                       <!-- Mobile Profile Header -->
                       <template v-else>
                           <span class="fw-bold text-dark leading-none small">{{ userName }}</span>
                           <span class="text-muted small" style="font-size: 0.7rem;">{{ userRole.toUpperCase() }}</span>
                       </template>
                   </div>
               </div>
           </div>
        </div>

      <div ref="sidebarContentRef" @scroll.passive="handleSidebarScroll" class="flex-fill overflow-auto px-3 py-2 custom-scrollbar position-relative z-10" :class="{ 'opacity-50 pe-none': isNavigating }" @click.capture="handleNavClick">
         
         <!-- POS BUTTON -->
         <div class="mb-4" v-if="can('pos.access') && !isMobile">
             <a href="/cashier/pos" class="btn btn-primary w-100 d-flex align-items-center justify-content-center py-3 shadow-lg hover-translate text-uppercase fw-bold border-0" 
                style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border-radius: 16px; letter-spacing: 0.5px;">
                 <i class="fas fa-cash-register"></i>
                 <span class="ms-2" v-show="isOpen || isMobile">Launch POS</span>
             </a>
         </div>

          <ul class="nav flex-column gap-2">
             <!-- DASHBOARD -->
             <li class="nav-item">
                 <a href="/admin/dashboard" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath === '/admin/dashboard' }">
                    <div class="icon-wrapper"><i class="fas fa-grid-2"></i></div> <!-- Using grid icon for modern feel if avail, fallback handles it generally -->
                    <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Dashboard</span>
                 </a>
             </li>

             <!-- INVENTORY -->
             <template v-if="can('inventory.view')">
                <li class="nav-header mt-4 mb-2 ps-2 text-uppercase text-xs fw-bold text-muted tracking-wider opacity-75" v-show="isOpen || isMobile">Inventory</li>

                <li class="nav-item">
                    <a href="/admin/categories" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/categories') }">
                       <div class="icon-wrapper"><i class="fas fa-tags"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Categories</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/admin/products" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/products') }">
                       <div class="icon-wrapper"><i class="fas fa-box"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Products</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/admin/purchases" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/purchases') }">
                       <div class="icon-wrapper"><i class="fas fa-truck-loading"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Stock In</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/inventory" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/inventory') }">
                       <div class="icon-wrapper"><i class="fas fa-warehouse"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Inventory</span>
                    </a>
                </li>
             </template>

             <!-- FINANCE -->
             <template v-if="can('sales.view') || can('reports.view')">
                <li class="nav-header mt-4 mb-2 ps-2 text-uppercase text-xs fw-bold text-muted tracking-wider opacity-75" v-show="isOpen || isMobile">Finance</li>
                <li class="nav-item">
                    <a href="/admin/customers" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/customers') }">
                       <div class="icon-wrapper"><i class="fas fa-users"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Customers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/suppliers" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/suppliers') }">
                       <div class="icon-wrapper"><i class="fas fa-dolly"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Suppliers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/credits" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/credits') }">
                       <div class="icon-wrapper"><i class="fas fa-file-invoice-dollar"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Credits</span>
                    </a>
                </li>
                <li class="nav-item" v-if="enableRegisterLogs == 1">
                    <a href="/admin/adjustments" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/adjustments') }">
                       <div class="icon-wrapper"><i class="fas fa-cash-register"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Cash Logs</span>
                    </a>
                </li>
             </template>
             
             <!-- TRANSACTIONS (Desktop Only) -->
             <template v-if="!isMobile && (can('sales.view') || can('reports.view'))">
                 <li class="nav-item">
                     <a href="/admin/transactions" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/transactions') }">
                        <div class="icon-wrapper"><i class="fas fa-history"></i></div>
                        <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Transactions</span>
                     </a>
                 </li>
             </template>

             <!-- ANALYTICS -->
             <template v-if="can('reports.view')">
                <li class="nav-header mt-4 mb-2 ps-2 text-uppercase text-xs fw-bold text-muted tracking-wider opacity-75" v-show="isOpen || isMobile">Analytics</li>
                <li class="nav-item">
                    <a href="/admin/reports" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/reports') }">
                       <div class="icon-wrapper"><i class="fas fa-chart-pie"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Reports</span>
                    </a>
                </li>
             </template>

             <!-- SYSTEM -->
             <template v-if="can('user.manage') || can('settings.manage')">
                <li class="nav-header mt-4 mb-2 ps-2 text-uppercase text-xs fw-bold text-muted tracking-wider opacity-75" v-show="isOpen || isMobile">System</li>
                <li class="nav-item" v-if="userRole === 'admin' && systemMode === 'multi'">
                    <a href="/admin/stores" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/stores') }">
                       <div class="icon-wrapper"><i class="fas fa-store"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Store Management</span>
                    </a>
                </li>
                <li class="nav-item" v-if="can('user.manage')">
                    <a href="/admin/users" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/users') }">
                        <div class="icon-wrapper"><i class="fas fa-user-shield"></i></div>
                        <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Users</span>
                    </a>
                </li>
                <li class="nav-item" v-if="can('settings.manage')">
                    <a href="/admin/settings" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/settings') }">
                       <div class="icon-wrapper"><i class="fas fa-cog"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">More Features</span>
                    </a>
                </li>
                <li class="nav-item" v-if="can('logs.view')">
                    <a href="/admin/logs" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/logs') }">
                       <div class="icon-wrapper"><i class="fas fa-history"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">Audit Logs</span>
                    </a>
                </li>
                
                <!-- BIR COMPLIANCE -->
                <li class="nav-item" v-if="enableBirCompliance">
                    <a href="/admin/bir" class="nav-link d-flex align-items-center" :class="{ 'active': currentPath.includes('/bir') }">
                       <div class="icon-wrapper"><i class="fas fa-file-invoice-dollar"></i></div>
                       <span class="text-nowrap fade-text ms-3 fw-medium" v-show="isOpen || isMobile">BIR Settings</span>
                    </a>
                </li>
             </template>
          </ul>
      </div>

       <!-- COLLAPSE TOGGLE (Desktop) -->
       <div v-if="!isMobile" class="p-3 mt-auto border-top border-light position-relative z-10">
           <button @click="toggleSidebar" class="btn btn-light w-100 d-flex align-items-center justify-content-center text-secondary border-0 hover-bg-light transition-all shadow-sm rounded-3">
               <i class="fas" :class="isOpen ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
           </button>
       </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="d-flex flex-column flex-fill position-relative w-100 bg-body" style="min-width: 0; height: 125vh; overflow-y: auto; overflow-x: hidden;">
      
      <!-- TOP NAVBAR (Floating Glass) -->
      <header class="navbar navbar-expand px-4 py-3 m-3 mt-3 rounded-4 shadow-sm align-items-center justify-content-between position-sticky top-0" 
              style="z-index: 1000; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.6);">
         
         <div class="d-flex align-items-center">

             <h5 class="h6 fw-bold text-dark mb-0 tracking-tight">Admin Panel</h5>
         </div>

         <div class="d-flex align-items-center gap-3">
             
             <!-- NOTIFICATIONS -->
             <div class="position-relative" v-click-outside="closeNotif">
                 <button @click="toggleNotif" class="btn btn-light rounded-circle position-relative p-2 text-secondary hover-bg-white shadow-sm transition-all border-0" :class="{ 'text-primary': notifOpen }" style="width: 44px; height: 44px;">
                     <i class="far fa-bell fa-lg"></i>
                     <span v-if="totalAlerts > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white shadow-sm">{{ totalAlerts }}</span>
                 </button>
    
                 <teleport to="body" :disabled="!isMobile">
                     <transition :name="isMobile ? 'bottom-sheet' : 'dropdown-slide'">
                     <div v-if="notifOpen" class="dropdown-menu dropdown-menu-end show shadow-xl border-0 mt-2 p-0 overflow-hidden rounded-4 notification-dropdown" style="z-index: 1060;">
                         <div class="px-4 py-3 bg-white border-bottom d-flex justify-content-between align-items-center sticky-top">
                             <h6 class="mb-0 fw-bold text-dark">Notifications</h6>
                             <span v-if="totalAlerts > 0" class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">{{ totalAlerts }} New</span>
                         </div>
                         <div class="list-group list-group-flush custom-scrollbar" style="max-height: 350px; overflow-y: auto;">
                             <div v-if="totalAlerts === 0" class="p-5 text-center text-muted">
                                <i class="far fa-bell-slash fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0 small">No new notifications</p>
                             </div>
                             <template v-else>
                                 <!-- PENDING APPROVALS -->
                                 <button 
                                    v-for="req in pendingApprovals" 
                                    :key="req.id"
                                    @click="openApprovalModal(req)"
                                    class="list-group-item list-group-item-action p-3 border-start border-4 border-info bg-info bg-opacity-10"
                                 >
                                     <div class="d-flex align-items-start">
                                         <div class="bg-info bg-opacity-25 text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                             <i class="fas fa-user-clock"></i>
                                         </div>
                                         <div>
                                             <small class="fw-bold text-dark d-block mb-1">Approval Request</small>
                                             <small class="text-secondary d-block lh-sm mb-1">
                                                 <span class="fw-bold">{{ req.requester.name }}</span> 
                                                 <span v-if="!req.target_user.is_active">wants to create account </span>
                                                 <span v-else>wants to promote </span>
                                                 <span class="fw-bold">{{ req.target_user.name }}</span> to 
                                                 <span class="badge bg-primary bg-opacity-25 text-primary ms-1">{{ req.new_role.toUpperCase() }}</span>
                                             </small>
                                             <small class="text-muted" style="font-size: 0.7rem;">Expires {{ new Date(req.expires_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</small>
                                         </div>
                                     </div>
                                 </button>

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
                     </transition>
                 </teleport>
            </div>

            <!-- MY ACCOUNT DROPDOWN (PREMIUM) -->
            <div class="position-relative" v-click-outside="closeAccountDropdown">
                <button @click="toggleAccountDropdown" class="btn btn-light d-flex align-items-center gap-2 rounded-pill px-1 pe-3 py-1 border-0 shadow-sm hover-translate transition-all" style="background: white;">
                    <div v-if="!userPhoto" class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                         style="width: 36px; height: 36px; font-size: 0.9rem; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                        {{ userName.charAt(0).toUpperCase() }}
                    </div>
                    <img v-else :src="userPhoto" class="rounded-circle shadow-sm" style="width: 36px; height: 36px; object-fit: cover;">
                    
                    <span class="d-none d-sm-block fw-bold text-dark small ms-1">{{ userName }}</span>
                    <i class="fas fa-chevron-down text-muted small ms-1 transition-transform" :class="{ 'rotate-180': accountDropdownOpen }"></i>
                </button>

                <teleport to="body" :disabled="!isMobile">
                    <transition :name="isMobile ? 'bottom-sheet' : 'dropdown-slide'">
                        <div v-if="accountDropdownOpen" class="dropdown-menu dropdown-menu-end show shadow-xl border-0 mt-2 p-0 overflow-hidden rounded-4 account-dropdown" style="z-index: 1060;">
                            
                            <!-- Premium Header with Pattern -->
                            <div class="position-relative bg-primary text-white p-4 text-center overflow-hidden" 
                                 style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                                <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" 
                                     style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 10px 10px;"></div>
                                
                                <div class="position-relative z-10">
                                    <div v-if="!userPhoto" class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow-lg mx-auto mb-3" 
                                         style="width: 64px; height: 64px; font-size: 1.8rem; font-family: monospace;">
                                        {{ userName.charAt(0).toUpperCase() }}
                                    </div>
                                    <img v-else :src="userPhoto" class="rounded-circle bg-white shadow-lg mx-auto mb-3 border border-4 border-white border-opacity-25" style="width: 64px; height: 64px; object-fit: cover;">

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

                                <div class="px-3 py-2">
                                    <p class="small text-muted fw-bold mb-2 text-uppercase ls-1" style="font-size: 0.7rem;">Appearance</p>
                                    <div class="d-flex bg-light rounded-pill p-1 border">
                                        <button @click="setTheme('light')" 
                                            class="btn btn-sm rounded-pill flex-fill d-flex align-items-center justify-content-center gap-1 transition-all"
                                            :class="currentTheme === 'light' ? 'bg-white shadow-sm text-primary fw-bold' : 'text-muted hover-text-dark'">
                                            <i class="fas fa-sun"></i>
                                        </button>
                                        <button @click="setTheme('dark')" 
                                            class="btn btn-sm rounded-pill flex-fill d-flex align-items-center justify-content-center gap-1 transition-all"
                                            :class="currentTheme === 'dark' ? 'bg-white shadow-sm text-primary fw-bold' : 'text-muted hover-text-dark'">
                                            <i class="fas fa-moon"></i>
                                        </button>
                                        <button @click="setTheme('system')" 
                                            class="btn btn-sm rounded-pill flex-fill d-flex align-items-center justify-content-center gap-1 transition-all"
                                            :class="currentTheme === 'system' ? 'bg-white shadow-sm text-primary fw-bold' : 'text-muted hover-text-dark'"
                                            title="System Default">
                                            <i class="fas fa-desktop"></i>
                                        </button>
                                    </div>
                                </div>

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
                </teleport>
            </div>

         </div>
      </header>

      <main class="flex-fill p-4 pt-1 bg-body" :class="{ 'pb-5 mb-5': isMobile }" style="padding-bottom: 100px !important;">
          <div class="container-fluid p-0" style="max-width: 1600px;">
              <slot></slot>
          </div>
      </main>

      <!-- MOBILE BOTTOM NAVIGATION (Native App Feel) -->
      <nav v-if="isMobile" class="fixed-bottom bg-white border-top shadow-lg pb-safe d-flex justify-content-around align-items-center px-2 py-2" style="z-index: 1045; height: auto; min-height: 70px;">
          <!-- 1. DASHBOARD -->
          <a href="/admin/dashboard" class="mobile-nav-item" :class="{ 'active': currentPath === '/admin/dashboard' }">
              <i class="fas fa-home mb-1"></i>
              <span>Home</span>
          </a>

          <!-- 2. INVENTORY -->
          <a href="/admin/products" class="mobile-nav-item" :class="{ 'active': currentPath.includes('/products') || currentPath.includes('/inventory') }">
              <i class="fas fa-box mb-1"></i>
              <span>Stock</span>
          </a>

          <!-- 3. POS FAB -->
          <div class="mobile-nav-item position-relative" style="overflow: visible;">
              <a href="/cashier/pos" class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
                 style="width: 56px; height: 56px; border: 4px solid #fff; position: absolute; top: -35px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                  <i class="fa-solid fa-cash-register fa-lg" style="color: #ffffff;"></i>
              </a>
              <i class="fas fa-circle mb-1 opacity-0"></i>
              <span class="text-primary fw-bold">POS</span>
          </div>

          <!-- 4. TRANSACTIONS -->
          <a href="/admin/transactions" class="mobile-nav-item" :class="{ 'active': currentPath.includes('/transactions') }">
              <i class="fas fa-history mb-1"></i>
              <span>Transactions</span>
          </a>

          <!-- 5. MENU -->
          <button @click="toggleSidebar" class="mobile-nav-item border-0 bg-transparent" :class="{ 'active': isOpen }">
              <i class="fas fa-bars mb-1"></i>
              <span>Menu</span>
          </button>
      </nav>

    </div>
    
    <toast-manager />
    
    <!-- NOTIFICATION PREVIEW TOAST -->
    <div v-if="showNewNotifToast" class="position-fixed top-0 end-0 p-3" style="z-index: 2000;">
        <div class="toast show align-items-center text-white bg-primary border-0 shadow-lg p-2 rounded-3" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    <i class="fas fa-bell me-2"></i> New Approval Request!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showNewNotifToast = false"></button>
            </div>
        </div>
    </div>
    
    <!-- APPROVAL MODAL -->
    <div v-if="showApprovalModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-xl rounded-4 overflow-hidden">
                <div class="modal-header text-white border-0 p-4" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-check me-2"></i>Review Request</h5>
                    <button type="button" class="btn-close btn-close-white" @click="closeApprovalModal"></button>
                </div>
                <!-- ... Content same as before but cleaner padding ... -->
                <div class="modal-body p-4 bg-light">
                     <div class="bg-white p-3 rounded-3 shadow-sm border mb-4">
                        <div class="d-flex align-items-center mb-2">
                             <div class="fw-bold text-secondary text-uppercase small" style="width: 80px;">Requester:</div>
                             <div class="fw-bold text-dark">{{ selectedRequest?.requester?.name }}</div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                             <div class="fw-bold text-secondary text-uppercase small" style="width: 80px;">Target:</div>
                             <div class="fw-bold text-primary">{{ selectedRequest?.target_user?.name }}</div>
                        </div>
                        <div class="d-flex align-items-center">
                             <div class="fw-bold text-secondary text-uppercase small" style="width: 80px;">Action:</div>
                             <div class="fw-bold text-dark">
                                 <span v-if="!selectedRequest?.target_user?.is_active">Activate New Account (<span class="badge bg-warning text-dark">{{ selectedRequest?.new_role }}</span>)</span>
                                 <span v-else>Promote to <span class="badge bg-warning text-dark">{{ selectedRequest?.new_role }}</span></span>
                             </div>
                        </div>
                    </div>

                    <p class="small text-muted mb-3">To approve this action, please confirm your Administrator password.</p>
                    
                    <div class="mb-4">
                        <input type="password" v-model="adminPassword" class="form-control form-control-lg bg-white border-0 shadow-sm" placeholder="Enter your password..." @keyup.enter="submitDecision('approve')">
                    </div>

                    <div class="d-grid gap-2">
                        <button @click="submitDecision('approve')" class="btn btn-primary btn-lg fw-bold shadow-lg" :disabled="modalLoading" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border: none;">
                            <span v-if="modalLoading" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-if="!modalLoading" class="fas fa-check-circle me-2"></i> Approve Request
                        </button>
                        <button @click="submitDecision('reject')" class="btn btn-white text-danger fw-bold shadow-sm" :disabled="modalLoading">
                            <i class="fas fa-times-circle me-2"></i> Reject Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>


<script>
import ThemeManager from '../theme';

export default {
  props: ['userName', 'userRole', 'userPermissions', 'userPhoto', 'pageTitle', 'csrfToken', 'outOfStock', 'lowStock', 'enableRegisterLogs', 'systemMode', 'enableBirCompliance'],
  data() {
    const isMobile = window.innerWidth < 992;
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
      isNavigating: false,
      
      // Approval Logic
      pendingApprovals: [],
      showApprovalModal: false,
      selectedRequest: null,
      adminPassword: '',
      modalLoading: false,
      pollInterval: null,
      
      // Toast Logic
      previousApprovalsCount: 0,
      showNewNotifToast: false,
      
      // Theme
      currentTheme: ThemeManager.getTheme()
    };
  },

  mounted() {
      if (this.userRole && this.userRole.toLowerCase() === 'admin') {
          this.fetchPendingApprovals(); // Initial fetch
          
          if (window.Echo) {
              window.Echo.private('admin-notifications')
                  .listen('.ApprovalRequestCreated', (e) => {
                      console.log('New Approval Request:', e.request);
                      this.pendingApprovals.push(e.request); // Assuming pendingApprovals exists or we trigger fetch
                      this.showNewNotifToast = true;
                      this.totalNotifications++; // Increment counter if you have one
                      // Optionally re-fetch to be safe and get full relations
                      this.fetchPendingApprovals();
                  });
          }
      }
      
      // Restore Sidebar Scroll
      if (!this.isMobile) {
          const savedScroll = localStorage.getItem('sidebar_scroll_pos');
          if (savedScroll && this.$refs.sidebarContentRef) {
              this.$nextTick(() => {
                  this.$refs.sidebarContentRef.scrollTop = parseInt(savedScroll);
              });
          }
      }
  },
  beforeUnmount() {
      if(this.pollInterval) clearInterval(this.pollInterval);
  },
  methods: {
    setTheme(theme) {
        ThemeManager.setTheme(theme);
        this.currentTheme = theme;
    },
    handleSidebarScroll(e) {
        if (!this.isMobile) {
            localStorage.setItem('sidebar_scroll_pos', e.target.scrollTop);
        }
    },
    can(permission) {
        if (!this.userPermissions) return this.userRole === 'admin';
        return this.userPermissions.includes(permission);
    },
    toggleSidebar() { 
        this.isOpen = !this.isOpen; 
        if (!this.isMobile) localStorage.setItem('sidebar_state', this.isOpen);
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
    closeAllDropdowns() {
        this.notifOpen = false;
        this.accountDropdownOpen = false;
    },
    handleResize() {
      const mobile = window.innerWidth < 992;
      if (this.isMobile !== mobile) {
        this.isMobile = mobile;
        if (this.isMobile) {
            this.isOpen = false; 
        } else {
            const savedState = localStorage.getItem('sidebar_state');
            this.isOpen = savedState !== null ? savedState === 'true' : true;
        }
    }
    },
    handleNavClick(e) {
        if (this.isNavigating) {
            e.preventDefault(); e.stopPropagation(); return;
        }
        const link = e.target.closest('a');
        if (link && link.href && link.href !== '#' && !link.href.startsWith('javascript')) {
            // Check if clicking the same link
            if (link.href === window.location.href) {
                e.preventDefault();
                return;
            }

            this.isNavigating = true;
            setTimeout(() => { this.isNavigating = false; }, 5000);
        }
    },
    
    // --- APPROVAL METHODS ---
    fetchPendingApprovals() {
        console.log('Fetching approvals...'); // Simplified
        fetch('/admin/approval/pending')
            .then(res => res.json())
            .then(data => {
                const newRequests = data.requests || [];
                if (newRequests.length > this.previousApprovalsCount) {
                     this.showNewNotifToast = true;
                     setTimeout(() => { this.showNewNotifToast = false; }, 2000);
                }
                this.pendingApprovals = newRequests;
                this.previousApprovalsCount = newRequests.length;
            })
            .catch(err => console.error('Poll error', err));
    },
    openApprovalModal(req) {
        this.selectedRequest = req;
        this.adminPassword = '';
        this.showApprovalModal = true;
        this.notifOpen = false;
    },
    closeApprovalModal() {
        this.showApprovalModal = false;
        this.selectedRequest = null;
    },
    submitDecision(decision) {
        if (decision === 'approve' && !this.adminPassword) {
            alert('Please enter your password to confirm approval.');
            return;
        }
        this.modalLoading = true;
        fetch(`/admin/approval/${this.selectedRequest.id}/decide`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
            body: JSON.stringify({ decision: decision, password: this.adminPassword })
        })
        .then(res => res.json())
        .then(data => {
            this.modalLoading = false;
            if (data.success) {
                this.closeApprovalModal();
                this.fetchPendingApprovals(); 
            } else {
                alert(data.message || 'Action failed.');
            }
        })
        .catch(err => {
            this.modalLoading = false;
            if (err.response && err.response.data) {
                 alert('Error: ' + (err.response.data.message || 'System error'));
            } else {
                 alert('System error: ' + err.message);
            }
        });
    }
  },
  computed: {
    sidebarClasses() {
        if (this.isMobile) {
            return this.isOpen 
                ? 'sidebar-open position-fixed top-0 end-0 bottom-0 shadow-lg' 
                : 'sidebar-closed-mobile position-fixed top-0 end-0 bottom-0';
        }
        return this.isOpen ? 'sidebar-open' : 'sidebar-closed-desktop';
    }
  },
  directives: {
    clickOutside: {
      beforeMount(el, binding) {
        el.clickOutsideEvent = function(event) {
          if (window.innerWidth < 992) return;
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
/* GLOBAL & RESET */
.font-sans { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
.bg-body { background-color: #f1f5f9; } /* Slate 100 base */
.text-xs { font-size: 0.65rem; }
.rotate-180 { transform: rotate(180deg); }
.transition-all { transition: all 0.2s ease-in-out; }
.hover-scale:hover { transform: scale(1.05); }
.hover-translate:hover { transform: translateY(-2px); }

/* SIDEBAR TRANSITIONS */
.sidebar-transition { transition: width 0.3s cubic-bezier(0.25, 1, 0.5, 1), transform 0.3s ease; white-space: nowrap; }
.sidebar-open { width: 280px; transform: translateX(0); }
.sidebar-closed-desktop { width: 88px; }

/* DESKTOP: Independent Scrolling */
@media (min-width: 992px) {
    aside {
        overflow: hidden !important; /* Let inner div scroll */
        height: 125vh !important; 
        max-height: 125vh !important;
        padding-bottom: 2rem;
    }

    /* FAILSAFE: Force hide text when closed */
    .sidebar-closed-desktop .fade-text,
    .sidebar-closed-desktop .nav-link span,
    .sidebar-closed-desktop .nav-header,
    .sidebar-closed-desktop .btn span {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        width: 0 !important;
    }
}
.sidebar-closed-mobile { 
    width: 85vw; 
    max-width: 320px; 
    transform: translateX(105%); 
    margin: 0 !important; 
    box-shadow: none !important; 
}

@media (max-width: 991px) {
    aside { 
        position: fixed !important; 
        top: 0; 
        right: 0; 
        left: auto;
        bottom: 0;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 0 !important;
        z-index: 2000;
        box-shadow: -4px 0 15px rgba(0,0,0,0.1) !important;
    }
    .sidebar-open { width: 85vw; max-width: 320px; transform: translateX(0); }
    .nav-link { padding: 1rem 1.2rem; font-size: 1.05rem; }
}

/* NAVIGATION LINKS */
.nav-link { 
    color: #475569; /* Slate 600 */
    padding: 0.8rem 1rem; 
    border-radius: 0.75rem; 
    transition: all 0.2s ease; 
    font-weight: 500;
}
.nav-link:hover { 
    color: #4f46e5; 
    background-color: rgba(79, 70, 229, 0.05); 
    transform: translateX(3px);
}
.nav-link.active { 
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); 
    color: white !important; 
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}
.nav-link.active .icon-wrapper { color: white !important; }

/* ICONS */
.icon-wrapper { 
    width: 24px; display: flex; justify-content: center; align-items: center; 
    font-size: 1.1rem; flex-shrink: 0; color: #64748b; transition: all 0.2s; 
}
.nav-link:hover .icon-wrapper { color: #4f46e5; transform: scale(1.1); }

/* DROPDOWNS */
.dropdown-slide-enter-active, .dropdown-slide-leave-active { transition: all 0.2s ease; }
.dropdown-slide-enter-from, .dropdown-slide-leave-to { opacity: 0; transform: translateY(10px); }
.notification-dropdown, .account-dropdown { 
    width: 380px; position: absolute; right: 0; top: 100%; margin-top: 1rem;
    border: 1px solid rgba(255,255,255,0.6); 
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    border-radius: 1.5rem;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
}
.account-dropdown { width: 280px; }

/* MOBILE BOTTOM NAV */
.mobile-nav-item {
    font-size: 0.7rem; color: #64748b; display: flex; flex-direction: column; 
    align-items: center; justify-content: center; transition: all 0.2s; 
    width: 60px; text-decoration: none;
}
.mobile-nav-item.active { color: #4f46e5; font-weight: bold; }
.mobile-nav-item i { font-size: 1.2rem; }
.pb-safe { padding-bottom: env(safe-area-inset-bottom); }

/* SCROLLBAR */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.5); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.8); }

/* MOBILE DROPDOWN OVERRIDES (Bottom Sheet) */
@media (max-width: 991px) {
    .notification-dropdown, .account-dropdown {
        position: fixed !important;
        top: auto !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        border-radius: 1.5rem 1.5rem 0 0 !important;
        max-height: 80vh;
        transform: none !important; /* Reset any translate */
        box-shadow: 0 -10px 40px rgba(0,0,0,0.2) !important;
    }
}

/* TRANSITIONS */
/* Desktop Slide */
.dropdown-slide-enter-active, .dropdown-slide-leave-active { transition: all 0.2s ease; }
.dropdown-slide-enter-from, .dropdown-slide-leave-to { opacity: 0; transform: translateY(10px); }

/* Mobile Bottom Sheet Slide */
.bottom-sheet-enter-active, .bottom-sheet-leave-active { transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1); }
.bottom-sheet-enter-from, .bottom-sheet-leave-to { transform: translateY(100%); }
</style>