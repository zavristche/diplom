<script setup>
import { ref, defineProps, defineEmits } from "vue";
import BaseIcon from "./BaseIcon.vue";

defineProps(["isOpen", 'recipe_id']);
const emit = defineEmits(["close"]);

const closeModal = () => {
  emit("close");
};

const collections = ref([
  { id: 1, name: "Грузинская кухня", image: "https://placehold.co/55x55" },
  { id: 2, name: "Супы мира", image: "https://placehold.co/55x55" },
  { id: 3, name: "Летние рецепты", image: "https://placehold.co/55x55" },
  { id: 4, name: "Постные блюда", image: "https://placehold.co/55x55" },
  { id: 5, name: "На скорую руку", image: "https://placehold.co/55x55" },
]);

const selectedCollectionId = ref(null);

const selectCollection = (id) => {
  selectedCollectionId.value = id;
};
</script>

<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2 class="modal-title">Добавить в коллекцию</h2>
        <button class="btn-icon" @click="closeModal">
          <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-2" name="close" />
        </button>
      </div>
      <div class="modal-content">
        <div class="collections-list">
          <div
            v-for="collection in collections"
            :key="collection.id"
            class="collection-item"
            :class="{ 'selected': selectedCollectionId === collection.id }"
            @click="selectCollection(collection.id)"
          >
            <img :src="collection.image" class="collection-image" />
            <span class="collection-name">{{ collection.name }}</span>
          </div>
        </div>
      </div>
      <form @submit.prevent="closeModal">
        <input
          type="hidden"
          name="collection_id"
          :value="selectedCollectionId"
        >
        <input
          type="hidden"
          name="recipe_id"
          :value="recipe_id"
        >
        <button class="btn-save" type="submit">Сохранить</button>
      </form>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
}

.modal-container {
  width: 480px;
  padding: 30px;
  background: $background;
  box-shadow: $shadow;
  border-radius: $border;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 9px;
}

.modal-title {
  color: $dark-text;
  font-size: map-get($font-weather, "large", "size");
  font-family: Rubik;
  font-weight: map-get($font-weather, "medium", "weight");
}

.modal-content {
  width: 420px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.collections-list {
  height: 314px;
  overflow-y: auto;
}

.collection-item {
  width: 100%;
  height: 70px;
  border-radius: $border;
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 10px;
  cursor: pointer;

  &.selected {
    background: $light;
  }
}

.collection-image {
  width: 55px;
  height: 55px;
  border-radius: $border;
  margin-left: 10px;
}

.collection-name {
  color: $dark-text;
  font-family: Rubik;
  font-weight: map-get($font-weather, "medium", "weight");
}

.btn-save {
  width: 161px;
  height: 50px;
  padding: 11px 18px;
  background: $background-dark;
  border-radius: $border;
  color: $light-text;
  font-size: 18px;
  font-family: Rubik;
  font-weight: map-get($font-weather, "small", "weight");
  border: none;
  align-self: flex-end;
  cursor: pointer;
}
</style>