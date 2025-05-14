import { defineStore } from 'pinia';
import UserService from '../api/UserService';

// Простая реализация debounce
function debounce(fn, wait) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), wait);
  };
}

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    currentUser: null,
    cacheTimestamps: {},
    searchCache: new Map(),
  }),
  actions: {
    async searchUsers(query) {
      const cacheKey = JSON.stringify(query);
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        this.searchCache.has(cacheKey) &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        this.users = this.searchCache.get(cacheKey);
        return this.users;
      }
      try {
        const response = await UserService.search(query);
        this.users = Array.isArray(response.data) ? response.data : [];
        this.searchCache.set(cacheKey, this.users);
        this.cacheTimestamps[cacheKey] = now;
        console.log('userStore: Search users:', this.users);
        return this.users;
      } catch (error) {
        console.error('userStore: Error searching users:', error);
        this.users = [];
        throw error;
      }
    },
    clearUsers() {
      this.users = [];
      this.searchCache.clear();
      this.cacheTimestamps = {};
      console.log('userStore: Users cleared');
    },
    debouncedSearch: debounce(async function (query) {
      await this.searchUsers(query);
    }, 300),
  },
});