// src/main.js
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router.js'; // Явно указываем расширение
import apiClient, { setupAuthInterceptor } from './api/apiClient';
import { useRecipeStore } from './stores/recipe';
import { useSearchStore } from './stores/search';
import { useCollectionStore } from './stores/collection';
import { useProfileStore } from './stores/profile';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
app.use(router);

const recipeStore = useRecipeStore();
const searchStore = useSearchStore();
const collectionStore = useCollectionStore();
const profileStore = useProfileStore();
const authStore = useAuthStore();

// Настраиваем интерцептор для apiClient с authStore
setupAuthInterceptor(authStore);

// Загружаем данные пользователя из локального хранилища
authStore.loadUser();

// Предварительная загрузка данных для часто используемых страниц
Promise.allSettled([
  recipeStore.fetchRecipes(), // Для Home
  recipeStore.fetchCreateData(), // Для /recipe/create
  searchStore.fetchSearchData(), // Для /search
  collectionStore.fetchCreateData(), // Для /collection/create
  authStore.isAuthenticated && authStore.user?.id
    ? profileStore.fetchProfileById(authStore.user.id)
    : Promise.resolve(), // Для профиля текущего пользователя, если авторизован
])
  .then(() => {
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