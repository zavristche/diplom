<script setup>
import { ref, onMounted } from "vue";
import { useRecipeStore } from "../../stores/recipe";
import Recipe from "../../components/RecipeAdmin.vue";
import Status from "../../components/StatusContent.vue";

const recipeStore = useRecipeStore();
const recipes = ref([]);
const isLoading = ref(true);

onMounted(async () => {
  try {
    isLoading.value = true;
    await recipeStore.fetchRecipes();
    recipes.value = recipeStore.recipes;
  } catch (error) {
    console.error("Admin.vue: Error fetching recipes:", error);
  } finally {
    isLoading.value = false;
  }
});

const updateRecipeList = async () => {
  try {
    isLoading.value = true;
    await recipeStore.fetchRecipes();
    recipes.value = recipeStore.recipes;
  } catch (error) {
    console.error("Admin.vue: Error updating recipes:", error);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <section class="admin-content">
    <h1>Управление рецептами</h1>
    <section class="admin-content">
      <div v-if="isLoading" v-for="n in 6" :key="n" class="skeleton-row"></div>
      <div v-else class="list-row">
        <Recipe
          v-for="recipe in recipes"
          :key="recipe.id"
          :recipe="recipe"
          @updateList="updateRecipeList"
        />
      </div>
    </section>
  </section>
</template>

<style scoped lang="scss">
@use "../../assets/styles/_variables" as *;

.admin-content {
  flex: 1;
  background-color: $background;
}

// .btn-group{
//   margin-top: 20px;
// }

.list-row {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 15px;
  border-radius: $border;
  height: 160px;
  animation: pulse 1.5s infinite;

  &::before {
    content: "";
    flex-shrink: 0;
    width: 100px;
    height: 100px;
    background-color: $text-info-light;
    border-radius: $border;
  }

  &::after {
    content: "";
    flex: 1;
    height: 80px;
    background-color: $text-info-light;
    border-radius: $border;
  }
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.6; }
  100% { opacity: 1; }
}
</style>