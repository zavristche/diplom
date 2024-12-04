<script setup>
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";
</script>

<template>
  <body>
    <Header />
    <main>
      <router-view />
    </main>
    <Footer />
  </body>
</template>

<style scoped></style>
