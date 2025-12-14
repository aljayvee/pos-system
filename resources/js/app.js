import './bootstrap';
import { createApp } from 'vue';

const app = createApp({
    data() {
        return {
            // Default to closed on mobile (< 992px), open on desktop (>= 992px)
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
            const currentMobile = window.innerWidth < 992;
            
            // If we switched from desktop to mobile or vice-versa
            if (this.isMobile !== currentMobile) {
                this.isMobile = currentMobile;
                // Auto-set sidebar state based on new device width
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

// Directive to close dropdowns when clicking outside
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