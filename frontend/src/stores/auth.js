import { defineStore } from "pinia";
import UserService from "../api/UserService";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    authKey: null,
    user: null,
  }),
  actions: {
    setUser(authKey, user) {
      this.authKey = authKey;
      this.user = user;
      localStorage.setItem("auth_key", authKey);
      localStorage.setItem("user", JSON.stringify(user));
    },
    loadUser() {
      this.authKey = localStorage.getItem("auth_key");
      const userJson = localStorage.getItem("user");
      try {
        this.user = userJson ? JSON.parse(userJson) : null;
      } catch (e) {
        console.error("Ошибка при парсинге пользователя:", e);
        this.user = null;
      }
    },
    clearUser() {
      this.authKey = null;
      this.user = null;
      localStorage.removeItem("auth_key");
      localStorage.removeItem("user");
    },
    async logout() {
      try {
        await UserService.logout(); // Убираем this.authKey
        this.clearUser();
      } catch (error) {
        console.error("Ошибка при выходе:", error);
        this.clearUser(); // Очищаем данные даже при ошибке
      }
    },
  },
  getters: {
    isAuthenticated: (state) => !!state.authKey && !!state.user,
    userId: (state) => state.user?.id,
    avatar: (state) => state.user?.avatar || "/default-avatar.jpg",
  },
});