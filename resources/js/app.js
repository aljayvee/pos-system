import './bootstrap';
import { createApp } from 'vue';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// CHANGED: Import AdminLayout instead of SidebarLayout to match your Blade template
import './webauthn.js';
import AdminLayout from './components/AdminLayout.vue';
import StatsCard from './components/StatsCard.vue';
import ToastManager from './components/ToastManager.vue';
import SwipeItem from './components/SwipeItem.vue';
import OfflineIndicator from './components/OfflineIndicator.vue';

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
        el.clickOutsideEvent = function (event) {
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
app.component('stats-card', StatsCard);
app.component('toast-manager', ToastManager);
app.component('swipe-item', SwipeItem);
app.component('offline-indicator', OfflineIndicator);

// Global $toast helper
app.config.globalProperties.$toast = {
    show(message, type = 'success', title = null) {
        window.dispatchEvent(new CustomEvent('toast-show', {
            detail: { message, type, title }
        }));
    },
    success(message, title) { this.show(message, 'success', title); },
    error(message, title) { this.show(message, 'error', title); },
    warning(message, title) { this.show(message, 'warning', title); },
    info(message, title) { this.show(message, 'info', title); }
};

// Global Error Handler
app.config.errorHandler = (err, instance, info) => {
    console.error('Global Vue Error:', err);
    console.error('Info:', info);

    // Filter out minor errors or handle specific types
    if (err.message && err.message.includes('ResizeObserver')) return; // Ignore harmless resize errors

    app.config.globalProperties.$toast.error(
        'An unexpected error occurred. Please try again.',
        'System Error'
    );
};

app.mount('#app');

// Check for Laravel Flash Messages after mount
// We use a small timeout to ensure the event listener in ToastManager is ready
setTimeout(() => {
    if (window.laravel_flash) {
        if (window.laravel_flash.success) app.config.globalProperties.$toast.success(window.laravel_flash.success);
        if (window.laravel_flash.error) app.config.globalProperties.$toast.error(window.laravel_flash.error);
        if (window.laravel_flash.warning) app.config.globalProperties.$toast.warning(window.laravel_flash.warning);
        if (window.laravel_flash.info) app.config.globalProperties.$toast.info(window.laravel_flash.info);
    }
}, 100);