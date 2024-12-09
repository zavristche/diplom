<style lang="scss">
.icon {
  display: flex;
  width: auto;
  height: auto;
}
</style>

<template>
<svg xmlns="http://www.w3.org/2000/svg" :class="$attrs.class || 'icon-45-1'"  :viewBox="computedViewBox" v-html="path"></svg>
</template>

<script>
import icons from "../assets/icons.js"; // Это пример файла с иконками

export default {
  props: {
    name: {
      type: String,
      required: true,
    },
    viewBox: {
      type: String,
    },
  },
  computed: {
    computedViewBox() {
      return this.viewBox; // Возвращает `viewBox` из пропсов или значение по умолчанию
    },
  },
  data() {
    return {
      path: icons[this.name] || "", // Загружает путь для иконки по имени
    };
  },
};
</script>
