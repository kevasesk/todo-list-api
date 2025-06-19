import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/tasks.css',
                'resources/css/notification.css',

                'resources/js/auth.js',
                'resources/js/tasks.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
