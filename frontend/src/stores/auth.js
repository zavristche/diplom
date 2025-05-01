import { defineStore } from "pinia";
import UserService from "../api/UserService";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    authKey: null,
    user: null,
    cacheBuster: Date.now(),
  }),
  actions: {
    setUser(authKey, user) {
      this.authKey = authKey;
      this.user = user;
      localStorage.setItem("auth_key", authKey);
      localStorage.setItem("user", JSON.stringify(user));
      this.cacheBuster = Date.now();
    },
    async loadUser() {
      this.authKey = localStorage.getItem("auth_key");
      const userJson = localStorage.getItem("user");

      try {
        this.user = userJson ? JSON.parse(userJson) : null;
      } catch (e) {
        console.error("Ошибка при парсинге пользователя из localStorage:", e);
        this.user = null;
      }

      if (this.authKey) {
        await this.syncUser();
      }
    },
    clearUser() {
      this.authKey = null;
      this.user = null;
      localStorage.removeItem("auth_key");
      localStorage.removeItem("user");
      this.cacheBuster = Date.now();
    },
    async logout() {
      try {
        await UserService.logout();
        this.clearUser();
      } catch (error) {
        console.error("Ошибка при выходе:", error);
        this.clearUser();
      }
    },
    async updateUser(newUserData) {
      this.user = { ...this.user, ...newUserData };
      localStorage.setItem("user", JSON.stringify(this.user));
      this.cacheBuster = Date.now();

      await this.syncUser();
    },
    async syncUser() {
      if (!this.authKey) {
        return;
      }

      try {
        const response = await UserService.getCurrentUser(this.authKey);
        if (response.data.success) {
          this.user = response.data.user;
          localStorage.setItem("user", JSON.stringify(this.user));
          this.cacheBuster = Date.now();
        } else {
          console.error("syncUser: Failed to fetch user from server:", response.data.message);
          this.clearUser();
        }
      } catch (error) {
        console.error("syncUser: Error fetching user from server:", error);
        if (!this.user) {
          this.clearUser();
        }
      }
    },
  },
  getters: {
    isAuthenticated: (state) => !!state.authKey && !!state.user,
    userId: (state) => state.user?.id,
    avatar: (state) => {
      const baseAvatar = state.user?.avatar || "/default-avatar.jpg";
      return `${baseAvatar}?cb=${state.cacheBuster}`;
    },
  },
});