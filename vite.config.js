import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/js/app.js'], refresh: true }),
        vue(),
        inertia(),
        tailwindcss(),
    ],
    server: { host: '127.0.0.1' },
});
