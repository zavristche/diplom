<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';

const router = useRouter();
const isReady = ref(false);

onMounted(async () => {
  console.log('Starting router.isReady()');
  try {
    await router.isReady();
    console.log('router.isReady() completed');
    isReady.value = true;
  } catch (error) {
    console.error('Error in router.isReady():', error);
  }
});

// Следим за изменением маршрута и прокручиваем вверх резко
watch(
  () => router.currentRoute.value.path,
  () => {
    window.scrollTo({
      top: 0
      // behavior: 'smooth' - убираем этот параметр для резкой прокрутки
      // или явно указываем behavior: 'auto'
    });
  }
);
</script>

<template>
  <div class="app-container">
    <div v-if="!isReady" class="loading-overlay">
      <p>Загрузка...</p>
    </div>
    <div v-else>
      <Header />
      <main>
        <router-view />
      </main>
      <Footer />
    </div>
  </div>
</template>

<style scoped lang="scss">
@use "../src/assets/styles/_variables" as *;
@use "../src/assets/styles/normalize.scss";

.app-container {
  width: 100vw;
  position: relative;
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
</style>