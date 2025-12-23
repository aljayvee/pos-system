<template>
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
     <transition-group name="toast-list">
        <Toast 
            v-for="toast in toasts" 
            :key="toast.id" 
            v-bind="toast"
            @close="removeToast(toast.id)"
        />
     </transition-group>
  </div>
</template>

<script>
import Toast from './Toast.vue';

export default {
  components: { Toast },
  data() {
    return {
      toasts: []
    };
  },
  created() {
    // Listen to the global event bus
    // In Vue 3, we usually use a tiny emitter library (mitt) or provide/inject.
    // However, since we are likely integrating into an existing app, let's attach to window for simplicity
    // or rely on the custom event we will dispatch from app.js
    window.addEventListener('toast-show', this.handleToastEvent);
  },
  unmounted() {
    window.removeEventListener('toast-show', this.handleToastEvent);
  },
  methods: {
    handleToastEvent(e) {
        const { message, type, title, duration } = e.detail;
        this.addToast({ message, type, title, duration });
    },
    addToast({ message, type = 'success', title, duration = 4000 }) {
       const id = Date.now().toString(36) + Math.random().toString(36).substr(2);
       
       // Set default titles based on type if not provided
       if(!title) {
           const titles = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' };
           title = titles[type] || 'Notification';
       }

       this.toasts.push({ id, message, type, title, duration });
       
       // Limit max toasts to avoid clutter
       if (this.toasts.length > 5) {
           this.toasts.shift();
       }
    },
    removeToast(id) {
       this.toasts = this.toasts.filter(t => t.id !== id);
    }
  }
};
</script>

<style scoped>
.toast-list-enter-active,
.toast-list-leave-active {
  transition: all 0.4s ease;
}
.toast-list-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.toast-list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
.toast-list-move {
  transition: transform 0.4s ease;
}
</style>
