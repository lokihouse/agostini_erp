import { defineConfig } from 'vite';
import laravel, {refreshPaths} from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        hmr: {
            host: '192.168.2.21'
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/app/theme.css'
            ],
            refresh: [
                ...refreshPaths,
                'app/Livewire/**'
            ],
        }),
    ],
});
