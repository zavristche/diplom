<script setup>
import { useRoute } from 'vue-router';
import Recipe from '../../components/Recipe.vue';

const route = useRoute();
const myRecipe = route.meta.recipe; // Получаем данные из meta
</script>

<template>
  <section class="content-container">
    <title>{{ myRecipe?.title }}</title> <!-- Используем опциональную цепочку -->
    <Recipe v-if="myRecipe" :recipe="myRecipe" />
    <p v-else>Рецепт не найден</p>
  </section>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/style";

.content-container {
  display: grid;
  width: 100%;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}
</style>