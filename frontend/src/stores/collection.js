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
        this.collections = Array.isArray(response.data) ? response.data : [];
        console.log('collectionStore: Fetched collections:', this.collections);
      } catch (error) {
        console.error('collectionStore: Error fetching collections:', error);
      }
    },
    async fetchCollectionById(id) {
      try {
        const response = await CollectionService.getById(id);
        this.currentCollection = response.data;
        console.log('collectionStore: Fetched collection:', response.data);
        return response.data;
      } catch (error) {
        console.error('collectionStore: Error fetching collection by ID:', error);
        this.currentCollection = null;
        return null;
      }
    },
    async fetchCreateData() {
      if (this.createData) return;
      try {
        const response = await CollectionService.getCreateData();
        this.createData = response.data;
      } catch (error) {
        console.error('collectionStore: Error fetching collection create data:', error);
      }
    },
    async fetchUserCollections(userId) {
      try {
        const response = await ProfileService.getById(userId);
        this.collections = response.data.collections || [];
        console.log('collectionStore: Fetched user collections:', this.collections);
        return response.data.collections;
      } catch (error) {
        console.error('collectionStore: Error fetching user collections:', error);
        throw error;
      }
    },
    async searchCollections(query) {
      try {
        const response = await CollectionService.search(query);
        this.collections = Array.isArray(response.data) ? response.data : [];
        console.log('collectionStore: Search collections:', this.collections);
      } catch (error) {
        console.error('collectionStore: Error searching collections:', error);
        this.collections = [];
        throw error;
      }
    },
    clearCollections() {
      this.collections = [];
      console.log('collectionStore: Collections cleared');
    },
  },
});