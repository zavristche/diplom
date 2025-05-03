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

// Логируем preview для отладки
console.log('Collection preview:', collection => collection.preview);
</script>

<template>
  <section class="card collection" v-if="collection && collection.id">
    <router-link :to="`/collection/${collection.id}`" class="card__preview">
      <div class="preview-grid">
        <div class="preview-main" :style="{ backgroundImage: collection.preview?.[0] ? `url(${collection.preview[0]})` : 'none', backgroundColor: !collection.preview?.[0] ? '#e0e0e0' : 'transparent' }"></div>
        <div class="preview-small" :style="{ backgroundImage: collection.preview?.[1] ? `url(${collection.preview[1]})` : 'none', backgroundColor: !collection.preview?.[1] ? '#e0e0e0' : 'transparent' }"></div>
        <div class="preview-small" :style="{ backgroundImage: collection.preview?.[2] ? `url(${collection.preview[2]})` : 'none', backgroundColor: !collection.preview?.[2] ? '#e0e0e0' : 'transparent' }"></div>
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
        <button type="button" @click="toggleMenu"><BaseIcon viewBox="0 0 40 40" class="icon-dark-45-0" name="menu"/></button>
        <div class="btn-popup" v-if="isMenuVisible">
          <button class="btn-item">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-1" name="heartb"/>
            Поставить лайк
          </button>
          <button class="btn-item">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-1" name="bookmarkb"/>
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
</style>