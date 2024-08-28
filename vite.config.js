import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import collectModuleAssetsPaths from './vite-module-loader.js';

const allPaths = await collectModuleAssetsPaths(paths, 'Modules');

async function getConfig() {
    const paths = ['resources/css/app.css', 'resources/js/app.js'];
    const allPaths = await collectModuleAssetsPaths(paths, 'Modules');

    return defineConfig({
        server: {
            host: '127.0.0.1', // Add this to force IPv4 only
        },
        plugins: [
            laravel({
                input: allPaths,
                refresh: true,
            }),
        ],
    });
}

export default getConfig();
