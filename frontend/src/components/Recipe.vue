<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useAuthStore } from "../stores/auth";
import BaseIcon from "./BaseIcon.vue";
import ReactionButton from "./ReactionButton.vue";
import SaveRecipe from "./SaveRecipe.vue";

const isMenuVisible = ref(false);
const isSaveRecipeOpen = ref(false);
const menuRef = ref(null);
const authStore = useAuthStore();

const { recipe } = defineProps({
  recipe: {
    type: Object,
    required: true,
  },
});

onMounted(() => {
  console.log("RecipeCard mounted, recipe:", recipe);
  console.log("authStore:", authStore.isAuthenticated, authStore.userId);
});

const toggleMenu = () => {
  isMenuVisible.value = !isMenuVisible.value;
};

const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    isMenuVisible.value = false;
  }
};

const isAuthor = computed(() => {
  const result =
    authStore.isAuthenticated &&
    authStore.userId &&
    recipe &&
    authStore.userId === recipe.user_id;
  console.log(
    "isAuthor:",
    result,
    "userId:",
    authStore.userId,
    "recipe.user_id:",
    recipe?.user_id
  );
  return result;
});

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
  <SaveRecipe
    :isOpen="isSaveRecipeOpen"
    :recipe_id="recipe?.id"
    @close="isSaveRecipeOpen = false"
  />
  <section class="card recipe" v-if="recipe && recipe.id">
    <router-link :to="`/recipe/${recipe.id}`" class="card__preview">
      <img :src="recipe.photo" alt="Recipe photo" width="300" height="200" />
    </router-link>
    <div class="card__info">
      <div class="card__title">
        <router-link :to="`/profile/${recipe.user.id}`">
          <img
            :src="recipe.user.avatar"
            alt="User avatar"
            width="40"
            height="40"
          />
        </router-link>
        <div class="card__text">
          <h3>
            <router-link :to="`/recipe/${recipe.id}`">{{ recipe.title }}</router-link>
          </h3>
          <router-link :to="`/profile/${recipe.user.id}`" class="card__author">
            {{ recipe.user.login }}
          </router-link>
          <div class="card__metadata">
            <span class="date">{{ recipe.created_at }}</span>
            <span class="marker">•</span>
            <span class="reaction">
              <BaseIcon viewBox="0 0 12 12" class="icon-light-12-1" name="bookmark" />
              <span class="reaction__count">{{ recipe.saved }}</span>
            </span>
            <span class="marker">•</span>
            <span class="reaction">
              <BaseIcon viewBox="0 0 13 13" class="icon-light-13-1" name="heart" />
              <span class="reaction__count">{{ recipe.likes }}</span>
            </span>
          </div>
        </div>
      </div>
      <div class="btn-menu-container" ref="menuRef">
        <button type="button" @click="toggleMenu">
          <BaseIcon viewBox="0 0 40 40" class="icon-dark-45-0" name="menu" />
        </button>
        <div class="btn-popup" v-if="isMenuVisible">
          <ReactionButton
            entity-type="recipe"
            :entity-id="recipe.id"
            :count="recipe.likesCount || 0"
            variant="menu"
          />
          <button class="btn-item" @click="isSaveRecipeOpen = true">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-1" name="bookmarkb" />
            В коллекцию
          </button>
          <router-link
            v-if="isAuthor"
            class="btn-item"
            :to="`/recipe/edit/${recipe.id}`"
          >
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-1" name="edit" />
            Редактировать
          </router-link>
          <button
            v-if="isAuthor"
            class="btn-item"
            @click="$emit('delete', recipe.id)"
          >
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-1" name="close" />
            Удалить
          </button>
        </div>
      </div>
    </div>
  </section>
  <div v-else>Рецепт не загружен</div>
</template>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;
@use "../assets/styles/cards.scss" as *;
// Стили оставляем пустыми, они в cards.scss
</style>