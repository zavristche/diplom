import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    server: {
        css: {
            preprocessorOptions: {
              scss: {
                additionalData: `@use "./src/assets/styles/_variables.scss" as *;`,
              },
            },
        },
        proxy: {
            '/api': {
                target: 'http://localhost:8080', // Адрес Yii2
                changeOrigin: true,
                rewrite: (path) => path.replace(/^\/api/, '')
            }
        }
    },
});