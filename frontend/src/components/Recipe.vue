<script setup>
import BaseIcon from "./BaseIcon.vue";
import { ref, onMounted, onUnmounted } from "vue";
import { defineProps } from "vue";

const isMenuVisible = ref(false);
const menuRef = ref(null); // Ссылка на контейнер меню

const toggleMenu = () => {
  isMenuVisible.value = !isMenuVisible.value;
};

// Функция для закрытия меню при клике вне
const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    isMenuVisible.value = false;
  }
};

// Добавляем и убираем слушатель событий
onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});

defineProps({
  recipe: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <section class="card recipe" v-if="recipe && recipe.id">
    <router-link :to="`/recipe/${recipe.id}`" class="card__preview">
      <img :src="`${recipe.photo}`" alt="" />
    </router-link>
    <div class="card__info">
      <div class="card__title">
        <router-link :to="`/profile/${recipe.user.id}`">
          <img :src="`${recipe.user.avatar}`" alt="" />
        </router-link>
        <div class="card__text">
          <h3>
            <router-link :to="`/recipe/${recipe.id}`">{{
              recipe.title
            }}</router-link>
          </h3>
          <router-link
            :to="`/profile/${recipe.user.id}`"
            class="card__author"
            >{{ recipe.user.login }}</router-link
          >
          <div class="card__metadata">
            <span class="date">{{ recipe.created_at }}</span>
            <span class="marker">•</span>
            <span class="reaction">
              <BaseIcon
                viewBox="0 0 12 12"
                class="icon-light-12-1"
                name="bookmark"
              />
              <span class="reaction__count">{{ recipe.saved }}</span>
            </span>
            <span class="marker">•</span>
            <span class="reaction">
              <BaseIcon
                viewBox="0 0 13 13"
                class="icon-light-13-1"
                name="heart"
              />
              <span class="reaction__count">{{ recipe.likes }}</span>
            </span>
          </div>
        </div>
      </div>
      <div class="btn-menu-container" ref="menuRef">
        <button type="button" @click="toggleMenu">
          <BaseIcon viewBox="0 0 40 40" class="icon-dark-45-0" name="menu" />
        </button>
        <div class="btn-popup" v-if="isMenuVisible">
          <button class="btn-item">
            <BaseIcon
              viewBox="0 0 65 65"
              class="icon-dark-55-1"
              name="heartb"
            />
            Поставить лайк
          </button>
          <button class="btn-item">
            <BaseIcon
              viewBox="0 0 65 65"
              class="icon-dark-55-1"
              name="bookmarkb"
            />
            В коллекцию
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;
@use "../assets/styles/normalize.scss";

.btn-menu-container {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
  font-size: 18px;

  .btn-popup {
    position: absolute;
    width: 12rem;
    z-index: 10;
    top: calc(100% + 5px);
    right: 0.5rem;
    gap: 5px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    background-color: $background;
    box-shadow: $shadow;
    border-radius: $border;
    transition: transform 0.5s, opacity 0.5s;

    .btn-item {
      display: flex;
      flex-direction: row;
      align-items: center;
      padding: 0 5px;
      border-radius: $border;
      transition: 0.5s ease;

      &:hover {
        background-color: $light;
      }
    }
  }
}

.card {
  // Пример стилей для .card, если они не определены в normalize.scss или variables.scss
  display: grid;
  grid-template-rows: 200px 1fr;
  width: 100%;
  height: 280px;
  gap: 20px;

  .card__preview {
    display: block;
    width: 100%;
    height: 100%;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: $border;
      box-shadow: $shadow;
    }
  }

  .card__info {
    display: flex;
    justify-content: space-between;
    gap: 10px;

    .card__title {
      display: flex;
      gap: 10px;

      img {
        object-fit: cover;
        width: 35px;
        height: 35px;
        border-radius: 100%;
      }

      .card__text {
        display: flex;
        flex-direction: column;
        gap: 3px;
        color: $text-info;
        font-weight: 400;
        font-size: 14px;

        h3 {
          color: $background-dark;
          font-size: 16px;
          font-weight: 500;
        }

        .card__author {
          color: $text-info;
        }

        .card__metadata {
          display: flex;
          align-items: center;
          gap: 5px;
        }
      }
    }
  }
}

.reaction {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 14px;
}
</style>