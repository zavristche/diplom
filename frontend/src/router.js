import { createRouter, createWebHistory } from 'vue-router';
import { useRecipeStore } from './stores/recipe';
import { useProfileStore } from './stores/profile';
import { useCollectionStore } from './stores/collection';

import Home from './views/Home.vue';
import About from './views/About.vue';
import Search from './views/Search.vue';
import RecipeCreate from './views/recipe/Create.vue';
import RecipeEdit from './views/recipe/Edit.vue';
import CollectionCreate from './views/collection/Create.vue';
import CollectionView from './views/collection/View.vue';
import CollectionEdit from './views/collection/Edit.vue';

const routes = [
  { path: '/', name: 'home', component: Home, meta: { title: 'Рецептище — Главная' } },
  {
    path: '/search/:type(recipe|collection|user)?',
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
      try {
        if (!recipeStore.createData) {
          await recipeStore.fetchCreateData();
        }
        to.meta.data = recipeStore.createData;
        if (!to.meta.data) {
          console.error('recipe-create: createData is null');
          next('/');
        } else {
          next();
        }
      } catch (error) {
        console.error('Error in beforeEnter for recipe-create:', error);
        next('/');
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
      try {
        console.log('recipe-edit: Fetching createData and recipe for id:', to.params.id);
        if (!recipeStore.createData) {
          await recipeStore.fetchCreateData();
        }
        to.meta.data = recipeStore.createData;

        const recipe = await recipeStore.fetchRecipeById(to.params.id);
        to.meta.recipe = recipe;

        if (!to.meta.data || !to.meta.recipe) {
          console.error('recipe-edit: Missing data or recipe');
          next('/');
        } else {
          next();
        }
      } catch (error) {
        console.error('Error in beforeEnter for recipe-edit:', error);
        next('/');
      }
    },
  },
  {
    path: '/collection/create',
    name: 'collection-create',
    component: CollectionCreate,
    meta: { title: 'Создать коллекцию' },
    beforeEnter: async (to, from, next) => {
      const collectionStore = useCollectionStore();
      try {
        if (!collectionStore.createData) {
          await collectionStore.fetchCreateData();
        }
        to.meta.data = collectionStore.createData;
        if (!to.meta.data) {
          console.error('collection-create: createData is null');
          next('/');
        } else {
          next();
        }
      } catch (error) {
        console.error('Error in beforeEnter for collection-create:', error);
        next('/');
      }
    },
  },
  {
    path: '/collection/:id',
    name: 'collection',
    component: CollectionView,
    meta: { title: 'Рецептище — Коллекция' },
  },
  {
    path: '/collection/edit/:id',
    name: 'collection-edit',
    component: CollectionEdit,
    meta: { title: 'Редактировать коллекцию' },
    beforeEnter: async (to, from, next) => {
      const collectionStore = useCollectionStore();
      try {
        console.log('collection-edit: Fetching createData and collection for id:', to.params.id);
        if (!to.params.id) {
          console.error('collection-edit: No id in route params');
          next('/');
          return;
        }

        if (!collectionStore.createData) {
          console.log('collection-edit: Fetching createData');
          await collectionStore.fetchCreateData();
        }
        to.meta.data = collectionStore.createData;

        console.log('collection-edit: Fetching collection by id:', to.params.id);
        const collection = await collectionStore.fetchCollectionById(to.params.id);
        to.meta.collection = collection;

        if (!to.meta.data) {
          console.error('collection-edit: createData is null');
          next('/');
        } else if (!to.meta.collection) {
          console.error('collection-edit: collection is null');
          next('/');
        } else {
          console.log('collection-edit: Data loaded successfully', { createData: to.meta.data, collection: to.meta.collection });
          next();
        }
      } catch (error) {
        console.error('Error in beforeEnter for collection-edit:', error);
        next('/');
      }
    },
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
  } else if (to.name === 'collection-edit' && to.params.id) {
    title = `Редактировать коллекцию`;
  }

  document.title = title;

  window.scrollTo({ top: 0, behavior: 'smooth' });

  next();
});

export default router;