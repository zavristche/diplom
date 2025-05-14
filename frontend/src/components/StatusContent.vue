<script setup>
import { defineProps } from "vue";

// Определяем пропс status с валидацией
const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (value) => ["Новое", "Опубликовано", "Отклонено"].includes(value),
  },
});
</script>

<template>
  <span class="status" :class="{
    'status-new': status === 'Новое',
    'status-published': status === 'Опубликовано',
    'status-cancelled': status === 'Отклонено'
  }">
    {{ status }}
  </span>
</template>

<style scoped lang="scss">
@use "../assets/styles/_variables.scss" as *;

.status {
  padding: 4px 10px;
  border-radius: $border;
  font-size: 16px;
  font-weight: 500;
  display: flex;
}

.status-new {
  background-color: rgba($accent-color-2, 0.2);
  color: $accent-color-2;
}

.status-published {
  background-color: rgba($valid, 0.2);
  color: $valid;
}

.status-cancelled {
  background-color: rgba($error, 0.2);
  color: $error;
}
</style>