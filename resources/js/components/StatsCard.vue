<template>
  <div class="card h-100 border-0 shadow-sm stats-card overflow-hidden position-relative">
    <div class="card-body p-4 d-flex align-items-center justify-content-between position-relative z-1">
      
      <div>
        <h6 class="text-uppercase fw-bold text-secondary mb-2 tracking-wide" style="font-size: 0.7rem;">
          {{ title }}
        </h6>
        <h2 class="fw-bold mb-1 ml-1 text-dark tracking-tight">{{ value }}</h2>
        <div class="d-flex align-items-center mt-2">
           <span class="badge bg-light text-secondary rounded-pill fw-normal px-2 py-1" style="font-size: 0.7rem;">
              {{ subtitle }}
           </span>
        </div>
      </div>

      <div 
        class="icon-box rounded-4 d-flex align-items-center justify-content-center text-white shadow"
        :class="gradientClass"
        style="width: 56px; height: 56px; font-size: 1.5rem;"
      >
        <i :class="icon"></i>
      </div>

    </div>
    
    <!-- Background Decoration -->
    <div class="position-absolute end-0 bottom-0 opacity-10" style="font-size: 8rem; line-height: 0; transform: translate(20%, 20%);">
        <i :class="[icon, textClass]"></i>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    title: { type: String, required: true },
    value: { type: String, required: true },
    subtitle: { type: String, default: '' },
    icon: { type: String, default: 'fas fa-chart-bar' },
    color: { type: String, default: 'primary' } 
  },
  computed: {
    gradientClass() {
      // Premium Gradients
      const map = {
        primary: 'bg-gradient-primary',
        success: 'bg-gradient-success',
        info:    'bg-gradient-info',
        warning: 'bg-gradient-warning',
        danger:  'bg-gradient-danger',
        dark:    'bg-gradient-dark'
      };
      return map[this.color] || 'bg-primary';
    },
    textClass() {
        return `text-${this.color}`;
    }
  }
};
</script>

<style scoped>
.stats-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: white;
  border-radius: 1rem !important; /* Rounded-4 equivalent */
}
.stats-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1), 0 5px 10px -3px rgba(0, 0, 0, 0.04) !important;
}
.tracking-wide { letter-spacing: 0.05em; }
.tracking-tight { letter-spacing: -0.025em; }

/* Custom Gradients if not in Bootstrap */
.bg-gradient-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.bg-gradient-info    { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
.bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.bg-gradient-danger  { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.bg-gradient-dark    { background: linear-gradient(135deg, #374151 0%, #1f2937 100%); }
</style>