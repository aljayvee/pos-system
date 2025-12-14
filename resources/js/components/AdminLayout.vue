<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';

export default {
    setup() {
        const isDesktop = ref(window.innerWidth >= 768);
        const sidebarOpen = ref(true); // Default open on desktop

        const toggleSidebar = () => {
            sidebarOpen.value = !sidebarOpen.value;
            updateBodyClasses();
        };

        const updateBodyClasses = () => {
            if (isDesktop.value) {
                document.body.classList.toggle('sidebar-closed', !sidebarOpen.value);
                document.body.classList.remove('sidebar-open');
            } else {
                document.body.classList.toggle('sidebar-open', sidebarOpen.value);
                document.body.classList.remove('sidebar-closed');
            }
        };

        const handleResize = () => {
            isDesktop.value = window.innerWidth >= 768;
            if (isDesktop.value) sidebarOpen.value = true;
            else sidebarOpen.value = false;
            updateBodyClasses();
        };

        onMounted(() => window.addEventListener('resize', handleResize));
        onUnmounted(() => window.removeEventListener('resize', handleResize));

        return {
            isDesktop,
            sidebarOpen,
            toggleSidebar,
            // Computed properties for template logic
            isMobileOpen: computed(() => !isDesktop.value && sidebarOpen.value),
            isDesktopClosed: computed(() => isDesktop.value && !sidebarOpen.value),
            showMobileToggle: computed(() => !isDesktop.value || !sidebarOpen.value)
        };
    }
}
</script>