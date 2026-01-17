<template>
  <div class="stats-card-wrapper h-100">
      <div 
        class="card h-100 border-0 shadow-sm stats-card overflow-hidden position-relative"
        :class="color"
      >
        <!-- Glass Sheen (Optimized) -->
        <div class="glass-sheen position-absolute top-0 start-0 w-100 h-100 pe-none"></div>

        <div class="card-body p-4 d-flex align-items-center justify-content-between position-relative z-1">
          
          <div class="d-flex flex-column justify-content-center">
            <h6 class="text-uppercase fw-bold text-secondary mb-2 tracking-wide opacity-75" style="font-size: 0.75rem;">
              {{ title }}
            </h6>
            <h2 class="fw-bolder mb-1 text-dark tracking-tight display-6">{{ value }}</h2>
            <div class="d-flex align-items-center mt-2">
               <span class="badge rounded-pill fw-medium px-2 py-1 bg-white bg-opacity-50 text-dark border border-white border-opacity-50 shadow-sm" style="font-size: 0.7rem;">
                  {{ subtitle }}
               </span>
            </div>
          </div>

          <!-- Floating Icon 3D -->
          <div 
            class="icon-box-3d rounded-4 d-flex align-items-center justify-content-center text-white shadow"
            :class="gradientClass"
            style="width: 64px; height: 64px; font-size: 1.75rem;"
          >
            <i :class="icon"></i>
          </div>

        </div>
        
        <!-- Background Decoration/Blob -->
        <div class="blob position-absolute end-0 bottom-0 opacity-10" :class="`bg-${color}`"></div>
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
      return map[this.color] || 'bg-gradient-primary';
    },
    textClass() {
        return `text-${this.color}`;
    }
  }
};
</script>

<style scoped>
.stats-card-wrapper {
    perspective: 1000px;
}

.stats-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  will-change: transform, box-shadow;
  background: rgba(255, 255, 255, 0.85); /* Increased opacity for less transparency calc */
  backdrop-filter: blur(10px); /* Reduced blur from 20px */
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.6);
  border-radius: 1.5rem !important;
  transform-style: preserve-3d;
}

.stats-card:hover {
  transform: translateY(-5px); /* Removed rotation for performance */
  box-shadow: 
    0 15px 30px -5px rgba(0, 0, 0, 0.1),
    0 8px 15px -5px rgba(0, 0, 0, 0.05) !important;
  border-color: rgba(255,255,255,1);
}

.icon-box-3d {
    transition: transform 0.3s ease;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    background-size: 200% 200%;
    /* Removed infinite animation which can be heavy */
}

.stats-card:hover .icon-box-3d {
    transform: scale(1.1); /* Simplified transform */
    box-shadow: 0 15px 25px rgba(0,0,0,0.15);
}

.glass-sheen {
    background: linear-gradient(120deg, rgba(255,255,255,0) 30%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 70%);
    background-size: 200% 100%;
    transition: background-position 0.6s ease;
    background-position: 100% 0;
}
.stats-card:hover .glass-sheen {
    background-position: -100% 0;
}

.blob {
    width: 120px; height: 120px;
    border-radius: 50%;
    filter: blur(30px); /* Reduced blob size and blur */
    z-index: 0;
    transform: translate(20%, 20%);
}

.tracking-wide { letter-spacing: 0.05em; }
.tracking-tight { letter-spacing: -0.025em; }

/* Custom Gradients */
.bg-gradient-primary { background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.bg-gradient-info    { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
.bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.bg-gradient-danger  { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.bg-gradient-dark    { background: linear-gradient(135deg, #1f2937 0%, #111827 100%); }
</style>