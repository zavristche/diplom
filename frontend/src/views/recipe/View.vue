<script setup>
import { ref } from 'vue';
import BaseIcon from "../../components/BaseIcon.vue";
import Recipe from "../../components/Recipe.vue";
import RecipeService from '../../api/RecipeService';
import { useRoute } from 'vue-router';

const myRecipe = ref(null);
const route = useRoute();
RecipeService.getById(route.params.id).then((recipe) => {
    console.log('Recipe:', recipe);
    myRecipe.value = recipe.data.data;
}).catch((error) => {
    console.error('Error fetching recipe:', error);
});
</script>
<template>
  <section class="content-container">
    <Recipe v-if="myRecipe" :recipe="myRecipe" />
  </section>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/style";

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