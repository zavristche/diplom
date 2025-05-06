import { defineStore } from 'pinia';
import RecipeService from '../api/RecipeService';

export const useRecipeStore = defineStore('recipe', {
  state: () => ({
    recipes: [],
    currentRecipe: null,
    createData: null,
  }),
  actions: {
    async fetchRecipes() {
      try {
        const response = await RecipeService.getAll();
        this.recipes = Array.isArray(response.data) ? response.data : [];
        console.log('recipeStore: Fetched recipes:', this.recipes);
      } catch (error) {
        console.error('recipeStore: Error fetching recipes:', error);
        throw error;
      }
    },
    async fetchRecipeById(id) {
      if (this.currentRecipe?.id === id) {
        return this.currentRecipe;
      }
      try {
        const response = await RecipeService.getById(id);
        console.log('recipeStore: API response for recipe:', JSON.stringify(response.data, null, 2));
        this.currentRecipe = response.data;
        return response.data;
      } catch (error) {
        console.error('recipeStore: Error fetching recipe by ID:', error);
        this.currentRecipe = null;
        throw error;
      }
    },
    async fetchCreateData() {
      if (this.createData) return;
      try {
        const response = await RecipeService.getCreateData();
        this.createData = response.data;
      } catch (error) {
        console.error('recipeStore: Error fetching recipe create data:', error);
        throw error;
      }
    },
    async searchRecipes(query) {
      try {
        const response = await RecipeService.search(query);
        console.log('recipeStore: Search recipes response:', response.data);
        this.recipes = Array.isArray(response.data.recipes) ? response.data.recipes : [];
        console.log('recipeStore: Search recipes updated:', this.recipes);
      } catch (error) {
        console.error('recipeStore: Error searching recipes:', error);
        this.recipes = [];
        throw error;
      }
    },
    clearRecipes() {
      this.recipes = [];
      console.log('recipeStore: Recipes cleared');
    },
  },
});