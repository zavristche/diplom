<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { useSearchStore } from "../stores/search";
import BaseIcon from "./BaseIcon.vue";
import MarkItem from "./MarkItem.vue";

const props = defineProps({
  name: {
    type: String,
    required: true,
    validator: (value) => ["mark", "product"].includes(value),
  },
  modelValue: {
    type: Array,
    default: () => [],
  },
  query: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(["update:modelValue"]);

const searchStore = useSearchStore();
const searchValue = ref("");
const activeType = ref(null);
const isInputFocused = ref(false);
const componentRef = ref(null); // Ссылка на корневой элемент компонента

const selectedItems = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});

const itemTypes = computed(() => {
  return props.name === "mark"
    ? searchStore.searchData.mark_types || {}
    : searchStore.searchData.product_types || {};
});

const items = computed(() => {
  return props.name === "mark"
    ? searchStore.searchData.marks || {}
    : searchStore.searchData.products || {};
});

const filteredItems = computed(() => {
  const itemList = Object.values(items.value);
  if (!searchValue.value && !activeType.value) return itemList;
  return itemList.filter((item) => {
    const matchesType = activeType.value
      ? item.type_id === Number(activeType.value)
      : true;
    const matchesSearch = searchValue.value.toLowerCase()
      ? item.title.toLowerCase().includes(searchValue.value.toLowerCase())
      : true;
    return matchesType && matchesSearch;
  });
});

// Инициализация выбранных элементов из query
watch(
  () => props.query,
  () => {
    const ids = props.query[props.name + "s"];
    if (ids) {
      const idArray = Array.isArray(ids) ? ids : [ids];
      const itemList = Object.values(items.value);
      const selected = itemList.filter((item) =>
        idArray.includes(String(item.id))
      );
      if (selected.length > 0) {
        selectedItems.value = selected;
      }
    }
  },
  { immediate: true }
);

const selectType = (typeId) => {
  activeType.value = typeId;
  searchValue.value = "";
  isInputFocused.value = true;
};

const addItem = (item) => {
  if (!selectedItems.value.some((m) => m.id === item.id)) {
    selectedItems.value = [...selectedItems.value, item];
  }
  searchValue.value = "";
  isInputFocused.value = false;
};

const removeItem = (itemId) => {
  selectedItems.value = selectedItems.value.filter(
    (item) => item.id !== itemId
  );
};

// Обработчик клика вне компонента
const handleClickOutside = (event) => {
  if (componentRef.value && !componentRef.value.contains(event.target)) {
    isInputFocused.value = false;
  }
};

// Добавляем слушатель событий при монтировании
onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

// Удаляем слушатель при размонтировании
onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
  <label :for="name" class="marks" ref="componentRef">
    {{ props.name === "mark" ? "Метки" : "Продукты" }}
    <div class="btn-group start">
      <button
        class="mark_type"
        :class="{ active: activeType === null }"
        @click.prevent="selectType(null)"
      >
        Все
      </button>
      <button
        v-for="(type, id) in itemTypes"
        :key="id"
        class="mark_type"
        :class="{ active: activeType === id }"
        @click.prevent="selectType(id)"
      >
        {{ type }}
      </button>
    </div>
    <div class="mark-search">
      <div class="input-with-marks">
        <div class="mark-items" :key="props.name">
          <MarkItem
            v-for="item in selectedItems"
            :key="item.id"
            :item="item"
            @remove="removeItem(item.id)"
          />
          <input
            v-model="searchValue"
            type="text"
            class="input-form"
            :placeholder="`Поиск ${
              props.name === 'mark' ? 'меток' : 'продуктов'
            }`"
            @focus="isInputFocused = true"
          />
        </div>
      </div>
      <div v-if="isInputFocused" class="mark-dropdown">
        <div v-if="filteredItems.length" class="mark-options">
          <div
            v-for="item in filteredItems"
            :key="item.id"
            class="mark-option"
            @click="addItem(item)"
          >
            {{ item.title }}
          </div>
        </div>
        <div v-else class="mark-option">
          Нет подходящих {{ props.name === "mark" ? "меток" : "продуктов" }}
        </div>
      </div>
    </div>
  </label>
</template>

<style scoped lang="scss">
@use "../assets/styles/variables" as *;

.marks {
  display: flex;
  flex-direction: column;
  gap: 15px;
  font-weight: 400;
  width: 100%;

  .input-with-marks {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
  }

  .mark-items {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px;
    border: 1px solid $text-info-light;
    border-radius: $border;
    min-height: 50px;
    align-items: center;
    width: 100%;

    .mark-item {
      display: flex;
      flex-direction: row;
      gap: 5px;
      align-items: center;
      background-color: $background;
      padding: 5px 10px;
      border-radius: $border;
      box-shadow: $shadow;

      .mark-item__close {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
      }
    }

    .input-form {
      border: none;
      flex-grow: 1;
      padding: 5px;
      font-size: 20px;
      font-weight: 400;
      background: transparent;
      outline: none;
      min-width: 100px;
    }
  }

  .mark-search {
    position: relative;
  }

  .mark-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background-color: $light;
    border: 1px solid $text-info-light;
    border-radius: $border;
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
    box-shadow: $shadow;
  }

  .mark-options {
    display: flex;
    flex-direction: column;
  }

  .mark-option {
    padding: 10px 20px;
    cursor: pointer;

    &:hover {
      background-color: $background;
    }
  }

  .mark_type {
    background: none;
    border-radius: $border;
    border: none;
    color: $background-dark;
    padding: 5px 10px;
    font-size: 20px;
    cursor: pointer;
    transition: color 0.2s ease;

    &.active {
      color: $background-dark;
      background-color: rgba($background-dark, 0.2);
    }

    &:hover {
      color: $background-dark;
    }
  }
}
</style>