<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useCollectionStore } from "../../stores/collection";
import BaseIcon from "../../components/BaseIcon.vue";
import Recipe from "../../components/Recipe.vue";
import ReactionButton from '../../components/ReactionButton.vue';
import DeleteButton from '../../components/DeleteButton.vue';


const route = useRoute();
const collectionStore = useCollectionStore();
const collection = computed(() => collectionStore.currentCollection);

// Инициализация заголовка страницы
if (collection.value) {
  document.title = collection.value.title || "Коллекция";
} else {
  // Загружаем данные, если они отсутствуют
  collectionStore.fetchCollectionById(route.params.id).then((data) => {
    if (data) {
      document.title = data.title || "Коллекция";
    }
  });
}
</script>

<template>
  <div v-if="collection && collection.photo" class="preview">
    <img :src="collection.photo" alt="" />
  </div>
  <div v-if="collection" class="content-info">
    <span class="time">{{ collection.created_at }}</span>
    <h1>{{ collection.title }}</h1>
    <router-link :to="`/profile/${collection.user.id}`" class="author">
      <img :src="collection.user.avatar" alt="" />
      {{ collection.user.login }}
    </router-link>
    <div class="description">
      {{ collection.description }}
    </div>
    <div class="btn-group end">
      <router-link :to="`/collection/edit/${collection.id}`" class="btn-dark">
        Редактировать
      </router-link>
      <DeleteButton :entity-id="collection.id" entity-type="collection" />
      <ReactionButton :entity-type="'collection'" :entity-id="collection.id" :count="collection.likes" />
    </div>
  </div>
  <section v-if="collection" class="content-container">
    <Recipe
      v-for="recipe in collection.recipes"
      :key="recipe.id"
      :recipe="recipe"
    />
  </section>
  <div v-if="collection" class="btn-group">
    <button
      v-for="(mark, index) in collection.marks"
      :key="index"
      class="btn-dark line"
    >
      <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="mark" />
      {{ mark.title }}
    </button>
  </div>
  <div v-else>Коллекция не найдена</div>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/style";

.preview {
  display: flex;
  width: 100%;
  height: 500px;
  img {
    box-shadow: $shadow;
    object-fit: cover;
    width: 100%;
    height: 100%;
    border-radius: $border;
  }
}

.cards-info {
  display: flex;
  flex-direction: row;
  gap: 40px;
}

.card-info {
  display: flex;
  gap: 10px;
  flex-direction: column;

  .card-info__title {
    font-weight: 500;
  }

  .card-info__var {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-direction: row;
  }
}

.time {
  color: $text-info;
  font-weight: 400;
}

.author {
  display: flex;
  align-items: center;
  flex-direction: row;
  gap: 10px;
  font-weight: 400;

  img {
    object-fit: cover;
    width: 40px;
    height: 40px;
    border-radius: 100%;
  }
}

.content-container {
  display: grid;
  width: 100%;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}

.content-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.description {
  line-height: 150%;
}
</style>
