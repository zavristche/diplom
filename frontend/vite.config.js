import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import svgLoader from 'vite-svg-loader';

export default defineConfig({
    plugins: [vue(), svgLoader()],
    alias: {
      '@': '/src',
    },
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      hmr: {
        protocol: 'ws',
        host: 'localhost'
      },
      watch: {
        usePolling: true,
        interval: 100
      },

      css: {
          preprocessorOptions: {
            scss: {
              additionalData: `@use "./src/assets/styles/_variables.scss" as *;`,
              test: /\.scss$/,
              use: ["style-loader", "css-loader", "sass-loader"],
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
    },
  optimizeDeps: {
    include: [
      'pinia',
      'pinia/dist/pinia.mjs'
    ],
    exclude: ['vue-demi']
  }
});