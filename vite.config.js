import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/premium-ui.css', 'resources/js/cashier.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            outDir: 'public/build', // Changed from default public to public/build to mimic laravel build flow? No, actually default is fine but we need to ensure it's accessible.
            // Actually, VitePWA usually outputs to dist. In Laravel with vite-plugin, it goes to public/build usually? 
            // Let's stick to default and see. The crucial part is 'base'.
            manifest: {
                name: 'POS System',
                short_name: 'POS',
                theme_color: '#0d6efd',
                background_color: '#ffffff',
                display: 'standalone',
                scope: '/',
                start_url: '/',
                icons: [
                    {
                        src: '/logo.png', // We need to check if logo exists, assuming standard
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/logo.png',
                        sizes: '512x512',
                        type: 'image/png'
                    }
                ]
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                maximumFileSizeToCacheInBytes: 5000000, // 5MB
            }
        }),
    ],
    resolve: {
        alias: {
            // CRITICAL: This allows Vue to compile HTML found in your Blade files
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        host: '0.0.0.0', // Listen on all network addresses
        hmr: {
            host: '192.168.0.144' // <--- REPLACE THIS with your computer's IP address
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});