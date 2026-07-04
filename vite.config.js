import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => ({
    build: {
        minify: mode === 'production',
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/company/invoiceplane.css',
                'resources/css/filament/company/invoiceplane-blue.css',
                'resources/css/filament/company/nord.css',
                'resources/css/filament/company/orange.css',
                'resources/css/filament/company/reddit.css'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
}));
