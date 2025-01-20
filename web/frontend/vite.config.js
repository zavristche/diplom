import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import svgLoader from 'vite-svg-loader';

export default defineConfig({
    plugins: [vue(), svgLoader()],
    alias: {
        '@': '/src',
      },
    server: {
        css: {
            preprocessorOptions: {
              scss: {
                additionalData: `@use "./src/assets/styles/_variables.scss" as *;`,
                test: /\.scss$/,
                use: ["style-loader", "css-loader", "sass-loader"],
                // isCustomElement: (tag) => ['container'].includes(tag),
              },
            },
        },
        // proxy: {
        //     '/api': {
        //         target: 'http://localhost:8080', // Адрес Yii2
        //         changeOrigin: true,
        //         rewrite: (path) => path.replace(/^\/api/, '')
        //     }
        // }
    },
});