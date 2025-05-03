<script setup>
import { defineProps } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import BaseIcon from './BaseIcon.vue';
import recipeService from '../api/RecipeService';
import collectionService from '../api/CollectionService';

const props = defineProps({
  entityId: {
    type: [Number, String],
    required: true,
  },
  entityType: {
    type: String,
    required: true,
    validator: (value) => ['recipe', 'collection'].includes(value),
  },
});

const router = useRouter();
const authStore = useAuthStore();

const handleDelete = async () => {
  const isConfirmed = window.confirm(`Вы действительно хотите удалить ${props.entityType === 'recipe' ? 'рецепт' : 'коллекцию'}?`);
  if (!isConfirmed) return;

  try {
    if (props.entityType === 'recipe') {
      await recipeService.delete(props.entityId);
      console.log(`Recipe ${props.entityId} deleted successfully`);
    } else if (props.entityType === 'collection') {
      await collectionService.delete(props.entityId);
      console.log(`Collection ${props.entityId} deleted successfully`);
    }
    // Перенаправляем на профиль авторизированного пользователя
    if (authStore.user?.id) {
      router.push(`/profile/${authStore.user.id}`);
    } else {
      console.error('User ID not found, redirecting to /profile');
      router.push('/profile');
    }
  } catch (error) {
    console.error(`Error deleting ${props.entityType}:`, error);
    alert(`Ошибка при удалении ${props.entityType === 'recipe' ? 'рецепта' : 'коллекции'}. Попробуйте снова.`);
  }
};
</script>

<template>
  <button class="btn-orange" @click="handleDelete">
    <BaseIcon viewBox="0 0 29 29" class="icon-white-30-2" name="trash" />
    Удалить
  </button>
</template>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;

// Стиль для .btn-orange
.btn-orange {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 20px;
  justify-content: space-between;
  color: $light-text; // #fff
  background-color: $accent-color-2; // #faaf2c
  padding: 15px 20px;
  border-radius: $border; // 0.5rem
  font-weight: map-get($font-weather, "medium", "weight"); // 500
  font-size: map-get($font-weather, "medium", "size"); // 16px
  cursor: pointer;
  box-shadow: $shadow; // 0rem 0rem 0.61rem 0rem rgba(0, 0, 0, 0.1)
  border: none;
  transition: background-color 0.3s;

  &:hover {
    background-color: darken($accent-color-2, 10%);
  }

  .icon-white-30-2 {
    fill: $light-text; // #fff
    width: 30px;
    height: 30px;
  }
}
</style>