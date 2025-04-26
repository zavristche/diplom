// src/stores/profile.js
import { defineStore } from 'pinia';
import ProfileService from '../api/ProfileService';

export const useProfileStore = defineStore('profile', {
  state: () => ({
    currentProfile: null,
  }),
  actions: {
    async fetchProfileById(id) {
      try {
        const response = await ProfileService.getById(id);
        this.currentProfile = response.data;
        return response.data;
      } catch (error) {
        console.error('Error fetching profile by ID:', error);
        this.currentProfile = null;
        return null;
      }
    },
  },
});