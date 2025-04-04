<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import BaseIcon from "../components/BaseIcon.vue";
import Recipe from "../components/Recipe.vue";
import RecipeService from '../api/RecipeService';

const myRecipes = ref([]);

const route = useRoute();
const recipes = route.meta.data;
console.log(recipes);

</script>
<template>
  <section class="hero">
    <div class="container-col">
      <h1>РЕЦЕПТИЩЕ</h1>
      <p class="slogan">Готовь просто – удивляй вкусно! </p>
      <div class="search">
        <div class="btn icon">
          <BaseIcon class="icon-dark-45-1" viewBox="0 0 45 45" name="search"/>
        </div>
        <input
          type="text"
          id="search"
          placeholder="Поиск"
        />
        <div class="btn-container">
          <button type="submit" id="btn-search" class="btn-salat">Найти</button>
        </div>
      </div>
      <button type="submit" id="" class="btn-dark line"><BaseIcon viewBox="0 0 45 45" class="icon-dark-45-1" name="random" />Случайный рецепт</button>
    </div>
    <img src="/img/mascot.png" alt="Test Image">
  </section>
  <section class="content-container">
    <Recipe
      v-for="recipe in recipes"
      :key="recipe.id"
      :recipe="recipe"
    />
  </section>
</template>

<style lang="scss">
@use "../assets/styles/variables" as *;
@use "../assets/styles/style";

.content-container {
  display: grid;
  width: 100%;
  grid-template-columns: repeat(3, 1fr); // Три равные колонки
  gap: 40px; // Расстояние между карточками
  // margin-top: 50px;
}

.hero {
  display: flex;
  flex-direction: row;
  align-items: center;
  width: 100%;
  // height: 36rem;
  align-content: center;
  // background-color: $accent-color-2;

  h1 {
    font-weight: 600;
    font-size: 100px;
  }

  .search {
    box-shadow: $shadow;
  }

  .container-col {
    justify-content: space-between;
    height: 19rem;
  }
}

.slogan {
  font-weight: 300;
  font-size: 32px;
}

.mascot {
  display: flex;
  width: 100%;
  height: 38rem;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}
</style>