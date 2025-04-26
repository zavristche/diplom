// src/stores/recipe.js
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
      }
    },
    async fetchRecipeById(id) {
      try {
        const response = await RecipeService.getById(id);
        this.currentRecipe = response.data;
        return response.data;
      } catch (error) {
        console.error('Error fetching recipe by ID:', error);
        this.currentRecipe = null;
        return null;
      }
    },
    async fetchCreateData() {
      if (this.createData) return; // Кэширование
      try {
        const response = await RecipeService.getCreateData();
        this.createData = response.data;
      } catch (error) {
        console.error('Error fetching recipe create data:', error);
      }
    },
  },
});