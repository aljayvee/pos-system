import './bootstrap';
import { createApp } from 'vue';

const app = createApp({
    data() {
        return {
            // Initialize based on window width
            sidebarOpen: window.innerWidth >= 992,
            isMobile: window.innerWidth < 992,
            notifOpen: false
        }
    },
    methods: {
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
        handleResize() {
            const wasMobile = this.isMobile;
            this.isMobile = window.innerWidth < 992;

            // Only auto-adjust if crossing the breakpoint
            if (wasMobile !== this.isMobile) {
                this.sidebarOpen = !this.isMobile;
            }
        }
    },
    mounted() {
        // Listen for window resize
        window.addEventListener('resize', this.handleResize);
    }
});

// Custom Directive for clicking outside elements (like the notification dropdown)
app.directive('click-outside', {
    mounted(el, binding) {
        el.clickOutsideEvent = function(event) {
            // Check if the click was outside the element and its children
            if (!(el === event.target || el.contains(event.target))) {
                // Invoke the method passed to the directive
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