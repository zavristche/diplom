import { defineStore } from 'pinia';
import CollectionService from '../api/CollectionService';
import ProfileService from '../api/ProfileService';
import { useProfileStore } from './profile';

// Простая реализация debounce
function debounce(fn, wait) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), wait);
  };
}

export const useCollectionStore = defineStore('collection', {
  state: () => ({
    collections: [],
    currentCollection: null,
    createData: null,
    cacheTimestamps: {},
    searchCache: new Map(),
  }),
  actions: {
    async fetchCollections() {
      const cacheKey = 'collections_all';
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        this.collections.length &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        return this.collections;
      }
      try {
        const response = await CollectionService.getAll();
        this.collections = Array.isArray(response.data) ? response.data : [];
        this.cacheTimestamps[cacheKey] = now;
        return this.collections;
      } catch (error) {
        console.error('collectionStore: Error fetching collections:', error);
        this.collections = [];
        return [];
      }
    },
    async fetchCollectionById(id) {
      const cacheKey = `collection_${id}`;
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        this.currentCollection?.id === id &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        return this.currentCollection;
      }
      try {
        const response = await CollectionService.getById(id);
        this.currentCollection = response.data;
        this.cacheTimestamps[cacheKey] = now;
        return response.data;
      } catch (error) {
        console.error('collectionStore: Error fetching collection by ID:', error);
        this.currentCollection = null;
        return null;
      }
    },
    async fetchCreateData() {
      const cacheKey = 'collection_create_data';
      const cached = localStorage.getItem(cacheKey);
      const cacheTTL = 24 * 60 * 60 * 1000; // 24 часа
      const now = Date.now();
      if (cached) {
        const { data, timestamp } = JSON.parse(cached);
        if (now - timestamp < cacheTTL) {
          this.createData = data;
          return;
        }
      }
      try {
        const response = await CollectionService.getCreateData();
        this.createData = response.data;
        localStorage.setItem(cacheKey, JSON.stringify({ data: response.data, timestamp: now }));
      } catch (error) {
        console.error('collectionStore: Error fetching collection create data:', error);
      }
    },
    async fetchUserCollections(userId) {
      const profileStore = useProfileStore();
      const cacheKey = `user_collections_${userId}`;
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        profileStore.currentProfile?.id === userId &&
        this.collections.length &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        return this.collections;
      }
      try {
        const response = await ProfileService.getById(userId);
        this.collections = response.data.collections || [];
        profileStore.currentProfile = response.data; // Синхронизация с профилем
        this.cacheTimestamps[cacheKey] = now;
        return this.collections;
      } catch (error) {
        console.error('collectionStore: Error fetching user collections:', error);
        this.collections = [];
        throw error;
      }
    },
    async searchCollections(query) {
      const cacheKey = JSON.stringify(query);
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        this.searchCache.has(cacheKey) &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        this.collections = this.searchCache.get(cacheKey);
        return this.collections;
      }
      try {
        const response = await CollectionService.search(query);
        this.collections = Array.isArray(response.data) ? response.data : [];
        this.searchCache.set(cacheKey, this.collections);
        this.cacheTimestamps[cacheKey] = now;
        return this.collections;
      } catch (error) {
        console.error('collectionStore: Error searching collections:', error);
        this.collections = [];
        throw error;
      }
    },
    clearCollections() {
      this.collections = [];
      this.searchCache.clear();
      console.log('collectionStore: Collections cleared');
    },
    debouncedSearch: debounce(async function (query) {
      await this.searchCollections(query);
    }, 300),
  },
});