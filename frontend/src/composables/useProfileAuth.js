import { computed } from "vue";
import { useAuthStore } from "../stores/auth";
import { useRoute } from "vue-router";

export function useProfileAuth() {
  const authStore = useAuthStore();
  const route = useRoute();

  // Данные профиля из маршрута
  const profile = route.meta.profile;

  // Текущий пользователь из authStore
  const currentUser = computed(() => authStore.user);

  // Проверка авторизации
  const isAuthenticated = computed(() => !!currentUser.value);

  // Проверка, является ли это страницей текущего пользователя
  const isOwnProfile = computed(() => {
    return isAuthenticated.value && currentUser.value?.id === profile?.id;
  });

  return {
    profile,
    currentUser,
    isAuthenticated,
    isOwnProfile,
  };
}