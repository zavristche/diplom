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
@use "../assets/styles/normalize.scss";

.tab-container {
  display: flex;
  flex-direction: row;
  box-shadow: $shadow;
  border-radius: $border;
  position: relative;
  background-color: $background;
  overflow: hidden;
}

.tab {
  flex: 0 0 auto;
  min-width: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: $background-dark;
  padding: 15px 25px;
  font-weight: 400;
  font-size: 20px;
  cursor: pointer;
  border: none;
  outline: none;
  background: none;
  position: relative;
  z-index: 1;
  transition: color 0.2s ease;
  white-space: nowrap;

  &:not(:last-child) {
    margin-right: 2px;
  }
}

.tab.active {
  color: #FFF;
  background-color: $background-dark; // Вернули заливку для активного таба
  position: relative;
  z-index: 2;
}

.tab-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  background-color: $background-dark;
  box-shadow: $shadow;
  border-radius: $border;
  transition: transform 0.2s ease;
  z-index: 0;
  transform-origin: left;
  width: 100%;
  transform: scaleX(0);
}

.tab-container .tab.active ~ .tab-container::before {
  display: none;
}

.tab-container .tab.active + .tab-container::before {
  display: none;
}

.tab-container .tab:nth-child(1).active ~ .tab-container::before {
  transform: scaleX(0);
}

.tab-container .tab:nth-child(1).active + .tab-container::before {
  transform: translateX(0) scaleX(100%);
  display: block;
}

.tab-container .tab:nth-child(2).active ~ .tab-container::before {
  transform: scaleX(0);
}

.tab-container .tab:nth-child(2).active + .tab-container::before {
  transform: translateX(100%) scaleX(100%);
  display: block;
}

.tab-container .tab:nth-child(3).active ~ .tab-container::before {
  transform: scaleX(0);
}

.tab-container .tab:nth-child(3).active + .tab-container::before {
  transform: translateX(200%) scaleX(100%);
  display: block;
}

.tab-container {
  --tab-count: v-bind('tabs.length');
}
</style>