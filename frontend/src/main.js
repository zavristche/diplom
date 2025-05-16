import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router.js';
import apiClient, { setupAuthInterceptor } from './api/apiClient';
import { useSearchStore } from './stores/search';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
app.use(router);

const searchStore = useSearchStore();
const authStore = useAuthStore();

// Настраиваем интерцептор для apiClient с authStore
setupAuthInterceptor(authStore);

// Загружаем только данные пользователя и поиск
Promise.allSettled([
  authStore.loadUser(),
  searchStore.fetchSearchData(),
])
  .then((results) => {
    console.log('Preload results:', results);
    router.isReady().then(() => {
      app.mount('#app');
    });
  })
  .catch((error) => {
    console.error('Error during preloading data:', error);
    router.isReady().then(() => {
      app.mount('#app');
    });
  });