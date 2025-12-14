import './bootstrap';
import { createApp } from 'vue';

const app = createApp({
    data() {
        return {
            // Default: Open on Desktop (>=992px), Closed on Mobile (<992px)
            sidebarOpen: window.innerWidth >= 992,
            isMobile: window.innerWidth < 992,
            notifOpen: false
        }
    },
    methods: {
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
        toggleNotif() {
            this.notifOpen = !this.notifOpen;
        },
        handleResize() {
            const currentIsMobile = window.innerWidth < 992;
            
            // Only update if the mode (mobile vs desktop) changes
            if (this.isMobile !== currentIsMobile) {
                this.isMobile = currentIsMobile;
                // Reset sidebar: Open if going to Desktop, Closed if going to Mobile
                this.sidebarOpen = !this.isMobile; 
            }
        }
    },
    mounted() {
        window.addEventListener('resize', this.handleResize);
    },
    unmounted() {
        window.removeEventListener('resize', this.handleResize);
    }
});

// Custom directive to close dropdowns when clicking outside
app.directive('click-outside', {
    mounted(el, binding) {
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
});

app.mount('#app');