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

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const handleClickOutside = (event) => {
  const isLoginModal = event.target.closest(".login-modal");
  const isRegisterModal = event.target.closest(".register-modal");
  
  if (isMenuOpen.value && 
      !event.target.closest(".sidebar") && 
      !event.target.closest(".burger-btn") &&
      !isLoginModal &&
      !isRegisterModal) {
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
      <!-- Лого и бургер -->
      <div class="header-left">
        <button class="btn-icon burger-btn" @click="toggleMenu" aria-label="Меню">
          <BaseIcon viewBox="0 0 65 65" class="icon-white-55-2" name="burger" />
        </button>
        <router-link :to="isAdminRoute() ? '/admin' : '/'" class="logo-link">
          <BaseIcon class="logo" name="logo" />
        </router-link>
      </div>
      
      <!-- Основные ссылки -->
      <nav class="main-nav">
        <template v-if="isAdminRoute()">
          <router-link to="/" class="nav-link">
            <div class="label-item">На сайт</div>
          </router-link>
        </template>
        <template v-else>
          <router-link to="/search/recipe" class="nav-link">
            <div class="label-item">Рецепты</div>
          </router-link>
          <router-link to="/search/collection" class="nav-link">
            <div class="label-item">Коллекции</div>
          </router-link>
          <router-link to="/search/user" class="nav-link">
            <div class="label-item">Пользователи</div>
          </router-link>
        </template>
      </nav>
      
      <!-- Поиск и профиль -->
      <div class="header-right">
        <Search v-if="!isAdminRoute()" class="search-bar" />
        
        <nav class="user-nav">
          <template v-if="!authStore.isAuthenticated">
            <button class="label-item auth-btn" @click="isRegisterOpen = true">Регистрация</button>
            <button class="label-item auth-btn" @click="isLoginOpen = true">Вход</button>
          </template>
          <template v-else>
            <template v-if="authStore.isAdmin">
              <router-link to="/admin" class="nav-link admin-link">
                <div class="label-item">Админ</div>
              </router-link>
            </template>
            <template v-else>
              <router-link :to="`/profile/${authStore.user?.id}`" class="avatar-link">
                <img :src="authStore.avatar" alt="Аватар" class="user-avatar" v-if="authStore.avatar" />
                <BaseIcon v-else name="user" class="default-avatar" />
              </router-link>
            </template>
            <button class="btn-icon logout-btn" @click="handleLogout" aria-label="Выйти">
              <BaseIcon viewBox="0 0 65 65" class="icon-white-55-2" name="logout" />
            </button>
          </template>
        </nav>
      </div>
    </div>
    
    <!-- Мобильное меню -->
    <div class="sidebar-overlay" :class="{ 'open': isMenuOpen }" @click="toggleMenu">
      <div class="sidebar" :class="{ 'open': isMenuOpen }" @click.stop>
        <div class="sidebar-header">
          <router-link :to="isAdminRoute() ? '/admin' : '/'" class="logo-link" @click="toggleMenu">
            <BaseIcon class="logo" name="logo" />
            <h1>Рецептище</h1>
          </router-link>
          <button class="btn-icon close-btn" @click="toggleMenu" aria-label="Закрыть">
            <BaseIcon viewBox="0 0 24 24" class="icon-dark-24-2" name="close" />
          </button>
        </div>
        
        <div class="sidebar-content">
          <nav class="sidebar-nav">
            <template v-if="isAdminRoute()">
              <router-link to="/" class="nav-link" @click="toggleMenu">
                <div class="label-item">На сайт</div>
              </router-link>
            </template>
            <template v-else>
              <router-link to="/search/recipe" class="nav-link" @click="toggleMenu">
                <div class="label-item">Рецепты</div>
              </router-link>
              <router-link to="/search/collection" class="nav-link" @click="toggleMenu">
                <div class="label-item">Коллекции</div>
              </router-link>
              <router-link to="/search/user" class="nav-link" @click="toggleMenu">
                <div class="label-item">Пользователи</div>
              </router-link>
            </template>
          </nav>
          
          <div class="sidebar-auth">
            <template v-if="!authStore.isAuthenticated">
              <button class="btn-dark" @click="isRegisterOpen = true; toggleMenu()">Регистрация</button>
              <button class="btn-dark" @click="isLoginOpen = true; toggleMenu()">Вход</button>
            </template>
            <template v-else>
              <router-link :to="`/profile/${authStore.user?.id}`" class="nav-link user-profile-link" @click="toggleMenu">
                <div class="user-profile">
                  <img :src="authStore.avatar" alt="Аватар" class="sidebar-avatar" v-if="authStore.avatar" />
                  <BaseIcon v-else name="user" class="sidebar-default-avatar" />
                  <span class="label-item">Мой профиль</span>
                </div>
              </router-link>
              <button class="btn-dark logout-btn" @click="handleLogout; toggleMenu()">
                Выйти
              </button>
            </template>
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
  z-index: 1000;
  
  .header-grid {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    width: 100%;
    max-width: 75rem;
    height: 100%;
    margin: 0 auto;
    padding: 0 1.5rem;
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
    
    .logo-link {
      display: flex;
      align-items: center;
    }
    
    .burger-btn {
      display: none;
      
      @media (max-width: 992px) {
        display: flex;
      }
    }
  }
  
  .main-nav {
    display: flex;
    align-items: center;
    gap: 2rem;
    justify-self: start;
    
    @media (max-width: 992px) {
      display: none;
    }
    
    .nav-link {
      text-decoration: none;
      
      .label-item {
        color: $light-text;
        font-size: 1rem;
        font-weight: 500;
        transition: color 0.2s;
        white-space: nowrap;
        
        &:hover {
          color: $accent-color-1;
        }
      }
    }
  }
  
  .header-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    justify-self: end;
    
    @media (max-width: 992px) {
      gap: 0.75rem;
    }
    
    .search-bar {
      width: 20rem;
      
      @media (max-width: 1200px) {
        width: 15rem;
      }
      
      @media (max-width: 992px) {
        display: none;
      }
    }
    
    .user-nav {
      display: flex;
      align-items: center;
      gap: 1rem;
      
      .auth-btn {
        background: none;
        border: none;
        color: $light-text;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        
        &:hover {
          color: $accent-color-1;
        }
      }
      
      .admin-link {
        .label-item {
          color: $light-text;
          font-weight: 500;
        }
      }
      
      .avatar-link {
        display: flex;
        align-items: center;
        
        .user-avatar {
          width: 2.5rem;
          height: 2.5rem;
          border-radius: 50%;
          object-fit: cover;
          
          @media (max-width: 768px) {
            width: 2rem;
            height: 2rem;
          }
        }
        
        .default-avatar {
          width: 2rem;
          height: 2rem;
          fill: $light-text;
        }
      }
      
      .logout-btn {
        margin-left: 0.5rem;
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
    
    &.open {
      opacity: 1;
      visibility: visible;
    }
    
    .sidebar {
      position: fixed;
      top: 0;
      left: -100%;
      width: 85%;
      max-width: 22rem;
      height: 100vh;
      background: $background;
      transition: left 0.3s ease;
      display: flex;
      flex-direction: column;
      overflow: hidden;

      .logo-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        
        h1 {
          font-weight: 500;
          color: $dark-text;
          margin: 0;
          font-size: 1.25rem;
        }
      }
      
      &.open {
        left: 0;
      }
      
      .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid $light;
        flex-shrink: 0;
        
        .close-btn {
          margin-left: auto;
        }
      }
      
      .sidebar-content {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow-y: auto;
      }
      
      .sidebar-nav {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex-grow: 1;
        
        .nav-link {
          text-decoration: none;
          padding: 0.75rem 0;
          border-bottom: 1px solid $light;
          
          .label-item {
            color: $dark-text;
            font-weight: 500;
            font-size: 1.125rem;
          }
        }
      }
      
      .sidebar-auth {
        padding: 1rem;
        border-top: 1px solid $light;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex-shrink: 0;
        
        button {
          width: 100%;
          padding: 0.75rem;
          border-radius: $border;
          font-weight: 500;
          text-align: center;
          background: $background-dark;
          color: $light-text;
          border: none;
          cursor: pointer;
          transition: background-color 0.2s;
          
          &:hover {
            background-color: lighten($background-dark, 10%);
          }
        }
        
        .user-profile {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          margin-bottom: 1rem;
          
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
}

/* Стили для модальных окон */
.login-modal,
.register-modal {
  position: fixed;
  z-index: 1001; /* Выше чем хедер */
}
</style>