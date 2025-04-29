<template>
  <button
    type="button"
    class="btn-dark"
    @click="toggleLike"
  >
    <BaseIcon
      viewBox="0 0 30 30"
      class="icon-white-30-2"
      :name="isLiked ? 'heart_fill' : 'heart_line'"
      :key="isLiked ? 'heart_fill' : 'heart_line'"
    />
    Оценить
  </button>
</template>

<script setup>
import { defineProps, onBeforeMount } from 'vue';
import BaseIcon from './BaseIcon.vue'; // Укажи правильный путь
import { useReaction } from '../composables/useReaction'; // Укажи правильный путь

const props = defineProps({
  entityType: {
    type: String,
    required: true,
    validator: (value) => ['recipe', 'collection'].includes(value),
  },
  entityId: {
    type: [Number, String],
    required: true,
  },
});

const { isLiked, toggleLike, checkInitialLike } = useReaction(props.entityType, props.entityId);

// Запускаем проверку, но не ждём её
onBeforeMount(() => {
  checkInitialLike();
});
</script>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;

.btn-dark {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background-color: $background-dark;
  color: $light-text;
  border: none;
  border-radius: $border;
  cursor: pointer;

  &:hover {
    background-color: darken($background-dark, 10%);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.icon-white-30-2 {
  stroke: $light-text; /* Белая обводка */
  stroke-width: 2;
}
</style>