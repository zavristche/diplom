import { createApp } from 'vue';
import { createPinia } from 'pinia';
import apiClient, { setupAuthInterceptor } from './api/apiClient';
import { useAuthStore } from './stores/auth';
import App from './App.vue';
import router from './router';

const app = createApp(App);
const pinia = createPinia();
app.use(router);
app.use(pinia);

const authStore = useAuthStore();
authStore.loadUser();

setupAuthInterceptor(authStore);

router.beforeEach((to, from, next) => {
  if (to.meta.title) {
    document.title = to.meta.title;
  } else {
    document.title = "Рецептище";
  }
  next();
});

router.isReady().then(() => {
  app.mount('#app');
});


