import { defineStore } from 'pinia';
import CollectionService from '../api/CollectionService';
import ProfileService from '../api/ProfileService';

export const useCollectionStore = defineStore('collection', {
  state: () => ({
    collections: [],
    currentCollection: null,
    createData: null,
  }),
  actions: {
    async fetchCollections() {
      try {
        const response = await CollectionService.getAll();
        this.collections = response.data;
      } catch (error) {
        console.error('Error fetching collections:', error);
      }
    },
    async fetchCollectionById(id) {
      try {
        const response = await CollectionService.getById(id);
        this.currentCollection = response.data;
        return response.data;
      } catch (error) {
        console.error('Error fetching collection by ID:', error);
        this.currentCollection = null;
        return null;
      }
    },
    async fetchCreateData() {
      if (this.createData) return; // Кэширование
      try {
        const response = await CollectionService.getCreateData();
        this.createData = response.data;
      } catch (error) {
        console.error('Error fetching collection create data:', error);
      }
    },
    async fetchUserCollections(userId) {
      try {
        const response = await ProfileService.getById(userId);
        this.collections = response.data.collections || [];
        return response.data.collections;
      } catch (error) {
        console.error('Error fetching user collections:', error);
        throw error;
      }
    },
  },
});