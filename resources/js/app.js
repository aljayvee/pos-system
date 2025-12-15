import './bootstrap';
import { createApp } from 'vue';
import SidebarLayout from './components/SidebarLayout.vue'; // <--- IMPORT THIS

const app = createApp({
    // We can leave the root data empty now, as the component handles it
});



// Register the click-outside directive (needed for notifications)
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

// Register the component so Blade can use it
app.component('sidebar-layout', SidebarLayout);
app.mount('#app');