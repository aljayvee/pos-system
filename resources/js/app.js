import './bootstrap';
import { createApp } from 'vue';

// Import the new component
import SidebarLayout from './components/SidebarLayout.vue';

const app = createApp({
    // We can leave the root data empty now, as the component handles it
});

// Register the component
app.component('sidebar-layout', SidebarLayout);

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

app.mount('#app');