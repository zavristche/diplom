<script setup>
import { defineProps, computed } from 'vue';
import BaseIcon from './BaseIcon.vue';

const props = defineProps({
  mark: {
    type: Object,
    required: true,
    default: () => ({ id: 0, title: '' }),
  },
  markType: {
    type: String,
    required: true,
    default: 'mark',
    validator: (value) => ['mark', 'product'].includes(value),
  },
  contentType: {
    type: String,
    required: true,
    default: 'recipe',
    validator: (value) => ['recipe', 'collection'].includes(value),
  },
});

const queryKey = computed(() => {
  return props.markType === 'mark' ? 'marks' : 'products';
});

const searchUrl = computed(() => {
  return `/search/${props.contentType}?${queryKey.value}=${props.mark.id}`;
});
</script>

<template>
  <router-link :to="searchUrl" class="btn-dark line">
    <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="mark" />
    {{ mark.title }}
  </router-link>
</template>

<style lang="scss">
@use "../assets/styles/variables" as *;
</style>