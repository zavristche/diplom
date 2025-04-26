<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import Register from "../components/Register.vue";
import Login from "../components/Login.vue";
import BaseIcon from "./BaseIcon.vue";

const authStore = useAuthStore();
const router = useRouter();

const isRegisterOpen = ref(false);
const isLoginOpen = ref(false);

const handleLogout = async () => {
  await authStore.logout(); // Вызываем logout из хранилища
  router.push("/"); // Перенаправляем после успешного выхода
};
</script>

<template>
  <Register :isOpen="isRegisterOpen" @close="isRegisterOpen = false" />
  <Login :isOpen="isLoginOpen" @close="isLoginOpen = false" />
  <header>
    <div class="container">
      <router-link to="/">
        <BaseIcon class="logo" name="logo" />
      </router-link>
      <nav class="labels left">
        <router-link to="/search/recipe">
          <div class="label-item">Рецепты</div>
        </router-link>
        <router-link to="/search/collection">
          <div class="label-item">Коллекции</div>
        </router-link>
        <router-link to="/search/author">
          <div class="label-item">Авторы</div>
        </router-link>
      </nav>
      <div class="search">
        <div class="btn icon">
          <BaseIcon class="icon-dark-45-1" viewBox="0 0 45 45" name="search" />
        </div>
        <input type="text" id="search" placeholder="Поиск" />
        <div class="btn-container">
          <button type="submit" id="btn-search" class="btn-salat">Найти</button>
        </div>
      </div>
      <nav class="labels left">
        <template v-if="!authStore.isAuthenticated">
          <button class="label-item" @click="isRegisterOpen = true">
            Регистрация
          </button>
          <button class="label-item" @click="isLoginOpen = true">Вход</button>
        </template>
        <template v-else>
          <router-link :to="`/profile/${authStore.user?.id}`" class="avatar-link">
            <img
              :src="authStore.user?.avatar"
              alt="Аватар пользователя"
              class="user-avatar"
              v-if="authStore.user?.avatar"
            />
            <BaseIcon v-else name="user" class="default-avatar" />
          </router-link>
          <button class="btn-dark" @click="handleLogout">
            <BaseIcon
              viewBox="0 0 29 29"
              class="icon-white-30-2"
              name="logout"
            />
          </button>
        </template>
      </nav>
    </div>
  </header>
</template>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;

.logo {
  width: 57px;
  height: 64px;
}

header {
  position: sticky;
  z-index: 20;
  top: 0;
  bottom: 50px;
  display: flex;
  justify-content: center;
  background-color: $background-dark;
  color: $light-text;
  box-shadow: $shadow;
  font-weight: 500;
  width: 100%;
  height: 6.25rem;

  .container {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    width: 75rem;
    height: 100%;
    max-width: 75rem;
    min-width: 28.75rem;
  }

  .label-item {
    color: $light;
  }

  .avatar-link {
    display: flex;
    align-items: center;
    margin-right: 1rem;
  }

  .user-avatar {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
  }

  .default-avatar {
    width: 40px;
    height: 40px;
    fill: $light-text;
  }

  .btn-dark {
    background-color: none;
    box-shadow: none;
  }
}
</style>