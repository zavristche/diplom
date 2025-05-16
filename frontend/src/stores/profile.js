import { defineStore } from 'pinia';
import ProfileService from '../api/ProfileService';
import apiClient from '../api/apiClient';

export const useProfileStore = defineStore('profile', {
  state: () => ({
    currentProfile: null,
  }),

  actions: {
    async fetchProfileById(id) {
      try {
        console.log('Fetching profile for ID:', id);
        const response = await ProfileService.getById(id);
        console.log('Profile fetched:', response.data);
        this.currentProfile = response.data;
        return response.data;
      } catch (error) {
        console.error('Error fetching profile:', error);
        throw error;
      }
    },

    async updateProfile(userId) {
      // Проверяем, обновлялся ли профиль недавно (например, в последние 30 секунд)
      const now = Date.now();
      if (this.lastUpdated && now - this.lastUpdated < 30_000) {
        console.log('Profile update skipped, using cached data');
        return;
      }

      try {
        const response = await apiClient.get(`/api/profile/${userId}`);
        this.currentProfile = response.data.profile;
        this.lastUpdated = now;
        console.log('Profile updated:', this.currentProfile);
      } catch (error) {
        console.error('Error updating profile:', error);
        throw error;
      }
    },
  },
});