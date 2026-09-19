import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/guest.css',
                'resources/css/filament/theme.css',
                'resources/js/app.js',
                'resources/js/signature-pad.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
