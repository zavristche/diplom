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
        this.recipes = response.data;
      } catch (error) {
        console.error('Error fetching recipes:', error);
        throw error;
      }
    },
    async fetchRecipeById(id) {
      if (this.currentRecipe?.id === id) {
        return this.currentRecipe;
      }

      try {
        const response = await RecipeService.getById(id);
        console.log("API response for recipe:", JSON.stringify(response.data, null, 2));
        this.currentRecipe = response.data;
        return response.data;
      } catch (error) {
        console.error('Error fetching recipe by ID:', error);
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
        console.error('Error fetching recipe create data:', error);
        throw error;
      }
    },
  },
});