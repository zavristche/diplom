<style lang="scss">
.icon {
  display: flex;
  width: auto;
  height: auto;
}
</style>

<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    class="icon"
    v-html="path + viewBox"
  ></svg>
</template>

<script>
import icons from "../assets/icons.js";

export default {
  props: ["name"],
  data() {
    return {
      path: icons[this.name].path,
      viewBox: icons[this.name].view,
    };
  },
};
</script>
