import { createRouter, createWebHistory } from 'vue-router';
import { useRecipeStore } from './stores/recipe';
import { useProfileStore } from './stores/profile';
import { useSearchStore } from './stores/search';
import { useCollectionStore } from './stores/collection';

import Home from './views/Home.vue';
import About from './views/About.vue';
import Search from './views/Search.vue';
import RecipeCreate from './views/recipe/Create.vue';
import RecipeEdit from './views/recipe/Edit.vue';
import CollectionCreate from './views/collection/Create.vue';
import CollectionView from './views/collection/View.vue';

const routes = [
  { path: '/', name: 'home', component: Home, meta: { title: 'Рецептище — Главная' } },
  {
    path: '/search/:type(recipe|collection|author)?',
    name: 'search',
    component: Search,
    meta: { title: 'Рецептище — Поиск' },
  },
  { path: '/about', name: 'about', component: About, meta: { title: 'Рецептище — О нас' } },
  {
    path: '/recipe/:id',
    name: 'RecipeView',
    component: () => import('./views/recipe/View.vue'),
    meta: { title: 'Рецептище — Рецепт' },
  },
  {
    path: '/profile/:id',
    name: 'ProfileView',
    component: () => import('./views/profile/View.vue'),
    meta: { title: 'Профиль' },
  },
  {
    path: '/recipe/create',
    name: 'recipe-create',
    component: RecipeCreate,
    meta: { title: 'Создать рецепт' },
    beforeEnter: async (to, from, next) => {
      const recipeStore = useRecipeStore();
      if (!recipeStore.createData) {
        await recipeStore.fetchCreateData();
      }
      to.meta.data = recipeStore.createData;
      if (!to.meta.data) {
        next('/');
      } else {
        next();
      }
    },
  },
  {
    path: '/recipe/edit/:id',
    name: 'recipe-edit',
    component: RecipeEdit,
    meta: { title: 'Редактировать рецепт' },
    beforeEnter: async (to, from, next) => {
      const recipeStore = useRecipeStore();
      if (!recipeStore.createData) {
        await recipeStore.fetchCreateData();
      }
      to.meta.data = recipeStore.createData;
      if (!to.meta.data) {
        next('/');
      } else {
        next();
      }
    },
  },
  {
    path: '/collection/create',
    name: 'collection-create',
    component: CollectionCreate,
    meta: { title: 'Создать коллекцию' },
  },
  {
    path: '/collection/:id',
    name: 'collection',
    component: CollectionView,
    meta: { title: 'Рецептище — Коллекция' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const recipeStore = useRecipeStore();
  const profileStore = useProfileStore();
  const collectionStore = useCollectionStore();
  const searchStore = useSearchStore();

  let title = to.meta.title || 'Рецептище';
  if (to.name === 'RecipeView' && to.params.id) {
    try {
      const recipe = await recipeStore.fetchRecipeById(to.params.id);
      title = `${recipe?.title || 'Рецепт'}`;
    } catch (error) {
      console.error('Error fetching recipe:', error);
      title = 'Рецепт не найден';
    }
  } else if (to.name === 'ProfileView' && to.params.id) {
    try {
      const profile = await profileStore.fetchProfileById(to.params.id);
      title = `${profile?.login || 'Профиль'}`;
    } catch (error) {
      console.error('Error fetching profile:', error);
      title = 'Профиль не найден';
    }
  } else if (to.name === 'collection' && to.params.id) {
    try {
      const collection = await collectionStore.fetchCollectionById(to.params.id);
      title = `${collection?.title || 'Коллекция'}`;
    } catch (error) {
      console.error('Error fetching collection:', error);
      title = 'Коллекция не найдена';
    }
  } else if (to.name === 'recipe-edit' && to.params.id) {
    title = `Редактировать рецепт`;
  }

  document.title = title;

  if (to.name === 'search' && !searchStore.searchData) {
    await searchStore.fetchSearchData();
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });

  next();
});

export default router;