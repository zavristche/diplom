<script setup>
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";
</script>

<template>
  <Header />
  <main><router-view /></main>
  <Footer />
</template>

<style scoped lang="scss">
@use "../src/assets/styles/_variables" as *;
@use "../src/assets/styles/normalize.scss";

main {
  // position: sticky;
  // z-index: -2;
  top: 6.25rem;
  display: flex;
  gap: 50px;
  flex-direction: column;
  justify-content: center;
  width: 1200px;
  max-width: 1200rem;
  min-width: 27.5rem;
  margin-top: 50px;
  // color: inherit;
}
</style>
