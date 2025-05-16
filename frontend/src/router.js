import { createRouter, createWebHistory } from 'vue-router';
import { useRecipeStore } from './stores/recipe';
import { useProfileStore } from './stores/profile';
import { useCollectionStore } from './stores/collection';
import { useAuthStore } from './stores/auth';

// Прямые импорты для компонентов с критичными стилями
import Home from './views/Home.vue';
import Search from './views/Search.vue';
import RecipeView from './views/recipe/View.vue';
import CollectionView from './views/collection/View.vue';
import RecipeCreate from './views/recipe/Create.vue';
import CollectionCreate from './views/collection/Create.vue';
import NotFound from './views/NotFound.vue';

const routes = [
  { path: '/', name: 'home', component: Home, meta: { title: 'Рецептище' } },
  { path: '/search/:type(recipe|collection|user)?', name: 'search', component: Search, meta: { title: 'Поиск' } },
  { path: '/recipe/:id', name: 'RecipeView', component: RecipeView, meta: { title: 'Рецепт' } },
  { path: '/profile/:id', name: 'ProfileView', component: () => import('./views/profile/View.vue'), meta: { title: 'Профиль' } },
  {
    path: '/recipe/create',
    name: 'recipe-create',
    component: RecipeCreate,
    meta: { title: 'Создать рецепт', store: useRecipeStore, fetch: 'fetchCreateData', dataField: 'createData', redirect: NotFound },
    async beforeEnter(to, from, next) {
      const store = useRecipeStore();
      try {
        const createData = store.createData ? Promise.resolve(store.createData) : store.fetchCreateData().then(() => store.createData);
        to.meta.data = await createData;
        next(to.meta.data ? undefined : { name: 'not-found', replace: true });
      } catch {
        next({ name: 'not-found', replace: true });
      }
    },
  },
  {
    path: '/recipe/edit/:id',
    name: 'recipe-edit',
    component: () => import('./views/recipe/Edit.vue'),
    meta: { title: 'Редактировать рецепт', store: useRecipeStore, fetch: 'fetchCreateData', dataField: 'createData', fetchItem: 'fetchRecipeById', itemField: 'recipe', redirect: NotFound },
    async beforeEnter(to, from, next) {
      const store = useRecipeStore();
      try {
        const [createData, recipe] = await Promise.all([
          store.createData ? Promise.resolve(store.createData) : store.fetchCreateData().then(() => store.createData),
          store.fetchRecipeById(to.params.id),
        ]);
        to.meta.data = createData;
        to.meta[to.meta.itemField] = recipe;
        next(createData && recipe ? undefined : { name: 'not-found', replace: true });
      } catch {
        next({ name: 'not-found', replace: true });
      }
    },
  },
  {
    path: '/collection/create',
    name: 'collection-create',
    component: CollectionCreate,
    meta: { title: 'Создать коллекцию', store: useCollectionStore, fetch: 'fetchCreateData', dataField: 'createData', redirect: NotFound },
    async beforeEnter(to, from, next) {
      const store = useCollectionStore();
      try {
        const createData = store.createData ? Promise.resolve(store.createData) : store.fetchCreateData().then(() => store.createData);
        to.meta.data = await createData;
        next(to.meta.data ? undefined : { name: 'not-found', replace: true });
      } catch {
        next({ name: 'not-found', replace: true });
      }
    },
  },
  { path: '/collection/:id', name: 'collection', component: CollectionView, meta: { title: 'Коллекция' } },
  {
    path: '/collection/edit/:id',
    name: 'collection-edit',
    component: () => import('./views/collection/Edit.vue'),
    meta: { title: 'Редактировать коллекцию', store: useCollectionStore, fetch: 'fetchCreateData', dataField: 'createData', fetchItem: 'fetchCollectionById', itemField: 'collection', redirect: NotFound },
    async beforeEnter(to, from, next) {
      const store = useCollectionStore();
      try {
        const createDataPromise = store.createData ? Promise.resolve(store.createData) : store.fetchCreateData().then(() => store.createData);
        const collectionPromise = store.collections[to.params.id] 
          ? Promise.resolve(store.collections[to.params.id]) 
          : store.fetchCollectionById(to.params.id);
        
        const [createData, collection] = await Promise.all([createDataPromise, collectionPromise]);
        to.meta.data = createData;
        to.meta[to.meta.itemField] = collection;
        next(createData && collection ? undefined : { name: 'not-found', replace: true });
      } catch {
        next({ name: 'not-found', replace: true });
      }
    },
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('./views/admin/Home.vue'),
    meta: { title: 'Админ-панель' },
    beforeEnter(to, from, next) {
      const authStore = useAuthStore();
      if (!authStore.isAuthenticated || !authStore.isAdmin) {
        return next({ name: 'not-found', replace: true });
      }
      next();
    },
  },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound, meta: { title: 'Страница не найдена' } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const stores = {
    RecipeView: { store: useRecipeStore(), fetch: 'fetchRecipeById', field: 'title', defaultTitle: 'Рецепт' },
    ProfileView: { store: useProfileStore(), fetch: 'fetchProfileById', field: 'login', defaultTitle: 'Профиль' },
    collection: { store: useCollectionStore(), fetch: 'fetchCollectionById', field: 'title', defaultTitle: 'Коллекция' },
  };

  let title = to.meta.title || 'Рецептище';
  const routeConfig = stores[to.name];
  if (routeConfig && to.params.id) {
    const store = routeConfig.store;
    const currentItem = store.currentRecipe || store.currentProfile || store.currentCollection;
    if (currentItem?.id === to.params.id) {
      title = currentItem[routeConfig.field] || routeConfig.defaultTitle;
    } else {
      try {
        const item = await routeConfig.store[routeConfig.fetch](to.params.id);
        if (!item) return next({ name: 'not-found', replace: true });
        title = item[routeConfig.field] || routeConfig.defaultTitle;
      } catch {
        title = `${routeConfig.defaultTitle} не найден`;
        return next({ name: 'not-found', replace: true });
      }
    }
  }

  document.title = title;
  window.scrollTo({ top: 0, behavior: 'smooth' });
  next();
});

export default router;