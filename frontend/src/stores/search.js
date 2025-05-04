import { defineStore } from 'pinia';
import SearchService from '../api/SearchService';

export const useSearchStore = defineStore('search', {
  state: () => ({
    searchData: null,
  }),
  actions: {
    async fetchSearchData() {
      try {
        if (this.searchData) {
          console.log('searchStore: Using cached searchData:', this.searchData);
          return this.searchData;
        }
        console.log('searchStore: Fetching searchData');
        const response = await SearchService.getData();
        if (!response.data?.data) {
          throw new Error('Invalid search data structure');
        }
        this.searchData = response.data.data;
        console.log('searchStore: searchData fetched:', this.searchData);
        return this.searchData;
      } catch (error) {
        console.error('searchStore: Error fetching searchData:', error);
        this.searchData = null;
        throw error;
      }
    },
  },
});