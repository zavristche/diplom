<script setup>
import { useRoute } from "vue-router";
import BaseIcon from "../../components/BaseIcon.vue";
import { ref, computed } from "vue";
import CollectionService from "../../api/CollectionService";
import Recipe from "../../components/Recipe.vue";

const route = useRoute();
const collection = route.meta.collection;
console.log(collection);
</script>

<template>
  <div class="preview">
    <img :src="`${collection.photo}`" alt="" />
  </div>
  <div class="content-info">
    <span class="time">{{ collection.created_at }}</span>
    <h1>{{ collection.title }}</h1>
    <div class="author">
      <img :src="`${collection.user.avatar}`" alt="" />
      {{ collection.user.login }}
    </div>
    <div class="description">
      {{ collection.description }}
    </div>
    <div class="btn-group">
      <button type="submit" class="btn-dark">
        <BaseIcon
          viewBox="0 0 25 26"
          class="icon-white-30-2"
          name="book"
        />Сохранить
      </button>
      <button type="submit" class="btn-dark">
        <BaseIcon
          viewBox="0 0 29 26"
          class="icon-white-30-2"
          name="heartb"
        />Оценить
      </button>
    </div>
  </div>
  <section class="content-container">
    <Recipe v-for="recipe in collection.recipes" :key="recipe.id" :recipe="recipe" />
  </section>
  <div class="btn-group">
    <button
      v-for="(mark, index) in collection.marks"
      :key="index"
      class="btn-dark line"
    >
      <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="mark" />
      {{ mark.title }}
    </button>
  </div>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/style";

//Карточка контента
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

.btn-group {
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
  justify-content: end;
  gap: 20px;
}

.description {
  line-height: 150%;
}

.content-container {
  display: grid;
  width: 100%;
  grid-template-columns: repeat(3, 1fr); // Три равные колонки
  gap: 40px; // Расстояние между карточками
  // margin-top: 50px;
}
</style>
