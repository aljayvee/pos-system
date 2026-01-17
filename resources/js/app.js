import './bootstrap';
import { createApp } from 'vue';


// CHANGED: Import AdminLayout instead of SidebarLayout to match your Blade template
import Swal from 'sweetalert2';
window.Swal = Swal;

import './webauthn.js';
import AdminLayout from './components/AdminLayout.vue';
import StatsCard from './components/StatsCard.vue';
import ToastManager from './components/ToastManager.vue';
import SwipeItem from './components/SwipeItem.vue';
import OfflineIndicator from './components/OfflineIndicator.vue';
import ThemeManager from './theme';

// Initialize Theme
ThemeManager.init();
// window.ThemeManager is now handled inside theme.js




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

// --- Global Mobile Modal Swipe Logic ---
document.addEventListener('DOMContentLoaded', () => {
    let startY = 0;
    let currentY = 0;
    let isDragging = false;
    let activeModal = null;
    let activeContent = null;

    document.addEventListener('touchstart', (e) => {
        const modal = e.target.closest('.modal-bottom-sheet.show');
        if (!modal) return;

        const content = modal.querySelector('.modal-content');
        if (!content || !content.contains(e.target)) return;

        // Ensure we are at the top (don't interfere with internal scrolling)
        if (content.scrollTop > 0) return;

        activeModal = modal;
        activeContent = content;
        startY = e.touches[0].clientY;
        isDragging = true;
        activeContent.style.transition = 'none'; // Disable transition for direct 1:1 movement
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (!isDragging || !activeContent) return;

        currentY = e.touches[0].clientY;
        let diff = currentY - startY;

        // Only allow dragging DOWN
        if (diff > 0) {
            // Prevent default page scroll ONLY if we are firmly dragging the modal
            if (e.cancelable && diff > 10) e.preventDefault();
            activeContent.style.transform = `translateY(${diff}px)`;
        }
    }, { passive: false }); // Passive false allows preventDefault

    document.addEventListener('touchend', (e) => {
        if (!isDragging || !activeContent) return;

        const diff = currentY - startY;
        isDragging = false;
        activeContent.style.transition = 'transform 0.3s ease-out'; // Restore smooth animation

        if (diff > 120) { // Threshold to close
            // Close Modal
            const modalInstance = bootstrap.Modal.getInstance(activeModal);
            if (modalInstance) modalInstance.hide();
        } else {
            // Snap back
            activeContent.style.transform = '';
        }

        activeModal = null;
        activeContent = null;
    });

    // Reset transform on hidden to ensure clean state next time
    document.addEventListener('hidden.bs.modal', (e) => {
        if (e.target.classList.contains('modal-bottom-sheet')) {
            const content = e.target.querySelector('.modal-content');
            if (content) {
                content.style.transform = '';
                content.style.transition = '';
            }
        }
    });
});