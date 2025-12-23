import './bootstrap';
import { createApp } from 'vue';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// CHANGED: Import AdminLayout instead of SidebarLayout to match your Blade template
import AdminLayout from './components/AdminLayout.vue';
import StatsCard from './components/StatsCard.vue'; // <--- ADD THIS

// Configure NProgress for premium feel
NProgress.configure({ 
    minimum: 0.3, 
    easing: 'ease', 
    speed: 500, 
    showSpinner: false, // Keep it clean
    trickleSpeed: 200 
});

// Global smooth transition listeners
document.addEventListener('click', e => {
    const link = e.target.closest('a');
    if (link && link.href && link.href.startsWith(window.location.origin) && !link.target && !e.ctrlKey && !e.metaKey && link.getAttribute('href') !== '#') {
        NProgress.start();
    }
});
document.addEventListener('submit', e => {
    if (!e.target.target) NProgress.start();
});
window.addEventListener('pageshow', () => NProgress.done());

const app = createApp({});

// Register the click-outside directive (required for the notification dropdown in AdminLayout)
app.directive('click-outside', {
    mounted(el, binding) {
        el.clickOutsideEvent = function(event) {
            // Check that click was outside the el and its children
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

// CHANGED: Register the component as 'admin-layout' so <admin-layout> works in Blade
app.component('admin-layout', AdminLayout);
app.component('stats-card', StatsCard); // <--- ADD THIS
app.mount('#app');