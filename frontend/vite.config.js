import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import svgLoader from 'vite-svg-loader';

export default defineConfig({
    plugins: [vue(), svgLoader()],
    alias: {
      '@': '/src',
    },
    server: {
      host: '0.0.0.0',  // Позволяет работать в Docker
      port: 5173,
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
      proxy: {
        '/api': {
          target: 'http://localhost:8080',
          changeOrigin: true,
          rewrite: (path) => path.replace(/^\/api/, ''),
        },
    },
});