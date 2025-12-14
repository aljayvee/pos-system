import './bootstrap';
import { createApp } from 'vue';

const app = createApp({
    data() {
        return {
            // CHANGED: Breakpoint increased to 992px to support Phablets/Tablets as "Mobile"
            sidebarOpen: window.innerWidth >= 992, 
            isMobile: window.innerWidth < 992,
            notifOpen: false
        }
    },
    mounted() {
        window.addEventListener('resize', () => {
            // CHANGED: Update check to 992px
            this.isMobile = window.innerWidth < 992;
            
            // Auto-adjust state based on new screen size
            if (!this.isMobile) {
                this.sidebarOpen = true; // Desktop: Default Open
            } else {
                this.sidebarOpen = false; // Mobile/Tablet: Default Closed
            }
        });
    }
});

app.mount('#app');