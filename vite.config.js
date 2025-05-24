import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        ...(process.env?.VIRTUAL_HOST ? { host: "0.0.0.0" } : { host: "127.0.0.1" }),
        ...(process.env?.VIRTUAL_HOST ? { origin: `//${process.env.VIRTUAL_HOST}:8443` } : { }),
        port: 3000,
        strictPort: true,
        https: false,
        cors: true,
    },
});
