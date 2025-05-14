<template>
  <button
    type="button"
    :class="['btn-dark', { 'btn-item': variant === 'menu' }]"
    @click="toggleLike"
  >
    <span v-if="variant === 'menu'">{{ isLiked ? 'Снять оценку' : 'Оценить' }}</span>
    <span v-if="variant === 'default'">{{ likesCount }}</span>
    <BaseIcon
      :viewBox="variant === 'menu' ? '0 0 65 65' : '0 0 30 30'"
      :class="variant === 'menu' ? 'icon-dark-55-1' : 'icon-white-30-2'"
      :name="isLiked ? 'heart_fill' : 'heart_line'"
      :key="isLiked ? 'heart_fill' : 'heart_line'"
    />
    <span v-if="variant === 'default'">Оценить</span>
  </button>
</template>

<script setup>
import { defineProps, onBeforeMount } from 'vue';
import BaseIcon from './BaseIcon.vue';
import { useReaction } from '../composables/useReaction';

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
  count: {
    type: Number,
    required: true,
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'menu'].includes(value),
  },
});

console.log('ReactionButton: entityType:', props.entityType, 'entityId:', props.entityId, 'initial count:', props.count);

const { isLiked, toggleLike, checkInitialLike, likesCount } = useReaction(props.entityType, props.entityId, props.count);

onBeforeMount(() => {
  console.log(`Checking initial like for ${props.entityType} with ID ${props.entityId}`);
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
  stroke: $light-text;
  stroke-width: 2;
}
</style>