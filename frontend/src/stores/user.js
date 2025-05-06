import { defineStore } from 'pinia';
import UserService from '../api/UserService';

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    currentUser: null,
  }),
  actions: {
    async searchUsers(query) {
      try {
        const response = await UserService.search(query);
        this.users = Array.isArray(response.data) ? response.data : [];
        console.log('userStore: Search users:', this.users);
      } catch (error) {
        console.error('userStore: Error searching users:', error);
        this.users = [];
        throw error;
      }
    },
    clearUsers() {
      this.users = [];
      console.log('userStore: Users cleared');
    },
  },
});