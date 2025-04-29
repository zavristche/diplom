<script setup>
import { onMounted, onUnmounted } from "vue";
import lottie from "lottie-web";
import loadingAnimation from "@/assets/lottie/loading.json"; // Путь к твоей Lottie-анимации

let animation = null;

onMounted(() => {
  animation = lottie.loadAnimation({
    container: document.getElementById("lottie-loading"),
    renderer: "svg",
    loop: true,
    autoplay: true,
    animationData: loadingAnimation,
  });
});

onUnmounted(() => {
  if (animation) {
    animation.destroy();
    animation = null;
  }
});
</script>

<template>
  <div class="loading-overlay">
    <div id="lottie-loading" class="lottie-container"></div>
  </div>
</template>

<style scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: white;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.lottie-container {
  width: 200px; /* Размер анимации, настрой под себя */
  height: 200px;
}
</style>