import './bootstrap';
import { createApp } from 'vue';

import { createApp } from 'vue';
const app = createApp({
    data() {
        return {
            sidebarOpen: window.innerWidth >= 768, // Default open on desktop
            isMobile: window.innerWidth < 768,
            notifOpen: false
        }
    },
    mounted() {
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth < 768;
            // Auto-reset state on resize to avoid broken layouts
            if(!this.isMobile) this.sidebarOpen = true;
            else this.sidebarOpen = false;
        });
    }
});
app.mount('#app');