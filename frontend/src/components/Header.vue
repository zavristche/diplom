<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../stores/auth";
import Register from "./Register.vue";
import Login from "./Login.vue";
import BaseIcon from "./BaseIcon.vue";
import Search from "./Search.vue";

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const isRegisterOpen = ref(false);
const isLoginOpen = ref(false);
const isMenuOpen = ref(false);

const isAdminRoute = () => route.path.includes("/admin");

const handleLogout = async () => {
  await authStore.logout();
  router.push("/");
};

const toggleMenu = () => (isMenuOpen.value = !isMenuOpen.value);

const handleClickOutside = (event) => {
  if (
    isMenuOpen.value &&
    !event.target.closest(".sidebar") &&
    !event.target.closest(".burger-btn")
  ) {
    isMenuOpen.value = false;
  }
};

onMounted(() => document.addEventListener("click", handleClickOutside));
onUnmounted(() => document.removeEventListener("click", handleClickOutside));
</script>

<template>
  <Register :isOpen="isRegisterOpen" @close="isRegisterOpen = false" class="register-modal" />
  <Login :isOpen="isLoginOpen" @close="isLoginOpen = false" class="login-modal" />

  <header>
    <div class="header-grid">
      <div class="header-left">
        <button class="btn-icon burger-btn" @click="toggleMenu" aria-label="Меню">
          <BaseIcon viewBox="0 0 65 65" class="icon-white-65-2" name="burger" />
        </button>
        <router-link :to="isAdminRoute() ? '/admin' : '/'" class="logo-link">
          <BaseIcon class="logo" name="logo" />
        </router-link>
      </div>

      <nav class="main-nav">
        <template v-if="isAdminRoute()">
          <router-link to="/" class="nav-link">На сайт</router-link>
        </template>
        <template v-else>
          <router-link to="/search/recipe" class="nav-link">Рецепты</router-link>
          <router-link to="/search/collection" class="nav-link">Коллекции</router-link>
          <router-link to="/search/user" class="nav-link">Пользователи</router-link>
        </template>
      </nav>

      <div class="header-right">
        <Search v-if="!isAdminRoute()" class="search-bar" />
        <nav class="user-nav">
          <template v-if="!authStore.isAuthenticated">
            <button @click="isRegisterOpen = true" class="auth-btn">Регистрация</button>
            <button @click="isLoginOpen = true" class="auth-btn">Вход</button>
          </template>
          <template v-else>
            <router-link v-if="authStore.isAdmin" to="/admin" class="nav-link admin-link">Админ</router-link>
            <router-link v-else :to="`/profile/${authStore.user?.id}`" class="avatar-link">
              <img v-if="authStore.avatar" :src="authStore.avatar" alt="Аватар" class="user-avatar" />
              <BaseIcon v-else name="user" class="default-avatar" />
            </router-link>
            <button @click="handleLogout" class="btn-icon logout-btn" aria-label="Выйти">
              <BaseIcon viewBox="0 0 65 65" class="icon-white-55-2" name="logout" />
            </button>
          </template>
        </nav>
      </div>
    </div>

    <div class="sidebar-overlay" :class="{ open: isMenuOpen }" @click="toggleMenu">
      <div class="sidebar" :class="{ open: isMenuOpen }" @click.stop>
        <div class="sidebar-header">
          <button class="btn-icon burger-btn" @click="toggleMenu" aria-label="Закрыть">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-75-2" name="close" />
          </button>
          <router-link :to="isAdminRoute() ? '/admin' : '/'" class="logo-link" @click="toggleMenu">
            <BaseIcon class="logo" name="logo" /><h1>Рецептище</h1>
          </router-link>
        </div>
        <div class="sidebar-content">
          <nav class="sidebar-nav">
            <router-link v-if="isAdminRoute()" to="/" @click="toggleMenu" class="nav-link">На сайт</router-link>
            <router-link v-else to="/search/recipe" @click="toggleMenu" class="nav-link">Рецепты</router-link>
            <router-link v-else to="/search/collection" @click="toggleMenu" class="nav-link">Коллекции</router-link>
            <router-link v-else to="/search/user" @click="toggleMenu" class="nav-link">Пользователи</router-link>
          </nav>
          <div class="sidebar-auth">
            <button v-if="!authStore.isAuthenticated" @click="isRegisterOpen = true; toggleMenu()" class="btn-dark">Регистрация</button>
            <button v-if="!authStore.isAuthenticated" @click="isLoginOpen = true; toggleMenu()" class="btn-dark">Вход</button>
            <router-link v-if="authStore.isAuthenticated" :to="`/profile/${authStore.user?.id}`" @click="toggleMenu" class="nav-link user-profile-link">
              <div class="user-profile">
                <img v-if="authStore.avatar" :src="authStore.avatar" alt="Аватар" class="sidebar-avatar" />
                <BaseIcon v-else name="user" class="sidebar-default-avatar" />
                <span>Мой профиль</span>
              </div>
            </router-link>
            <button v-if="authStore.isAuthenticated" @click="handleLogout; toggleMenu()" class="btn-dark logout-btn">Выйти</button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style lang="scss" scoped>
@use "../assets/styles/_variables.scss" as *;

header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 5rem;
  background-color: $background-dark;
  color: $light-text;
  box-shadow: $shadow;
  z-index: 100;

  .header-grid {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    max-width: 75rem;
    margin: 0 auto;
    padding: 0 1.5rem;
    height: 100%;
    gap: 1rem;

    @media (max-width: 1200px) {
      padding: 0 1rem;
      gap: 0.5rem;
    }

    @media (max-width: 768px) {
      padding: 0 0.75rem;
    }
  }

  .header-left {
    display: flex;
    align-items: center;
    gap: 1rem;

    .burger-btn {
      display: none;

      @media (max-width: 992px) {
        display: flex;
      }
    }

    .logo-link {
      display: flex;
      align-items: center;
    }
  }

  .main-nav {
    display: flex;
    align-items: center;
    gap: 2rem;
    justify-self: start;

    .nav-link {
      padding: 1rem 0;
      color: $light-text;
      text-decoration: none;
      font-weight: 500;
      white-space: nowrap;

      &:hover {
        color: $accent-color-1;
      }
    }

    @media (max-width: 992px) {
      display: none;
    }
  }

  .header-right {
    display: flex;
    align-items: center;
    gap: clamp(0.5rem, 2vw, 1.5rem);
    justify-self: end;

    @media (max-width: 1200px) {
      gap: 0.75rem;
    }

    @media (max-width: 992px) {
      gap: 0.5rem;
      width: 100%;
      justify-content: space-between;
    }

    @media (max-width: 768px) {
      gap: 0.25rem;
      .search-bar {
        display: none;
      }
    }

    .search-bar {
      flex: 1 1 auto; // Растяжение с возможностью сжатия
      max-width: 20rem;

      @media (max-width: 1200px) {
        max-width: 15rem;
      }

      @media (max-width: 992px) {
        max-width: none; // Убираем ограничение для растяжения
      }
    }

    .user-nav {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-shrink: 0;

      @media (max-width: 992px) {
        gap: 0.5rem;
      }

      @media (max-width: 768px) {
        gap: 0.25rem;
      }

      .auth-btn {
        background: none;
        border: none;
        color: $light-text;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        padding: 0 0.5rem;

        &:hover {
          color: $accent-color-1;
        }
      }

      .admin-link {
        .nav-link {
          color: $light-text;
          font-weight: 500;
        }
      }

      .avatar-link {
        display: flex;
        align-items: center;

        .user-avatar {
          width: 2rem;
          height: 2rem;
          border-radius: 50%;
          object-fit: cover;

          @media (max-width: 768px) {
            width: 1.75rem;
            height: 1.75rem;
          }
        }

        .default-avatar {
          width: 2rem;
          height: 2rem;
          fill: $light-text;

          @media (max-width: 768px) {
            width: 1.75rem;
            height: 1.75rem;
          }
        }
      }

      .logout-btn {
        margin-left: 0.25rem;
      }
    }
  }

  .sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;

    @media (min-width: 992px) {
      display: none;
    }

    &.open {
      opacity: 1;
      visibility: visible;
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: -100%;
      max-width: 22rem;
      background: $background;
      min-height: 100%;
      transition: left 0.3s ease;
      display: flex;
      flex-direction: column;

      &.open {
        left: 0;
      }

      .sidebar-header {
        display: flex;
        align-items: center;
        padding: 0.5rem;
        border-bottom: 1px solid $light;
      }

      .sidebar-content {
        padding: 0 1.5rem 1.5rem;
        overflow-y: auto;
      }

      .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;

        .nav-link {
          text-decoration: none;
          border-bottom: 1px solid $light;
          color: $dark-text;
          font-weight: 500;
          padding: 0.5rem 0;
        }
      }

      .sidebar-auth {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid $light;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;

        .user-profile {
          display: flex;
          align-items: center;
          gap: 0.75rem;

          .sidebar-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            object-fit: cover;
          }

          .sidebar-default-avatar {
            width: 2.5rem;
            height: 2.5rem;
            fill: $dark-text;
          }
        }
      }
    }
  }

  .login-modal,
  .register-modal {
    position: fixed;
    z-index: 1001;
  }
}
</style>