// Theme Manager
const ThemeManager = {
    getContext() {
        // Allow explicit context override via meta tag
        const meta = document.querySelector('meta[name="theme-context"]');
        if (meta) return meta.content;

        // Determine context based on URL
        const path = window.location.pathname;
        if (path.includes('/admin')) return 'admin';
        if (path.includes('/cashier')) return 'cashier';
        return 'default';
    },

    getThemeKey() {
        return 'theme_' + this.getContext();
    },

    getTheme() {
        const key = this.getThemeKey();
        return localStorage.getItem(key) || 'system';
    },

    setTheme(theme) {
        const key = this.getThemeKey();
        localStorage.setItem(key, theme);
        this.applyTheme();

        // Dispatch event for UI updates (scoped to current context effectively)
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme, context: this.getContext() } }));
    },

    applyTheme() {
        const theme = this.getTheme();
        const html = document.documentElement;

        if (theme === 'dark') {
            html.classList.add('dark');
        } else if (theme === 'light') {
            html.classList.remove('dark');
        } else {
            // System
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
        }
    },

    init() {
        // Migration: If legacy 'theme' exists and new keys don't, migrate it
        const legacyTheme = localStorage.getItem('theme');
        if (legacyTheme) {
            if (!localStorage.getItem('theme_admin')) localStorage.setItem('theme_admin', legacyTheme);
            if (!localStorage.getItem('theme_cashier')) localStorage.setItem('theme_cashier', legacyTheme);
            localStorage.removeItem('theme'); // Cleanup
        }

        this.applyTheme();

        // Listen for system changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (this.getTheme() === 'system') {
                this.applyTheme();
            }
        });

        // Listen for storage changes (cross-tab sync for SAME context)
        window.addEventListener('storage', (e) => {
            if (e.key === this.getThemeKey()) {
                this.applyTheme();
            }
        });
    }
};

// Expose globally for inline onclick handlers
window.ThemeManager = ThemeManager;
ThemeManager.init();

export default ThemeManager;
