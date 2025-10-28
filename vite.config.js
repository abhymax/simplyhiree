import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import basicSsl from '@vitejs/plugin-basic-ssl'; // <-- 1. IMPORT THE PLUGIN

export default defineConfig({
    // --- UPDATED CONFIGURATION ---
    server: {
        host: '0.0.0.0',
        https: true, // <-- 2. ENABLE HTTPS
        hmr: {
            host: 'simplyhiree.massivedynamics.net.in',
        },
    },
    // --- END UPDATED CONFIGURATION ---

    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        basicSsl(), // <-- 3. USE THE PLUGIN
    ],
});