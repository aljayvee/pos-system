<template>
  <div class="card h-100 border-0 shadow-sm card-hover">
    <div class="card-body p-3 d-flex align-items-center justify-content-between">
      
      <div>
        <h6 class="text-uppercase fw-bold text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
          {{ title }}
        </h6>
        <h2 class="fw-bold mb-0 text-dark">{{ value }}</h2>
        <small class="text-muted" style="font-size: 0.75rem;">
          {{ subtitle }}
        </small>
      </div>

      <div 
        class="icon-box rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm"
        :class="bgClass"
        style="width: 48px; height: 48px; font-size: 1.25rem;"
      >
        <i :class="icon"></i>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  props: {
    title: { type: String, required: true },
    value: { type: String, required: true }, // Passed as formatted string (e.g. "P 1,500.00")
    subtitle: { type: String, default: '' },
    icon: { type: String, default: 'fas fa-chart-bar' },
    color: { type: String, default: 'primary' } // primary, success, warning, etc.
  },
  computed: {
    bgClass() {
      return `bg-${this.color}`;
    }
  }
};
</script>

<style scoped>
.card-hover {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease;
}
.card-hover:hover {
  transform: translateY(-3px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}
</style>