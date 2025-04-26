// src/stores/search.js
import { defineStore } from 'pinia';
import SearchService from '../api/SearchService';

export const useSearchStore = defineStore('search', {
  state: () => ({
    searchData: null,
  }),
  actions: {
    async fetchSearchData() {
      if (this.searchData) return; // Кэширование
      try {
        const response = await SearchService.getData();
        this.searchData = response.data;
      } catch (error) {
        console.error('Error fetching search data:', error);
      }
    },
  },
});
