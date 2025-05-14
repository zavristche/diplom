import { defineStore } from 'pinia';
import SearchService from '../api/SearchService';

export const useSearchStore = defineStore('search', {
  state: () => ({
    searchData: null,
    cacheTimestamps: {},
  }),
  actions: {
    async fetchSearchData() {
      const cacheKey = 'search_data';
      const cached = localStorage.getItem(cacheKey);
      const cacheTTL = 24 * 60 * 60 * 1000; // 24 часа
      const now = Date.now();
      if (cached) {
        const { data, timestamp } = JSON.parse(cached);
        if (now - timestamp < cacheTTL) {
          this.searchData = data;
          this.cacheTimestamps[cacheKey] = timestamp;
          console.log('searchStore: Using cached searchData:', this.searchData);
          return this.searchData;
        }
      }
      try {
        console.log('searchStore: Fetching searchData');
        const response = await SearchService.getData();
        if (!response.data?.data) {
          throw new Error('Invalid search data structure');
        }
        this.searchData = response.data.data;
        localStorage.setItem(cacheKey, JSON.stringify({ data: this.searchData, timestamp: now }));
        this.cacheTimestamps[cacheKey] = now;
        console.log('searchStore: searchData fetched:', this.searchData);
        return this.searchData;
      } catch (error) {
        console.error('searchStore: Error fetching searchData:', error);
        this.searchData = null;
        throw error;
      }
    },
    clearSearchData() {
      this.searchData = null;
      this.cacheTimestamps = {};
      localStorage.removeItem('search_data');
      console.log('searchStore: Search data cleared');
    },
  },
});