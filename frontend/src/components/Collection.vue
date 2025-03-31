<script setup>
import BaseIcon from "./BaseIcon.vue";
import { ref } from 'vue';
import { defineProps } from 'vue';

const isMenuVisible = ref(false);
const toggleMenu = () => {
  isMenuVisible.value = !isMenuVisible.value;
};

defineProps({
  collection: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <section class="card collection">
    <router-link :to="`/collection/${collection.id}`" class="card__preview">
      <!-- Первое фото (большое) -->
      <div class="preview-item preview-main" 
           :style="collection.recipes?.[0]?.photo ? 
                  { backgroundImage: `url(${collection.recipes[0].photo})` } : 
                  { backgroundColor: '$text-info-light' }">
      </div>
      
      <!-- Второе фото -->
      <div class="preview-item" 
           :style="collection.recipes?.[1]?.photo ? 
                  { backgroundImage: `url(${collection.recipes[1].photo})` } : 
                  { backgroundColor: '$text-info-light' }">
      </div>
      
      <!-- Третье фото -->
      <div class="preview-item" 
           :style="collection.recipes?.[2]?.photo ? 
                  { backgroundImage: `url(${collection.recipes[2].photo})` } : 
                  { backgroundColor: '$text-info-light' }">
      </div>
    </router-link>

    <div class="card__info">
      <div class="card__title">
        <router-link :to="`/profile/${collection.user.id}`">
          <img :src="`${collection.user.avatar}`" alt="">
        </router-link>
        <div class="card__text">
          <h3><router-link :to="`/collection/${collection.id}`">{{ collection.title }}</router-link></h3>
          <router-link :to="`/profile/${collection.user.id}`" class="card__author">{{ collection.user.login }}</router-link>
          <div class="card__metadata">
            <span class="date">{{ collection.created_at }}</span>
            <span class="marker">•</span>
            <span class="reaction">
              <BaseIcon viewBox="0 0 13 13" class="icon-light-13-1" name="heart"/>
              <span class="reaction__count">{{ collection.likes }}</span>
            </span>
          </div>
        </div>
      </div>

      <div class="btn-menu-container">
        <button type="button" @click="toggleMenu">
          <BaseIcon viewBox="0 0 40 40" class="icon-dark-45-0" name="menu"/>
        </button>
        <div class="btn-popup" v-if="isMenuVisible">
          <button class="btn-item">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-1" name="heartb"/>
            Поставить лайк
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

.reaction {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 14px;
}

.card {
  display: grid;
  grid-template-rows: 200px 1fr;
  width: 100%;
  height: 280px;
  gap: 20px;

  .card__preview {
    display: grid;
    grid-template: repeat(2, 1fr) / repeat(2, 1fr);
    width: 100%;
    height: 100%;
    gap: 5px;

    .preview-item {
      border-radius: $border;
      box-shadow: $shadow;
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .preview-main {
      grid-row: 1 / 3;
      grid-column: 1 / 2;
    }
  }

  .card__info {
    display: flex;
    justify-content: space-between;
    gap: 10px;

    img {
      object-fit: cover;
      width: 35px;
      height: 35px;
      border-radius: 100%;
    }

    .card__title {
      display: flex;
      gap: 10px;

      .card__text {
        display: flex;
        flex-direction: column;
        align-content: center;
        gap: 3px;
        color: $text-info;
        font-weight: 400;
        font-size: 14px;

        h3 {
          color: $background-dark;
          font-size: 16px;
          font-weight: 500;
        }

        .card__metadata {
          display: flex;
          align-items: center;
          flex-direction: row;
          gap: 5px;
        }
      }
    }
  }
}
</style>