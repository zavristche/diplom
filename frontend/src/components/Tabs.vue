<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  tabs: {
    type: Array,
    required: true,
    default: () => []
  },
  activeTab: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['update:activeTab']);

const internalActiveTab = ref(props.activeTab);

// Синхронизация с пропсом activeTab
watch(() => props.activeTab, (newTab) => {
  if (newTab !== internalActiveTab.value) {
    console.log('Tabs.vue: watch activeTab:', newTab);
    internalActiveTab.value = newTab;
  }
});

const setActiveTab = (index) => {
  if (internalActiveTab.value === index) {
    console.log('Tabs.vue: setActiveTab: Ignored:', index);
    return;
  }
  console.log('Tabs.vue: setActiveTab:', index);
  internalActiveTab.value = index;
  emit('update:activeTab', index);
};
</script>

<template>
  <div class="tab-container" :key="internalActiveTab">
    <button 
      v-for="(tab, index) in tabs"
      :key="index"
      class="tab"
      :class="{ active: internalActiveTab === index }"
      @click="setActiveTab(index)"
    >
      {{ tab }}
    </button>
  </div>
</template>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;

.tab-container {
  display: flex;
  position: relative;
  box-shadow: $shadow;
  border-radius: $border;
  background-color: $background;
  overflow: hidden;

  // Подложка для активного таба
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: calc(100% / var(--tab-count));
    background-color: $background-dark;
    border-radius: $border;
    box-shadow: $shadow;
    transition: transform 0.3s ease;
    z-index: 0;
    transform: translateX(calc(var(--active-index, 0) * 100%));
  }
}

.tab {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: map-get($colors, "dark");
  padding: 15px 25px;
  font-size: 20px;
  font-weight: 400;
  cursor: pointer;
  background: none;
  border: none;
  outline: none;
  white-space: nowrap;
  position: relative;
  z-index: 1;
  transition: color 0.2s ease;

  &.active {
    color: map-get($colors, "white");
    background-color: $background-dark;
  }
}

</style>