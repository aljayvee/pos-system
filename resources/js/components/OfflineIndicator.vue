<template>
  <Transition name="slide-up">
    <div v-if="offline" class="offline-indicator shadow-lg rounded-pill px-3 py-2 d-flex align-items-center gap-2">
      <div class="spinner-grow spinner-grow-sm text-danger" role="status"></div>
      <span class="fw-bold text-dark small">You are currently offline.</span>
      <button @click="refresh" v-if="showRetry" class="btn btn-sm btn-link text-decoration-none p-0 ms-1 fw-bold text-primary" style="font-size: 0.8rem">Retry</button>
    </div>
  </Transition>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';

export default {
  setup() {
    const offline = ref(!navigator.onLine);
    const showRetry = ref(false);

    const updateStatus = () => {
      offline.value = !navigator.onLine;
      if (offline.value) {
        // Show retry button after a few seconds of being offline
        setTimeout(() => showRetry.value = true, 5000);
      } else {
        showRetry.value = false;
      }
    };

    const refresh = () => {
        window.location.reload();
    };

    onMounted(() => {
      window.addEventListener('online', updateStatus);
      window.addEventListener('offline', updateStatus);
    });

    onUnmounted(() => {
      window.removeEventListener('online', updateStatus);
      window.removeEventListener('offline', updateStatus);
    });

    return { offline, showRetry, refresh };
  }
};
</script>

<style scoped>
.offline-indicator {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(0,0,0,0.1);
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease-out;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translate(-50%, 100%);
  opacity: 0;
}
</style>
