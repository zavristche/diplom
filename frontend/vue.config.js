const { defineConfig } = require('@vue/cli-service');
const webpack = require('webpack');

module.exports = {
  devServer: {
    host: '0.0.0.0', // Обеспечивает доступ из контейнера Docker
    port: 8082, // Указываем порт
    hot: true, // Включаем горячую перезагрузку
    liveReload: true, // Включаем обновление в реальном времени
    watchFiles: ['src/**/*'], // Указываем, что нужно следить за файлами в src
    client: {
      webSocketURL: 'ws://localhost:8082/ws', // Явно указываем URL WebSocket для HMR
      overlay: false, // Отключаем отображение ошибок в браузере (по желанию)
    },
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'Origin, Content-Type, Accept, Authorization',
    },
    // proxy: {
    //   '/api': {
    //     target: 'http://localhost:8000', // Прокси для API, если нужно
    //     changeOrigin: true,
    //     secure: false,
    //   },
    // },
  },
};


// module.exports = defineConfig({
//   devServer: {
//     host: '0.0.0.0', // Доступ извне (из контейнера)
//     port: 8082,
//     watchFiles: ['src/**/*'], // ✅ Следим за изменениями в src/
//     hot: true, // Включаем горячую замену модулей (HMR)
//     liveReload: true, // Перезагрузка страницы при изменениях
//     client: {
//       overlay: false, // Показывать ошибки на странице
//       webSocketURL: 'ws://localhost:8082/ws', // Явное указание WebSocket
//     },
//   },
//   configureWebpack: {
//     plugins: [
//       new webpack.DefinePlugin({
//         __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false),
//         __VUE_OPTIONS_API__: JSON.stringify(true),
//         __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
//       }),
//     ],
//   },
// });
