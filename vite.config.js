import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fullReload from 'vite-plugin-full-reload';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        // Full reload for Blade and PHP files (helps when HMR doesn't reload views)
        fullReload(['resources/views/**/*.blade.php', 'routes/**/*.php']),
    ],
    server: {
        // Enable polling for file changes on environments (Windows, Docker) where watchers are unreliable
        watch: {
            usePolling: true,
            interval: 100,
        },
        // Keep the overlay enabled so build errors are visible in the browser during dev
        hmr: {
            overlay: true,
        },
    },
    build: {
        // Production optimizations
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.logs in production
                drop_debugger: true,
            },
        },
        rollupOptions: {
            output: {
                // Note: alpinejs is managed by Livewire 3, no manual chunk needed
                manualChunks: {
                    'swiper': ['swiper'],
                },
                // Avoid _ prefix on chunk filenames - Apache may block _ prefixed files
                chunkFileNames: 'assets/[name]-[hash].js',
            },
        },
        cssMinify: true,
        reportCompressedSize: false, // Faster builds
        chunkSizeWarningLimit: 1000,
    },
});
