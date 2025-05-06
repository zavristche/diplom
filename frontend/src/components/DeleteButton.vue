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
  <button class="btn-dark" @click="handleDelete">
    <BaseIcon viewBox="0 0 29 29" class="icon-white-30-2" name="close" />
    Удалить
  </button>
</template>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;
</style>