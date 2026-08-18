import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // SSR is deliberately deferred; this sprint builds the client only.
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
