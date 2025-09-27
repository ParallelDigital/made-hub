import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            buildDirectory: 'build',
        }),
    ],
    build: {
        // Enable minification and optimization
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
            },
        },
        // Enable CSS code splitting
        cssCodeSplit: true,
        // Optimize chunk size
        rollupOptions: {
            output: {
                manualChunks: {
                    'welcome': ['resources/js/welcome-scripts.js'],
                    'welcome-styles': ['resources/css/welcome-styles.css']
                }
            }
        }
    },
    // Enable CSS preprocessing optimizations
    css: {
        devSourcemap: true,
    }
});
