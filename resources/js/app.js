import './bootstrap';
import { createApp } from 'vue';

const app = createApp({
    data() {
        return {
            sidebarOpen: window.innerWidth >= 768,
            isMobile: window.innerWidth < 768,
            notifOpen: false
        }
    },
    mounted() {
        // Handle screen resizing
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth < 768;
            if (!this.isMobile) {
                this.sidebarOpen = true; // Always open on desktop
            } else {
                this.sidebarOpen = false; // Always closed on mobile
            }
        });
    }
});

app.mount('#app');