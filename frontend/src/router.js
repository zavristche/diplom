import { createRouter, createWebHistory } from 'vue-router';
import Home from './views/Home.vue';
import About from './views/About.vue';
import View from './views/recipe/View.vue';
import RecipeService from './api/RecipeService'; // Исправленный путь

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: {
      title: 'Рецептище',
    },
  },
  {
    path: '/about',
    name: 'about',
    component: About,
  },
  {
    path: '/recipe/:id',
    name: 'recipe',
    component: View,
    beforeEnter: async (to, from, next) => {
      try {
        const response = await RecipeService.getById(to.params.id);
        to.meta.recipe = response.data; // Сохраняем данные в meta
        next();
      } catch (error) {
        console.error('Error fetching recipe:', error);
        next({ name: 'not-found' }); // Перенаправляем на страницу ошибки
      }
    },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    // component: () => import('./views/NotFound.vue'), // Ленивая загрузка
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.afterEach((to) => {
  document.title = to.meta.title || 'Рецептище';
});

export default router;