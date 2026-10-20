import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwind from '@tailwindcss/vite';
import inertia from '@inertiajs/vite';
export default defineConfig({
    plugins: [
        laravel({ input: ['resources/js/app.ts'], refresh: true }),
        vue(),
        tailwind(),
        inertia({ ssr: { host: '127.0.0.1', port: 13714 } }),
    ],
});
