<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useRecipeStore } from './stores/recipe';
import { useCollectionStore } from './stores/collection';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';

const router = useRouter();
const recipeStore = useRecipeStore();
const collectionStore = useCollectionStore();
const isReady = ref(false);

onMounted(async () => {
  try {
    await Promise.all([
      router.isReady(),
      recipeStore.fetchCreateData().catch(error => {
        console.error('App.vue: Error fetching recipe createData:', error);
      }),
      collectionStore.fetchCreateData().catch(error => {
        console.error('App.vue: Error fetching collection createData:', error);
      }),
    ]);
    isReady.value = true;
  } catch (error) {
    console.error('App.vue: Error during initialization:', error);
    isReady.value = true;
  }
});
</script>

<template>
  <div class="app-container">
    <div v-if="!isReady" class="loading-overlay">
      <p>Загрузка...</p>
    </div>
    <router-view v-else v-slot="{ Component }">
      <transition name="fade">
        <div>
          <Header />
          <main>
            <component :is="Component" />
          </main>
          <Footer />
        </div>
      </transition>
    </router-view>
  </div>
</template>

<style scoped lang="scss">
@use "./assets/styles/_variables" as *;

.app-container {
  width: 100vw;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
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
  font-size: 24px;
  color: #fff;
}

main {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 50px;
  justify-content: center;
  width: 1200px;
  max-width: 1200px;
  margin: 50px auto 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>