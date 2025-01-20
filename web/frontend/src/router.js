import { createRouter, createWebHistory } from 'vue-router';
import Home from './views/Home.vue';
import About from './views/About.vue';
import View  from './views/recipe/View.vue';


const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: {
      title: 'Рецептище'
    }
  },
  {
    path: '/about',
    name: 'about',
    component: About
  },
  {
    path: '/recipe/:id',
    name: 'recipe',
    component: View,
    meta: {
      title: 'Рецепт'
    }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;