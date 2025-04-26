<script setup>
import { ref, onMounted } from "vue";
import { useRecipeStore } from "../stores/recipe"; // Создаём хранилище
import BaseIcon from "../components/BaseIcon.vue";
import Recipe from "../components/Recipe.vue";

// Используем Pinia для хранения данных
const recipeStore = useRecipeStore();
const recipes = ref(recipeStore.recipes); // Берем из кэша
const isLoading = ref(!recipes.value.length); // Если данных нет, показываем загрузку

onMounted(async () => {
  if (!recipes.value.length) {
    // Загружаем данные, если кэш пуст
    await recipeStore.fetchRecipes();
    recipes.value = recipeStore.recipes;
  }
  isLoading.value = false; // Скрываем загрузку
});
</script>

<template>
  <section class="hero">
    <div class="container-col">
      <h1>РЕЦЕПТИЩЕ</h1>
      <p class="slogan">Готовь просто – удивляй вкусно!</p>
      <div class="search">
        <div class="btn icon">
          <BaseIcon class="icon-dark-45-1" viewBox="0 0 45 45" name="search" />
        </div>
        <input type="text" id="search" placeholder="Поиск" />
        <div class="btn-container">
          <button type="submit" id="btn-search" class="btn-salat">Найти</button>
        </div>
      </div>
      <button type="submit" class="btn-dark line">
        <BaseIcon viewBox="0 0 45 45" class="icon-dark-45-1" name="random" />Случайный рецепт
      </button>
    </div>
    <img src="/img/mascot.png" alt="Mascot" width="600" height="760" />
  </section>
  <section class="content-container">
    <div v-if="isLoading" class="skeleton-container">
      <div v-for="n in 6" :key="n" class="skeleton-card"></div>
    </div>
    <Recipe v-else v-for="recipe in recipes" :key="recipe.id" :recipe="recipe" />
  </section>
</template>

<style lang="scss">
@use "../assets/styles/variables" as *;

.content-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}

.skeleton-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}

.skeleton-card {
  height: 280px; /* Соответствует высоте карточки */
  background: #eee;
  border-radius: 8px;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.hero {
  display: flex;
  align-items: center;

  h1 {  
    font-weight: 600;
    font-size: 100px;
  }

  .search {
    box-shadow: $shadow;
  }

  .container-col {
    height: 19rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  img {
    width: 524px;
    height: 580px;
    object-fit: cover;
  }
}

.slogan {
  font-size: 32px;
}
</style>