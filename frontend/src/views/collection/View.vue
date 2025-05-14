<script setup>
import { ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useCollectionStore } from "../../stores/collection";
import { useAuthStore } from "../../stores/auth";
import Recipe from "../../components/Recipe.vue";
import ReactionButton from "../../components/ReactionButton.vue";
import DeleteButton from "../../components/DeleteButton.vue";
import Mark from "../../components/Mark.vue";

const route = useRoute();
const collectionStore = useCollectionStore();
const collection = computed(() => collectionStore.currentCollection);
const authStore = useAuthStore();

// Загружаем коллекцию
if (!collection.value) {
  collectionStore.fetchCollectionById(route.params.id).then((data) => {
    if (data) {
      document.title = data.title || "Коллекция";
    }
  });
}

// Проверка, является ли текущий пользователь автором
const isAuthor = computed(() => {
  return (
    authStore.isAuthenticated &&
    authStore.userId &&
    collection.value &&
    authStore.userId === collection.value.user_id
  );
});

// Инициализация заголовка страницы и отладка
watch(
  collection,
  (newCollection) => {
    console.log("Collection loaded:", newCollection);
    if (newCollection) {
      document.title = newCollection.title || "Коллекция";
    }
  },
  { immediate: true }
);

// Отладка authStore
watch(
  [() => authStore.userId],
  () => {
    console.log("authStore.userId:", authStore.userId);
    console.log("isAuthor:", isAuthor.value);
  },
  { immediate: true }
);
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
      <div v-if="isAuthor" class="btn-group">
        <router-link :to="`/collection/edit/${collection.id}`" class="btn-dark">
          Редактировать
        </router-link>
        <DeleteButton :entity-id="collection.id" entity-type="collection" />
      </div>
      <ReactionButton :entity-type="'collection'" :entity-id="collection.id" :count="collection.likes" />
    </div>
  </div>
  <section v-if="collection" class="content-grid">
    <Recipe
      v-for="recipe in collection.recipes"
      :key="recipe.id"
      :recipe="recipe"
    />
  </section>
  <div v-if="collection" class="btn-group end">
    <Mark
      v-for="(mark, index) in collection.marks"
      :key="index"
      :mark="mark"
      markType="mark"
      contentType="collection"
    />
    <Mark
      v-for="(product, index) in collection.products"
      :key="index"
      :mark="product"
      markType="product"
      contentType="collection"
    />
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

.content-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.description {
  line-height: 150%;
}
</style>