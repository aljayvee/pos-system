import './bootstrap';
import { createApp } from 'vue';

const app = createApp({
    data() {
        return {
            // Initialize: Desktop (>=992px) = Open, Mobile (<992px) = Closed
            sidebarOpen: window.innerWidth >= 992,
            isMobile: window.innerWidth < 992,
            notifOpen: false
        }
    },
    methods: {
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
            console.log('Sidebar Toggled. New State:', this.sidebarOpen);
        },
        toggleNotif() {
            this.notifOpen = !this.notifOpen;
        },
        handleResize() {
            const isNowMobile = window.innerWidth < 992;
            if (this.isMobile !== isNowMobile) {
                this.isMobile = isNowMobile;
                // Auto-reset sidebar state when switching view modes
                this.sidebarOpen = !this.isMobile;
            }
        }
    },
    mounted() {
        console.log('Vue App Mounted Successfully');
        window.addEventListener('resize', this.handleResize);
    },
    unmounted() {
        window.removeEventListener('resize', this.handleResize);
    }
});

// Robust Click Outside Directive
app.directive('click-outside', {
    mounted(el, binding) {
        el.clickOutsideEvent = function(event) {
            // Check if click was outside the element and its children
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        // Listen to both click and touchstart for better mobile support
        document.body.addEventListener('click', el.clickOutsideEvent);
        document.body.addEventListener('touchstart', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.body.removeEventListener('click', el.clickOutsideEvent);
        document.body.removeEventListener('touchstart', el.clickOutsideEvent);
    }
});

app.mount('#app');