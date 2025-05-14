<script setup>
import { defineProps, defineEmits } from "vue";
import BaseIcon from "./BaseIcon.vue";

defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["remove"]);
</script>

<template>
  <span class="mark-item">
    {{ item.title }}
    <button class="mark-item__close" @click="emit('remove')">
      <BaseIcon viewBox="0 0 65 65" class="icon-dark-40-1" name="close" />
    </button>
  </span>
</template>

<style scoped lang="scss">
@use "../assets/styles/variables" as *;

.mark-item {
  display: flex;
  flex-direction: row;
  gap: 5px;
  align-items: center;
  background-color: $background;
  border-radius: $border;
  box-shadow: $shadow;

  .mark-item__close {
    cursor: pointer;
  }
}
</style>