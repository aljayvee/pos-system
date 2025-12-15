<template>
  <div class="flex h-screen w-full bg-gray-100 text-gray-800 font-sans overflow-hidden">
    
    <div 
        v-if="isMobile && isOpen" 
        class="fixed inset-0 bg-black/50 z-40 backdrop-blur-sm transition-opacity"
        @click="toggleSidebar"
    ></div>

    <aside 
      class="fixed lg:static inset-y-0 left-0 z-50 bg-[#1e1e2d] text-[#9899ac] transition-all duration-300 ease-in-out flex flex-col shadow-xl"
      :class="[
        isOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-20'
      ]"
    >
      <div class="h-16 flex items-center shrink-0 px-4 bg-[#151521] border-b border-white/5"
           :class="isMobile ? 'justify-start gap-4' : (isOpen ? 'justify-center' : 'justify-center')"
      >
         <button v-if="isMobile" @click="toggleSidebar" class="p-1 text-white hover:text-blue-500">
             <i class="fas fa-bars text-xl"></i>
         </button>

         <div class="flex items-center font-bold text-white text-lg tracking-wide whitespace-nowrap overflow-hidden">
             <i class="fas fa-store text-blue-500 text-xl" :class="{ 'mr-3': isOpen || isMobile }"></i>
             <span v-show="isOpen || isMobile">SariPOS</span>
         </div>
      </div>

      <nav class="flex-1 overflow-y-auto py-4 scrollbar-thin scrollbar-thumb-gray-700">
         <div class="space-y-1">
            
            <a href="/cashier" class="group flex items-center px-6 py-3 border-l-4 border-transparent hover:bg-white/5 hover:text-white transition-colors"
               :class="{ 'bg-blue-500/10 text-blue-400 border-l-blue-400': currentPath.includes('/cashier') }">
               <i class="fas fa-cash-register w-6 text-center text-lg"></i>
               <span class="ml-3 font-medium whitespace-nowrap" v-show="isOpen || isMobile">Cashier POS</span>
            </a>

            <template v-if="userRole === 'admin'">
                <div class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider" v-show="isOpen || isMobile">Overview</div>
                
                <a href="/admin/dashboard" class="flex items-center px-6 py-3 border-l-4 border-transparent hover:bg-white/5 hover:text-white transition-colors"
                   :class="{ 'bg-blue-500/10 text-blue-400 border-l-blue-400': currentPath === '/admin/dashboard' }">
                   <i class="fas fa-tachometer-alt w-6 text-center"></i>
                   <span class="ml-3 whitespace-nowrap" v-show="isOpen || isMobile">Dashboard</span>
                </a>

                <div class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider" v-show="isOpen || isMobile">Inventory</div>
                
                <a href="/admin/products" class="flex items-center px-6 py-3 border-l-4 border-transparent hover:bg-white/5 hover:text-white transition-colors"
                   :class="{ 'bg-blue-500/10 text-blue-400 border-l-blue-400': currentPath.includes('/products') }">
                   <i class="fas fa-box w-6 text-center"></i>
                   <span class="ml-3 whitespace-nowrap" v-show="isOpen || isMobile">Products</span>
                </a>
                
                <a href="/admin/inventory" class="flex items-center px-6 py-3 border-l-4 border-transparent hover:bg-white/5 hover:text-white transition-colors"
                   :class="{ 'bg-blue-500/10 text-blue-400 border-l-blue-400': currentPath.includes('/inventory') }">
                   <i class="fas fa-warehouse w-6 text-center"></i>
                   <span class="ml-3 whitespace-nowrap" v-show="isOpen || isMobile">Stock Level</span>
                </a>
                
                <div class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider" v-show="isOpen || isMobile">System</div>
                 <a href="/admin/settings" class="flex items-center px-6 py-3 border-l-4 border-transparent hover:bg-white/5 hover:text-white transition-colors"
                   :class="{ 'bg-blue-500/10 text-blue-400 border-l-blue-400': currentPath.includes('/settings') }">
                   <i class="fas fa-cog w-6 text-center"></i>
                   <span class="ml-3 whitespace-nowrap" v-show="isOpen || isMobile">Settings</span>
                </a>
            </template>

         </div>
      </nav>

      <div class="p-4 bg-[#151521] border-t border-white/5 shrink-0">
          <form action="/logout" method="POST">
             <input type="hidden" name="_token" :value="csrfToken">
             <button class="w-full flex items-center justify-center p-2 text-red-400 bg-red-500/10 rounded-lg hover:bg-red-500 hover:text-white transition-colors">
                 <i class="fas fa-sign-out-alt"></i>
                 <span class="ml-2 font-semibold whitespace-nowrap" v-show="isOpen || isMobile">LOGOUT</span>
             </button>
          </form>
      </div>
    </aside>

    <div class="flex-1 flex flex-col h-full overflow-hidden relative w-full">
      
      <header class="h-16 bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-4 lg:px-8 shrink-0 z-30">
        
        <button @click="toggleSidebar" class="hidden lg:flex items-center justify-center w-10 h-10 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-bars text-xl"></i>
        </button>

        <h1 class="text-lg lg:text-xl font-bold text-gray-800 truncate mx-4 flex-1 text-center lg:text-left">
            {{ pageTitle }}
        </h1>

        <div class="relative" v-click-outside="closeNotif">
             <button @click="toggleNotif" class="relative p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
                 <i class="fas fa-bell text-xl"></i>
                 <span v-if="totalAlerts > 0" class="absolute top-1 right-1 h-2.5 w-2.5 bg-red-500 rounded-full ring-2 ring-white"></span>
             </button>

             <div v-if="notifOpen" class="absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden origin-top-right transform transition-all">
                 <div class="px-4 py-3 bg-gray-50 font-semibold border-b text-sm text-gray-700">Notifications</div>
                 <div class="max-h-64 overflow-y-auto">
                     <div v-if="totalAlerts === 0" class="p-4 text-center text-gray-500 text-sm">No new alerts</div>
                     <template v-else>
                         <a href="/admin/products" v-if="outOfStock > 0" class="flex items-start gap-3 p-3 hover:bg-gray-50 border-b transition-colors">
                             <div class="p-2 bg-red-100 text-red-600 rounded-full"><i class="fas fa-exclamation-circle"></i></div>
                             <div>
                                 <p class="text-sm font-semibold text-gray-800">Out of Stock</p>
                                 <p class="text-xs text-gray-500">{{ outOfStock }} products need restocking.</p>
                             </div>
                         </a>
                         <a href="/admin/products" v-if="lowStock > 0" class="flex items-start gap-3 p-3 hover:bg-gray-50 border-b transition-colors">
                             <div class="p-2 bg-yellow-100 text-yellow-600 rounded-full"><i class="fas fa-box-open"></i></div>
                             <div>
                                 <p class="text-sm font-semibold text-gray-800">Low Stock</p>
                                 <p class="text-xs text-gray-500">{{ lowStock }} items running low.</p>
                             </div>
                         </a>
                     </template>
                 </div>
             </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-4 lg:p-8 bg-gray-50/50">
          <div class="w-full max-w-7xl mx-auto">
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
      // Principle: "Define breakpoints once".
      // We use 1024px (Tailwind 'lg') as the mental model for "Desktop".
      isMobile: window.innerWidth < 1024,
      isOpen: window.innerWidth >= 1024, // Open by default on Desktop
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
      // Logic: Sync JS state with CSS Breakpoint (lg: 1024px)
      const mobile = window.innerWidth < 1024;
      if (this.isMobile !== mobile) {
        this.isMobile = mobile;
        // Auto-reset: Open on desktop, Closed on mobile
        this.isOpen = !this.isMobile; 
      }
    }
  }
};
</script>