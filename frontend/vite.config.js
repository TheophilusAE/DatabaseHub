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
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        proxy: {
            '/tables': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/multi-import': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/multi-export': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/simple-multi': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/users': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/data': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/documents': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/upload': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/download': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/databases': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/discovery': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/joins': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/unified': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/permissions': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/auth': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/health': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
        },
    },
});
