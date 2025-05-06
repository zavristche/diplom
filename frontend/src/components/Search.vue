<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useRecipeStore } from '../stores/recipe.js';
import BaseIcon from './BaseIcon.vue';

const authStore = useAuthStore();
const recipeStore = useRecipeStore();
const router = useRouter();
const searchQuery = ref('');

function handleSearch() {
  if (searchQuery.value.trim()) {
    recipeStore.searchRecipes({ title: searchQuery.value.trim() })
      .then(() => {
        // Перенаправляем на /search/recipe с параметром title
        router.push(`/search/recipe?title=${encodeURIComponent(searchQuery.value.trim())}`);
      })
      .catch((error) => {
        console.error('Ошибка при поиске рецептов:', error);
      });
  }
}
</script>

<template>
  <div class="search">
    <div class="btn icon">
      <BaseIcon class="icon-dark-45-1" viewBox="0 0 45 45" name="search" />
    </div>
    <input
      type="text"
      id="search"
      placeholder="Поиск"
      v-model="searchQuery"
      @keyup.enter="handleSearch"
    />
    <div class="btn-container">
      <button type="submit" id="btn-search" class="btn-salat" @click="handleSearch">Найти</button>
    </div>
  </div>
</template>

<style lang="scss" scoped>
</style>