<template>
    <div class="d-flex" id="wrapper" :class="{ 'toggled': !sidebarOpen }">
        
        <div id="sidebar-wrapper">
            <div class="sidebar-header">
                <div class="d-flex align-items-center flex-grow-1">
                    <i class="fas fa-store text-primary fa-lg me-3"></i> 
                    <span class="fw-bold text-white tracking-wide fs-5">SariPOS</span>
                </div>
                <button class="btn btn-link text-muted p-0 d-lg-none" @click="toggleSidebar">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>

            <div class="sidebar-content">
                <div class="list-group list-group-flush">
                    <a href="/cashier" class="list-group-item">
                        <i class="fas fa-cash-register text-success"></i>
                        <span>Cashier POS</span>
                    </a>
                    
                    <template v-if="userRole === 'admin'">
                        <div class="menu-label">Overview</div>
                        <a href="/admin/dashboard" class="list-group-item">
                            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                        </a>
                        
                        <div class="menu-label">Inventory</div>
                        <a href="/admin/products" class="list-group-item">
                            <i class="fas fa-box"></i> <span>Products</span>
                        </a>
                        <a href="/admin/inventory" class="list-group-item">
                            <i class="fas fa-warehouse"></i> <span>Stock Level</span>
                        </a>
                        </template>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="user-card">
                    <div class="user-avatar">{{ userName.charAt(0) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ userName }}</div>
                        <div class="user-role">{{ userRole }}</div>
                    </div>
                </div>
                <form action="/logout" method="POST">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <button class="btn-logout" type="submit">
                        <i class="fas fa-sign-out-alt"></i> <span>LOGOUT</span>
                    </button>
                </form>
            </div>
        </div>

        <div id="page-content-wrapper">
            
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-4 sticky-top" style="height: var(--top-nav-height);">
                
                <button class="btn btn-light border shadow-sm me-3" @click="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>

                <h5 class="m-0 fw-bold text-dark d-none d-lg-block">
                    {{ pageTitle }}
                </h5>

                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown me-3 position-relative">
                        <a class="nav-link position-relative" href="#" @click.prevent="notifOpen = !notifOpen">
                            <i class="fas fa-bell fa-lg text-secondary"></i>
                            <span v-if="totalAlerts > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                                {{ totalAlerts }}
                            </span>
                        </a>
                        
                        <div class="dropdown-menu dropdown-menu-end notification-menu shadow p-0" 
                             :class="{ 'show': notifOpen }" 
                             style="position: absolute; right: 0; top: 100%;">
                            
                            <div class="p-3 border-bottom bg-light">
                                <h6 class="mb-0 fw-bold text-dark">Notifications</h6>
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <a v-if="outOfStock > 0" class="dropdown-item py-3 px-3 border-bottom" href="/admin/products">
                                    <div class="text-danger small fw-bold">Out of Stock</div>
                                    <div class="small text-muted">{{ outOfStock }} items need restocking</div>
                                </a>
                                <a v-if="lowStock > 0" class="dropdown-item py-3 px-3 border-bottom" href="/admin/products">
                                    <div class="text-warning small fw-bold">Low Stock</div>
                                    <div class="small text-muted">{{ lowStock }} items running low</div>
                                </a>
                                <div v-if="totalAlerts === 0" class="p-4 text-center small text-muted">
                                    No new notifications
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid p-4">
                <slot></slot>
            </div>
        </div>

        <div class="sidebar-backdrop" 
             v-if="isMobile && sidebarOpen" 
             @click="sidebarOpen = false">
        </div>

    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';

export default {
    props: {
        userName: String,
        userRole: String,
        pageTitle: String,
        csrfToken: String,
        outOfStock: Number,
        lowStock: Number
    },
    setup(props) {
        const sidebarOpen = ref(window.innerWidth >= 992);
        const isMobile = ref(window.innerWidth < 992);
        const notifOpen = ref(false);

        const totalAlerts = computed(() => {
            return (props.outOfStock || 0) + (props.lowStock || 0);
        });

        const toggleSidebar = () => {
            sidebarOpen.value = !sidebarOpen.value;
        };

        onMounted(() => {
            window.addEventListener('resize', () => {
                const mobileCheck = window.innerWidth < 992;
                if (isMobile.value !== mobileCheck) {
                    isMobile.value = mobileCheck;
                    // Auto-show sidebar on desktop, auto-hide on mobile
                    sidebarOpen.value = !mobileCheck;
                }
            });
        });

        return {
            sidebarOpen,
            isMobile,
            notifOpen,
            totalAlerts,
            toggleSidebar
        };
    }
}
</script>

<style scoped>
/* Scoped styles specific to the Sidebar Component */
/* (You can keep most global styles in layout.blade.php but specific toggle classes work best here) */

#wrapper {
    display: flex;
    width: 100%;
    align-items: stretch;
}

#sidebar-wrapper {
    min-width: 280px;
    max-width: 280px;
    background: #1e1e2d;
    color: #9899ac;
    transition: all 0.3s;
    position: fixed;
    height: 100vh;
    z-index: 1050;
    /* Desktop: Open by default */
    left: 0;
}

#page-content-wrapper {
    width: 100%;
    margin-left: 280px; /* Matches Sidebar Width */
    transition: all 0.3s;
}

/* CLOSED STATE (Desktop) */
#wrapper.toggled #sidebar-wrapper {
    margin-left: -280px;
}

#wrapper.toggled #page-content-wrapper {
    margin-left: 0;
}

/* MOBILE RESPONSIVENESS */
@media (max-width: 991.98px) {
    #sidebar-wrapper {
        margin-left: -280px; /* Hidden by default on mobile */
    }
    #page-content-wrapper {
        margin-left: 0;
        width: 100%;
    }
    
    /* When Toggled OPEN on Mobile */
    #wrapper.toggled #sidebar-wrapper {
        margin-left: 0; /* Slide in */
    }
    
    /* Backdrop */
    .sidebar-backdrop {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
    }
}
</style>