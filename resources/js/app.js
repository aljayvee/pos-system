import './bootstrap';
import { createApp } from 'vue';

// CHANGED: Import AdminLayout instead of SidebarLayout to match your Blade template
import AdminLayout from './components/AdminLayout.vue';
import StatsCard from './components/StatsCard.vue'; // <--- ADD THIS
import PosInterface from './components/cashier/PosInterface.vue'; // <--- ADD THIS

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
app.component('pos-interface', PosInterface); // <--- ADD THIS
app.mount('#app');