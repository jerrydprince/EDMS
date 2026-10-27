import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
       // tailwindcss(),
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('pdfjs-dist') || id.includes('pdf-lib') || id.includes('jspdf') || id.includes('html2canvas')) {
                            return 'pdf-vendor';
                        }
                        if (id.includes('apexcharts') || id.includes('chart.js') || id.includes('vue-chartjs') || id.includes('vue3-apexcharts') || id.includes('d3')) {
                            return 'chart-vendor';
                        }
                        if (id.includes('quill') || id.includes('emoji-picker')) {
                            return 'editor-vendor';
                        }
                        if (id.includes('primevue') || id.includes('primeicons')) {
                            return 'primevue';
                        }
                        if (id.includes('vue') || id.includes('@inertiajs') || id.includes('@vueuse')) {
                            return 'vue-vendor';
                        }
                        if (id.includes('lodash')) {
                            return 'lodash';
                        }
                        return 'vendor';
                    }
                }
            }
        }
    }
});