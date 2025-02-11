const { defineConfig } = require('@vue/cli-service');
const webpack = require('webpack');

module.exports = defineConfig({
  devServer: {
    host: '0.0.0.0', // Доступ из контейнера
    port: 8282,
    watchFiles: ['src/**/*'], // Следим за изменениями
    hot: true, // Включаем HMR (Hot Module Replacement)
    liveReload: true, // Принудительно перезапускать страницу при изменениях
    client: {
      overlay: true, // Показывать ошибки в браузере
    },
  },
  configureWebpack: {
    plugins: [
      new webpack.DefinePlugin({
        // Определяем feature flags для Vue
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false), // Отключаем детали hydration mismatch
        __VUE_OPTIONS_API__: JSON.stringify(true), // Включаем поддержку Options API
        __VUE_PROD_DEVTOOLS__: JSON.stringify(false), // Отключаем Vue Devtools в production
      }),
    ],
  },
});