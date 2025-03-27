import { createRouter, createWebHistory } from "vue-router";

import RecipeService from "./api/RecipeService";
import CollectionService from "./api/CollectionService";
import ProfileService from "./api/ProfileService";
import SearchService from "./api/SearchService";

import Home from "./views/Home.vue";
import About from "./views/About.vue";
import Search from "./views/Search.vue";

import { default as RecipeView } from "./views/recipe/View.vue";
import { default as RecipeCreate } from "./views/recipe/Create.vue";

import { default as CollectionView } from "./views/collection/View.vue";
import { default as CollectionCreate } from "./views/collection/Create.vue";

import { default as ProfileView } from "./views/profile/View.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: Home,
    meta: {
      title: "Рецептище",
    },
    beforeEnter: async (to, from, next) => {
      try {
        const response = await RecipeService.getAll();
        to.meta.data = response.data;
        next();
      } catch (error) {
        console.error("Error fetching recipe:", error);
        next({ name: "not-found" });
      }
    },
  },

  {
    path: "/search",
    name: "search",
    component: Search,
    meta: {
      title: "Поиск",
    },
    beforeEnter: async (to, from, next) => {
      try {
        const response = await SearchService.getData();
        to.meta.data = response.data;
        next();
      } catch (error) {
        console.error("Error fetching data:", error);
        next({ name: "not-found" });
      }
    },
  },

  {
    path: "/about",
    name: "about",
    component: About,
  },
  {
    path: "/recipe/:id",
    name: "recipe-view", // Уникальное имя для просмотра
    component: RecipeView,
    beforeEnter: async (to, from, next) => {
      try {
        const response = await RecipeService.getById(to.params.id);
        to.meta.recipe = response.data;
        to.meta.title = response.data.title;
        next();
      } catch (error) {
        console.error("Error fetching recipe:", error);
        next({ name: "not-found" });
      }
    },
  },

  {
    path: "/recipe/create",
    name: "recipe-create",
    component: RecipeCreate,
    meta: {
      title: "Создать рецепт",
    },
    beforeEnter: async (to, from, next) => {
      try {
        const response = await RecipeService.getCreateData();
        to.meta.data = response.data;
        next();
      } catch (error) {
        console.error("Error fetching recipe create data:", error);
        next({ name: "not-found" });
      }
    },
  },

  {
    path: "/collection/create",
    name: "collection-create",
    component: CollectionCreate,
    meta: {
      title: "Создать коллекцию",
    },
    beforeEnter: async (to, from, next) => {
      try {
        const response = await CollectionService.getCreateData();
        to.meta.data = response.data;
        next();
      } catch (error) {
        console.error("Error fetching collection:", error);
        next({ name: "not-found" });
      }
    },
  },

  {
    path: "/collection/:id",
    name: "collection",
    component: CollectionView,
    beforeEnter: async (to, from, next) => {
      try {
        const response = await CollectionService.getById(to.params.id);
        to.meta.collection = response.data;
        to.meta.title = response.data.title;
        next();
      } catch (error) {
        console.error("Error fetching collection:", error);
        next({ name: "not-found" });
      }
    },
  },

  {
    path: "/profile/:id",
    name: "profile",
    component: ProfileView,
    beforeEnter: async (to, from, next) => {
      try {
        const response = await ProfileService.getById(to.params.id);
        to.meta.profile = response.data;
        to.meta.title = response.data.login;
        next();
      } catch (error) {
        console.error("Error fetching profile:", error);
        next({ name: "not-found" });
      }
    },
  },

  // {
  //   path: '/:pathMatch(.*)*',
  //   name: 'not-found',
  //   component: () => import('./views/NotFound.vue'),
  // },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.afterEach((to) => {
  document.title = to.meta.title || "Рецептище";
});

export default router;
