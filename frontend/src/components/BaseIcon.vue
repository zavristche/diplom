<style lang="scss">
.icon {
  display: flex;
  align-content: center;
  width: auto;
  height: auto;
}
</style>

<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    :class="$attrs.class || 'icon-45-1'"
    :viewBox="computedViewBox"
    v-html="path"
  ></svg>
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
  data() {
    return {
      path: icons[this.name] || "",
    };
  },
  computed: {
    computedViewBox() {
      return this.viewBox;
    },
  },
  watch: {
    name(newName) {
      this.path = icons[newName] || "";
    },
  },
};
</script>