<!-- Search.vue -->
<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useRecipeStore } from '../stores/recipe.js';
import BaseIcon from './BaseIcon.vue';

const recipeStore = useRecipeStore();
const router = useRouter();
const searchQuery = ref('');

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    recipeStore.searchRecipes({ title: searchQuery.value.trim() })
      .then(() => {
        router.push(`/search/recipe?title=${encodeURIComponent(searchQuery.value.trim())}`);
      })
      .catch(console.error);
  }
};
</script>

<template>
  <div class="search-container">
    <div class="search-input">
      <BaseIcon class="icon-dark-45-1" viewBox="0 0 45 45" name="search" />
      <input
        type="text"
        placeholder="Поиск рецептов..."
        v-model="searchQuery"
        @keyup.enter="handleSearch"
      />
    </div>
    <button class="btn-search" @click="handleSearch">
      Найти
    </button>
  </div>
</template>

<style lang="scss" scoped>
@use "../assets/styles/_variables.scss" as *;

.search-container {
  display: flex;
  align-items: center;
  width: 100%;
  height: 2.5rem;
  border-radius: $border;
  overflow: hidden;
  background: $background;
  
  .search-input {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 0 0.75rem;
    height: 100%;
    
    input {
      flex: 1;
      height: 100%;
      border: none;
      outline: none;
      background: transparent;
      padding-left: 0.5rem;
      font-size: 0.875rem;
      
      &::placeholder {
        color: $text-info-light;
      }
    }
  }
  
  .btn-search {
    height: 100%;
    padding: 0 1rem;
    background: $accent-color-1;
    color: $dark-text;
    border: none;
    font-weight: 400;
    cursor: pointer;
    transition: background 0.2s;
    
    &:hover {
      background: darken($accent-color-1, 5%);
    }
  }
}
</style>