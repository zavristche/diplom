import { defineStore } from 'pinia';
import RecipeService from '../api/RecipeService';

// Простая реализация debounce
function debounce(fn, wait) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), wait);
  };
}

// Сжатие данных для localStorage
function compressData(data) {
  return JSON.stringify(data); // Можно добавить LZString или другой алгоритм сжатия
}

function decompressData(data) {
  return JSON.parse(data);
}

export const useRecipeStore = defineStore('recipe', {
  state: () => ({
    recipes: [],
    currentRecipe: null,
    createData: null,
    cacheTimestamps: {},
    searchCache: new Map(),
    pendingRequests: new Map(), // Для дедупликации запросов
  }),
  actions: {
    async fetchRecipes() {
      const cacheKey = 'recipes_all';
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        this.recipes.length &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        return this.recipes;
      }
      if (this.pendingRequests.has(cacheKey)) {
        return this.pendingRequests.get(cacheKey);
      }
      const request = RecipeService.getAll()
        .then((response) => {
          this.recipes = Array.isArray(response.data) ? response.data : [];
          this.cacheTimestamps[cacheKey] = now;
          return this.recipes;
        })
        .catch((error) => {
          this.recipes = [];
          throw error;
        })
        .finally(() => {
          this.pendingRequests.delete(cacheKey);
        });
      this.pendingRequests.set(cacheKey, request);
      return request;
    },
    async fetchRecipeById(id) {
      const cacheKey = `recipe_${id}`;
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        this.currentRecipe?.id === id &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        return this.currentRecipe;
      }
      if (this.pendingRequests.has(cacheKey)) {
        return this.pendingRequests.get(cacheKey);
      }
      const request = RecipeService.getById(id)
        .then((response) => {
          this.currentRecipe = response.data;
          this.cacheTimestamps[cacheKey] = now;
          return response.data;
        })
        .catch((error) => {
          this.currentRecipe = null;
          throw error;
        })
        .finally(() => {
          this.pendingRequests.delete(cacheKey);
        });
      this.pendingRequests.set(cacheKey, request);
      return request;
    },
    async fetchCreateData() {
      const cacheKey = 'recipe_create_data';
      const cached = localStorage.getItem(cacheKey);
      const cacheTTL = 24 * 60 * 60 * 1000; // 24 часа
      const now = Date.now();
      if (cached) {
        try {
          const { data, timestamp } = decompressData(cached);
          if (now - timestamp < cacheTTL) {
            this.createData = data;
            return;
          }
        } catch (error) {
          console.error('Error decompressing createData:', error);
        }
      }
      if (this.pendingRequests.has(cacheKey)) {
        return this.pendingRequests.get(cacheKey);
      }
      const request = RecipeService.getCreateData()
        .then((response) => {
          this.createData = response.data;
          localStorage.setItem(
            cacheKey,
            compressData({ data: response.data, timestamp: now })
          );
        })
        .catch((error) => {
          throw error;
        })
        .finally(() => {
          this.pendingRequests.delete(cacheKey);
        });
      this.pendingRequests.set(cacheKey, request);
      return request;
    },
    async searchRecipes(query) {
      const cacheKey = JSON.stringify(query);
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        this.searchCache.has(cacheKey) &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        this.recipes = this.searchCache.get(cacheKey);
        return this.recipes;
      }
      if (this.pendingRequests.has(cacheKey)) {
        return this.pendingRequests.get(cacheKey);
      }
      const request = RecipeService.search(query)
        .then((response) => {
          this.recipes = Array.isArray(response.data.recipes) ? response.data.recipes : [];
          this.searchCache.set(cacheKey, this.recipes);
          this.cacheTimestamps[cacheKey] = now;
          return this.recipes;
        })
        .catch((error) => {
          this.recipes = [];
          throw error;
        })
        .finally(() => {
          this.pendingRequests.delete(cacheKey);
        });
      this.pendingRequests.set(cacheKey, request);
      return request;
    },
    clearRecipes() {
      this.recipes = [];
      this.searchCache.clear();
      this.cacheTimestamps = {};
      this.pendingRequests.clear();
      localStorage.removeItem('recipe_create_data');
    },
    async fetchRandomRecipe() {
      const cacheKey = 'random_recipe';
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        this.currentRecipe &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        return this.currentRecipe;
      }
      if (this.pendingRequests.has(cacheKey)) {
        return this.pendingRequests.get(cacheKey);
      }
      const request = RecipeService.getRandom()
        .then((response) => {
          this.currentRecipe = response.data.recipe;
          this.cacheTimestamps[cacheKey] = now;
          return this.currentRecipe;
        })
        .catch((error) => {
          this.currentRecipe = null;
          throw error;
        })
        .finally(() => {
          this.pendingRequests.delete(cacheKey);
        });
      this.pendingRequests.set(cacheKey, request);
      return request;
    },
    async createRecipe(recipeData) {
      try {
        const response = await RecipeService.create(recipeData);
        this.clearRecipes(); // Инвалидация кэша
        return response.data;
      } catch (error) {
        throw error;
      }
    },
    async updateRecipe(id, recipeData) {
      try {
        const response = await RecipeService.update(id, recipeData);
        this.clearRecipes(); // Инвалидация кэша
        return response.data;
      } catch (error) {
        throw error;
      }
    },
    debouncedSearch: debounce(async function (query) {
      await this.searchRecipes(query);
    }, 300),
  },
});