import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router.js';
import apiClient, { setupAuthInterceptor } from './api/apiClient';
import { useSearchStore } from './stores/search';
import { useCollectionStore } from './stores/collection';
import { useProfileStore } from './stores/profile';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
app.use(router);

const searchStore = useSearchStore();
const collectionStore = useCollectionStore();
const profileStore = useProfileStore();
const authStore = useAuthStore();

// Настраиваем интерцептор для apiClient с authStore
setupAuthInterceptor(authStore);

// Загружаем данные пользователя и другие предзагрузки
Promise.allSettled([
  authStore.loadUser(),
  searchStore.fetchSearchData(),
  collectionStore.fetchCreateData(),
  authStore.isAuthenticated && authStore.user?.id
    ? profileStore.fetchProfileById(authStore.user.id)
    : Promise.resolve(),
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