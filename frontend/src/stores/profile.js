import { defineStore } from 'pinia';
import ProfileService from '../api/ProfileService';

export const useProfileStore = defineStore('profile', {
  state: () => ({
    currentProfile: null,
    cacheTimestamps: {},
  }),
  actions: {
    async fetchProfileById(id) {
      const cacheKey = `profile_${id}`;
      const cacheTTL = 5 * 60 * 1000; // 5 минут
      const now = Date.now();
      if (
        this.currentProfile?.id === id &&
        this.cacheTimestamps[cacheKey] &&
        now - this.cacheTimestamps[cacheKey] < cacheTTL
      ) {
        return this.currentProfile;
      }
      try {
        const response = await ProfileService.getById(id);
        this.currentProfile = response.data;
        this.cacheTimestamps[cacheKey] = now;
        return response.data;
      } catch (error) {
        console.error('Error fetching profile by ID:', error);
        this.currentProfile = null;
        return null;
      }
    },
    clearProfile() {
      this.currentProfile = null;
      this.cacheTimestamps = {};
      console.log('profileStore: Profile cleared');
    },
  },
});