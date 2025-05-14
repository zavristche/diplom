<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import RecipeService from "../api/RecipeService";
import { useRecipeStore } from "../stores/recipe";
import BaseIcon from "../components/BaseIcon.vue";
import Recipe from "../components/Recipe.vue";
import Search from "../components/Search.vue";

const router = useRouter();
const recipeStore = useRecipeStore();
const recipes = ref(recipeStore.recipes);
const isLoading = ref(!recipes.value.length);

onMounted(async () => {
  if (!recipes.value.length) {
    await recipeStore.fetchRecipes();
    recipes.value = recipeStore.recipes;
  }
  isLoading.value = false;
});

const getRandomRecipe = async () => {
  try {
    console.log("Starting getRandomRecipe...");
    console.log("Calling RecipeService.getRandom()...");
    const response = await RecipeService.getRandom();
    console.log("Random API response:", response.data);
    const recipeId = response.data.id;
    if (recipeId) {
      console.log("Navigating to /recipe/", recipeId);
      router.push(`/recipe/${recipeId}`);
    } else {
      console.log("No valid recipeId, redirecting to /not-found");
      router.push('/not-found');
    }
  } catch (error) {
    console.error("Error in getRandomRecipe:", error);
    console.error("Error details:", error.response?.data || error.message);
    router.push('/not-found');
  }
};
</script>

<template>
  <section class="hero">
    <div class="container-col">
      <h1>РЕЦЕПТИЩЕ</h1>
      <p class="slogan">Готовь просто – удивляй вкусно!</p>
      <Search class="search" />
      <button type="button" class="btn-dark line random-btn" @click="getRandomRecipe">
        <BaseIcon viewBox="0 0 45 45" class="icon-dark-45-1" name="random" />Случайный рецепт
      </button>
    </div>
    <img src="/img/mascot.png" alt="Mascot" class="mascot-img" />
  </section>
  <section class="content-grid">
    <div v-if="isLoading" v-for="n in 6" :key="n" class="skeleton-card"></div>
    <Recipe v-else v-for="recipe in recipes" :key="recipe.id" :recipe="recipe" />
  </section>
</template>

<style lang="scss">
@use "../assets/styles/variables" as *;

.hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  // padding: 0 1rem 2rem;
  flex-wrap: wrap;
  background-color: $background;

  @media (max-width: 768px) {
    flex-direction: column;
    text-align: center;
  }

  .container-col {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
    width: 50%;
    max-width: 37.5rem;

    @media (max-width: 768px) {
      width: 100%;
      align-items: center;
    }

    h1 {
      font-weight: 600;
      font-size: clamp(2.5rem, 5vw, 6.25rem);
    }

    .slogan {
      font-size: clamp(1.25rem, 3vw, 2rem);
    }

    .search {
      width: 80%;
      box-shadow: $shadow;

      @media (max-width: 1200px) {
        width: 100%;
      }
    }

  }

  .mascot-img {
    max-width: 40%;
    max-height: 80vh;
    object-fit: contain;
    width: auto;
    height: auto;

    @media (max-width: 1200px) {
      max-width: 30%;
    }

    @media (max-width: 768px) {
      max-width: 50%;
      max-height: 40vh;
      display: none;
    }
  }
}
</style>