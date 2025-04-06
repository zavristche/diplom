import { defineStore } from 'pinia';
import UserService from "../api/UserService";

export const useAuthStore = defineStore('auth', {
  state: () => ({
    authKey: null,
    user: null,
  }),
  actions: {
    setUser(authKey, user) {
      this.authKey = authKey;
      this.user = user;
      localStorage.setItem('auth_key', authKey);
      // Сериализуем объект пользователя в JSON
      localStorage.setItem('user', JSON.stringify(user));
    },
    loadUser() {
      this.authKey = localStorage.getItem('auth_key');
      const userJson = localStorage.getItem('user');
      try {
        // Парсим JSON только если он есть и является валидным
        this.user = userJson ? JSON.parse(userJson) : null;
      } catch (e) {
        console.error("Ошибка при парсинге пользователя:", e);
        this.user = null;
      }
    },
    clearUser() {
      this.authKey = null;
      this.user = null;
      localStorage.removeItem('auth_key');
      localStorage.removeItem('user');
    },
    async logout() {
      try {
        await UserService.logout();
      } finally {
        this.clearUser();
      }
    },
  },
  getters: {
    isAuthenticated: (state) => !!state.authKey && !!state.user,
    userId: (state) => state.user?.id,
    avatar: (state) => state.user?.avatar || '/default-avatar.jpg'
  },
});