<script setup>
import { ref } from 'vue';

const props = defineProps({
  tabs: {
    type: Array,
    required: true,
    default: () => []
  }
});

const emit = defineEmits(['update:activeTab']); // Добавляем событие

const activeTab = ref(0);

const setActiveTab = (index) => {
  activeTab.value = index;
  emit('update:activeTab', index); // Отправляем индекс активного таба родителю
};
</script>

<template>
  <div class="tab-container">
    <button 
      v-for="(tab, index) in tabs"
      :key="index"
      class="tab"
      :class="{ active: activeTab === index }"
      @click="setActiveTab(index)"
    >
      {{ tab }}
    </button>
  </div>
</template>

<style lang="scss">
/* Ваш существующий SCSS код остается без изменений */
</style>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;
@use "../assets/styles/normalize.scss";

.tab-container {
  display: flex;
  flex-direction: row;
  box-shadow: $shadow;
  width: auto;
  border-radius: $border;
  position: relative;
  background-color: $background;
}

.tab {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  color: $background-dark;
  padding: 15px 30px;
  font-weight: 400;
  font-size: 20px;
  cursor: pointer;
  border: none;
  outline: none;
  background: none;
  position: relative;
  z-index: 1;
  transition: color 0.3s ease;

  &.active {
    color: #FFF;
  }
}

/* Добавляем псевдоэлемент для активного состояния */
.tab-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  background-color: $background-dark;
  box-shadow: $shadow;
  border-radius: $border;
  transition: all 0.3s ease;
  z-index: 0;
}

/* Динамическая ширина и позиция активного фона */
.tab-container:has(.tab:nth-child(1).active)::before {
  width: calc((100% /  var(--tab-count)) * 1);
  transform: translateX(0%);
}

.tab-container:has(.tab:nth-child(2).active)::before {
  width: calc((100% /  var(--tab-count)) * 1);
  transform: translateX(100%);
}

.tab-container:has(.tab:nth-child(3).active)::before {
  width: calc((100% /  var(--tab-count)) * 1);
  transform: translateX(200%);
}

/* Устанавливаем переменную для количества табов */
.tab-container {
  --tab-count: v-bind('tabs.length');
}
</style>