<template>
  <div 
    class="swipe-container position-relative overflow-hidden mb-2 rounded-4"
    @touchstart="onTouchStart"
    @touchmove="onTouchMove"
    @touchend="onTouchEnd"
  >
    <!-- Background Actions -->
    <div class="swipe-actions position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-between align-items-center px-3"
         :style="{ backgroundColor: bgDetails.color, opacity: bgDetails.opacity }"
    >
      <!-- Left Action (Delete - Swipe Right to Left) -->
      <div v-if="x < 0" class="action-right ms-auto text-white fw-bold d-flex align-items-center">
        <span class="me-2">Delete</span> <i class="fas fa-trash"></i>
      </div>

      <!-- Right Action (Edit - Swipe Left to Right) -->
      <div v-if="x > 0" class="action-left text-white fw-bold d-flex align-items-center">
         <i class="fas fa-edit me-2"></i> <span>Edit</span>
      </div>
    </div>

    <!-- Foreground Content -->
    <div 
      class="swipe-content bg-white position-relative hover-lift shadow-sm border-0"
      :style="{ transform: `translateX(${x}px)` }"
    >
      <slot></slot>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
    props: {
        threshold: { type: Number, default: 80 },
        itemData: { type: Object, default: () => ({}) }
    },
    emits: ['edit', 'delete'],
    setup(props, { emit }) {
        const x = ref(0);
        const startX = ref(0);
        const isDragging = ref(false);

        const onTouchStart = (e) => {
            startX.value = e.touches[0].clientX;
            isDragging.value = true;
        };

        const onTouchMove = (e) => {
            if (!isDragging.value) return;
            const currentX = e.touches[0].clientX;
            const diff = currentX - startX.value;
            
            // Limit the drag range
            if (diff > 0 && diff < 150) x.value = diff; // Swipe Right (Edit)
            else if (diff < 0 && diff > -150) x.value = diff; // Swipe Left (Delete)
        };

        const onTouchEnd = () => {
            isDragging.value = false;
            
            if (x.value > props.threshold) {
                // Trigger Edit
                emit('edit', props.itemData);
            } else if (x.value < -props.threshold) {
                // Trigger Delete
                emit('delete', props.itemData);
            }

            // Reset
            x.value = 0;
        };

        const bgDetails = computed(() => {
            if (x.value > 20) return { color: '#0d6efd', opacity: Math.min(Math.abs(x.value) / 100, 1) }; // Blue
            if (x.value < -20) return { color: '#dc3545', opacity: Math.min(Math.abs(x.value) / 100, 1) }; // Red
            return { color: 'transparent', opacity: 0 };
        });

        return { x, onTouchStart, onTouchMove, onTouchEnd, bgDetails };
    }
};
</script>

<style scoped>
.swipe-content {
  transition: transform 0.2s ease-out;
  /* Ensure content covers background */
  z-index: 2; 
}
.swipe-actions {
  z-index: 1;
  transition: background-color 0.2s;
}
</style>
