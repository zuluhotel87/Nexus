import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'nexus.test',
        },
        https: {
            key: fs.readFileSync(`.certs/host.key`),
            cert: fs.readFileSync(`.certs/host.crt`),
        },
    },
});
