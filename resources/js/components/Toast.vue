<template>
  <div 
    class="toast-card d-flex align-items-center p-3 mb-3 bg-white rounded-4 shadow-lg border-0 position-relative animate__animated animate__fadeInRight animate__fast"
    :class="typeClass"
    role="alert" 
    aria-live="assertive" 
    aria-atomic="true"
    @mouseenter="pauseTimer"
    @mouseleave="resumeTimer"
  >
    <!-- Icon Wrapper -->
    <div class="toast-icon rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" :class="iconBgClass">
       <i :class="iconClass"></i>
    </div>

    <!-- Content -->
    <div class="toast-content flex-grow-1 pe-2">
       <h6 class="mb-0 fw-bold text-dark fs-6">{{ title }}</h6>
       <p class="mb-0 small text-secondary lh-sm mt-1">{{ message }}</p>
    </div>

    <!-- Close Button -->
    <button type="button" class="btn-close btn-sm ms-auto focus-ring" data-bs-dismiss="toast" aria-label="Close" @click="$emit('close')"></button>
    
    <!-- Progress Bar -->
    <div class="progress-bar-container position-absolute bottom-0 start-0 w-100 overflow-hidden rounded-bottom-4">
        <div class="progress-bar" :style="{ width: progress + '%', backgroundColor: progressColor }"></div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    id: { type: String, required: true },
    type: { type: String, default: 'success' }, // success, error, warning, info
    title: { type: String, default: 'Notification' },
    message: { type: String, required: true },
    duration: { type: Number, default: 4000 }
  },
  data() {
    return {
      progress: 100,
      timer: null,
      interval: null,
      startTime: null,
      remaining: this.duration
    };
  },
  computed: {
    typeClass() {
      // Premium left border accent
      return `border-start border-4 border-${this.bootstrapContext}`; 
    },
    iconBgClass() {
       return `bg-${this.bootstrapContext} bg-opacity-10 text-${this.bootstrapContext}`;
    },
    iconClass() {
      const icons = {
        success: 'fas fa-check',
        error: 'fas fa-times',
        warning: 'fas fa-exclamation',
        info: 'fas fa-info'
      };
      return icons[this.type] || icons.info;
    },
    bootstrapContext() {
      const map = {
        success: 'success',
        error: 'danger',
        warning: 'warning',
        info: 'primary'
      };
      return map[this.type] || 'primary';
    },
    progressColor() {
       const colors = {
           success: '#198754',
           error: '#dc3545',
           warning: '#ffc107',
           info: '#0d6efd'
       };
       return colors[this.type] || '#0d6efd';
    }
  },
  mounted() {
    this.startTimer();
  },
  beforeUnmount() {
    this.clearTimers();
  },
  methods: {
    startTimer() {
        const step = 20; // update every 10ms
        this.startTime = Date.now();
        
        this.timer = setTimeout(() => {
            this.$emit('close');
        }, this.remaining);

        this.interval = setInterval(() => {
             const elapsed = Date.now() - this.startTime;
             const total = this.duration; // Use original total duration for bar scaling relative to current session would be complex, simpler to just linear decrement
             // Better logic: standard decrement
             this.progress -= (100 / (this.duration / step));
             if(this.progress <= 0) this.progress = 0;
        }, step);
    },
    pauseTimer() {
         this.clearTimers();
         // simple pause: we don't calculate exact expected end, just stop.
         // For a perfect resume, we'd calculate remaining. 
         // Implementation Detail: For simplicity in this version, we just stop auto-close on hover.
    },
    resumeTimer() {
        // Reset only if we want to restart full cycle or continue.
        // Let's just restart the close timer for the original duration for simplicity, or 
        // essentially "extend" life.
        // Re-implementing simplified version:
        this.startTimer(); 
    },
    clearTimers() {
        clearTimeout(this.timer);
        clearInterval(this.interval);
    }
  }
};
</script>

<style scoped>
.toast-card {
    min-width: 320px;
    max-width: 380px;
    backdrop-filter: blur(10px); /* Glass effect support */
    /* Adds a subtle pop */
    transform: translateZ(0); 
    z-index: 1070;
}
.toast-icon {
    width: 32px;
    height: 32px;
    font-size: 0.9rem;
}
.progress-bar-container {
    height: 3px;
    background: transparent;
}
.progress-bar {
    height: 100%;
    transition: width 0.02s linear;
}
</style>
