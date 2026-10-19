import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        // pdf.worker y docx-preview son librerías externas que no se pueden
        // dividir sin fragmentar el propio paquete.
        chunkSizeWarningLimit: 1100,
        rollupOptions: {
            onwarn(warning, warn) {
                // pdfjs-dist incluye un build con `eval`; es una dependencia de
                // terceros y no se puede modificar desde el proyecto.
                if (warning.code === 'EVAL' && warning.id?.includes('pdfjs-dist')) return;
                warn(warning);
            },
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
});
