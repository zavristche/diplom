<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import { useRecipeStore } from './stores/recipe';
import { useCollectionStore } from './stores/collection';
import { useProfileStore } from './stores/profile';

const router = useRouter();
const isReady = ref(false);
const isLoading = ref(true);

const recipeStore = useRecipeStore();
const collectionStore = useCollectionStore();
const profileStore = useProfileStore();

const hasPendingRequests = () => {
  const pending =
    (recipeStore.pendingRequests?.size > 0 || false) ||
    (collectionStore.pendingRequests?.size > 0 || false) ||
    (profileStore.pendingRequests?.size > 0 || false);
  console.log('hasPendingRequests:', pending);
  console.log('recipeStore.pendingRequests:', recipeStore.pendingRequests);
  console.log('collectionStore.pendingRequests:', collectionStore.pendingRequests);
  console.log('profileStore.pendingRequests:', profileStore.pendingRequests);
  return pending;
};

router.beforeEach((to, from, next) => {
  if (to.name === 'not-found' || to.name === 'admin') {
    isLoading.value = false;
    return next();
  }

  const routeConfig = {
    home: { store: recipeStore, fetch: 'fetchRecipes', field: 'recipes' },
    search: { store: recipeStore, fetch: 'searchRecipes', field: 'recipes' },
    RecipeView: { store: recipeStore, fetch: 'fetchRecipeById', field: 'currentRecipe' },
    ProfileView: { store: profileStore, fetch: 'fetchProfileById', field: 'currentProfile' },
    'recipe-create': { store: recipeStore, fetch: 'fetchCreateData', field: 'createData' },
    'recipe-edit': { store: recipeStore, fetch: 'fetchRecipeById', field: 'currentRecipe' },
    collection: { store: collectionStore, fetch: 'fetchCollectionById', field: 'currentCollection' },
    'collection-create': { store: collectionStore, fetch: 'fetchCreateData', field: 'createData' },
    'collection-edit': { store: collectionStore, fetch: 'fetchCollectionById', field: 'currentCollection' },
  };

  const config = routeConfig[to.name];
  if (config && (to.params.id || to.name.includes('create') || to.name === 'home' || to.name === 'search')) {
    const store = config.store;
    const currentItem = store[config.field];
    const cacheKey = config.fetch === 'fetchRecipeById' || config.fetch === 'fetchCollectionById' || config.fetch === 'fetchProfileById'
      ? `${config.fetch.split('ById')[0].toLowerCase()}_${to.params.id}`
      : config.fetch === 'searchRecipes'
        ? JSON.stringify(to.query)
        : config.fetch;

    const cacheTTL = 5 * 60 * 1000;
    const now = Date.now();

    if (
      (Array.isArray(currentItem) && currentItem.length) ||
      (currentItem?.id === to.params.id) ||
      (store.cacheTimestamps?.[cacheKey] && now - store.cacheTimestamps[cacheKey] < cacheTTL)
    ) {
      isLoading.value = false;
    } else {
      isLoading.value = true;
    }
  } else {
    isLoading.value = false;
  }

  next();
});

router.afterEach(() => {
  let timeoutReached = false;
  const timeout = setTimeout(() => {
    timeoutReached = true;
    isLoading.value = false;
    console.warn('Timeout reached: Forcing isLoading to false');
  }, 5000);

  const checkPending = () => {
    if (timeoutReached) return;
    if (!hasPendingRequests()) {
      clearTimeout(timeout);
      isLoading.value = false;
    } else {
      setTimeout(checkPending, 100);
    }
  };
  checkPending();
});

onMounted(async () => {
  isLoading.value = true;
  if (router.currentRoute.value.name === 'not-found') {
    isReady.value = true;
    isLoading.value = false;
    return;
  }

  try {
    await new Promise(resolve => setTimeout(resolve, 100));
    let timeoutReached = false;
    const timeout = setTimeout(() => {
      timeoutReached = true;
      isLoading.value = false;
      console.warn('Timeout reached on mount: Forcing isLoading to false');
    }, 5000);

    const checkPending = () => {
      if (timeoutReached) return;
      if (!hasPendingRequests()) {
        clearTimeout(timeout);
        isLoading.value = false;
        resolve();
      } else {
        setTimeout(checkPending, 100);
      }
    };
    checkPending();

    isReady.value = true;
  } catch (error) {
    console.error('App.vue: Error during initialization:', error);
    isReady.value = true;
    isLoading.value = false;
  }
});

watch(isLoading, (newValue) => {
  console.log('isLoading:', newValue);
});
</script>

<template>
  <div class="app-container">
    <div v-if="!isReady" class="loading-overlay">
      <div class="loader"></div>
    </div>

    <router-view v-else v-slot="{ Component }">
      <transition name="fade-slide" mode="out-in">
        <div class="content-wrapper">
          <Header />
          <main class="main-content">
            <div v-if="isLoading" class="content-grid">
              <div v-for="n in 6" :key="n" class="skeleton-card"></div>
            </div>
            <transition name="fade" appear>
              <component v-if="!isLoading" :is="Component" />
            </transition>
          </main>
          <Footer />
        </div>
      </transition>
    </router-view>
  </div>
</template>

<style scoped lang="scss">
@use "./assets/styles/variables" as *;
@use "./assets/styles/style" as *;

.app-container {
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
  background-color: $background;
}

.content-wrapper {
  display: flex;
  flex-direction: column;
  flex: 1 0 auto; // Растягиваем содержимое
  width: 100%;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  transition: opacity 0.3s ease;

  .loader {
    width: 3rem;
    height: 3rem;
    border: 0.25rem solid $light-text;
    border-top: 0.25rem solid $accent-color-1;
    border-radius: 50%;
    animation: spin 1s linear infinite;

    @media (max-width: 768px) {
      width: 2rem;
      height: 2rem;
      border-width: 0.2rem;
    }
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.main-content {
  flex: 1 0 auto;
  width: 100%;
  max-width: 75rem; // 1200px
  margin: 0 auto;
  padding: 8rem 2rem 2rem; // Верхний отступ для шапки + нижний отступ
  
  // Отступы между элементами внутри main
  > * {
    margin-bottom: 2rem; // Базовый отступ между блоками
    
    &:last-child {
      margin-bottom: 0; // Убираем отступ у последнего элемента
    }
  }

  // Адаптивные отступы
  @media (max-width: 1200px) {
    padding: 8rem 1.5rem 1.5rem;
    > * {
      margin-bottom: 1.5rem;
    }
  }

  @media (max-width: 768px) {
    padding: 8rem 1rem 1rem;
    > * {
      margin-bottom: 1rem;
    }
  }

  @media (max-width: 480px) {
    padding: 8rem 0.5rem 0.5rem;
    > * {
      margin-bottom: 0.75rem;
    }
  }
}
content-grid {
  display: grid;
  width: 100%;
  gap: 1.25rem;
  grid-template-columns: repeat(auto-fit, minmax(18.75rem, 1fr)); // 300px

  @media (max-width: 1200px) {
    grid-template-columns: repeat(auto-fit, minmax(15.625rem, 1fr)); // 250px
    gap: 1rem;
  }

  @media (max-width: 768px) {
    grid-template-columns: repeat(auto-fit, minmax(12.5rem, 1fr)); // 200px
    gap: 0.75rem;
  }

  @media (max-width: 480px) {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
}

.skeleton-card {
  height: 17.5rem; // 280px
  background: $light;
  border-radius: $border;
  animation: pulse 1.5s infinite;
  box-shadow: $shadow;

  @media (max-width: 1200px) {
    height: 15rem; // 240px
  }

  @media (max-width: 768px) {
    height: 12.5rem; // 200px
  }

  @media (max-width: 480px) {
    height: 10rem; // 160px
  }
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

/* Плавное появление + сдвиг */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(0.625rem); // 10px
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-0.625rem); // -10px
}

/* Плавное появление контента */
.fade-enter-active {
  transition: opacity 0.5s ease 0.2s;
}

.fade-enter-from {
  opacity: 0;
}
</style>