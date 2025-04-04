<script setup>
import { useRoute } from "vue-router";
import { ref } from "vue";
import BaseIcon from "../../components/BaseIcon.vue";
import Recipe from "../../components/Recipe.vue";
import Tabs from "../../components/Tabs.vue";
import Collection from "../../components/Collection.vue";
import Setting from "../../components/Setting.vue";

const route = useRoute();
const profile = route.meta.profile;

const isSettingOpen = ref(false);
const activeTab = ref(0);

const handleTabChange = (index) => {
  activeTab.value = index;
};
</script>

<template>
  <Setting 
    :isOpen="isSettingOpen" 
    @close="isSettingOpen = false"
    :profile="profile"
  />
  <div class="preview profile">
    <div class="profile_header" v-if="profile.photo_header">
      <img :src="profile.photo_header" />
    </div>
    <div class="profile-info">
      <div class="avatar">
        <img :src="`${profile.avatar}`" />
      </div>
      <div class="profile-info__container">
        <h1>{{ profile.login }}</h1>
        <span>{{ profile.status }}</span>
        <div class="btn-group">
          <button class="btn-dark no-line" @click="isSettingOpen = true">
            <BaseIcon
              viewBox="0 0 29 27"
              class="icon-pale-30-2"
              name="setting"
            />
          </button>
          <button class="btn-dark no-line">
            <BaseIcon
              viewBox="0 0 29 29"
              class="icon-pale-30-2"
              name="logout"
            />
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="profile-active">
    <Tabs
      :tabs="['Рецепты', 'Коллекции']"
      @update:activeTab="handleTabChange"
    />
    <div class="btn-group">
      <router-link to="/collection/create" class="btn-dark">
        Создать коллекцию
      </router-link>
      <router-link to="/recipe/create" class="btn-dark">
        Создать рецепт
      </router-link>
    </div>
  </div>
  <section class="content-container" v-if="activeTab === 0">
    <Recipe
      v-for="recipe in profile.recipes"
      :key="recipe.id"
      :recipe="recipe"
    />
  </section>
  <section class="content-container" v-if="activeTab === 1">
    <Collection
      v-for="collection in profile.collections"
      :key="collection.id"
      :collection="collection"
    />
  </section>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/style";

.btn-group {
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
  justify-content: start;
}

.profile-active {
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
  justify-content: space-between;
}

.preview {
  display: flex;
  flex-direction: column;
  width: 100%;
  border-radius: $border;
  box-shadow: $shadow;

  &.profile {
    height: auto;
  }

  .profile_header {
    display: flex;
    height: 250px;

    img {
      object-fit: cover;
      border-radius: $border $border 0 0;
      width: 100%;
      height: 100%;
      box-shadow: none;
    }
  }

  .profile-info {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 30px;
    padding: 30px;

    .avatar {
      width: 140px;
      height: 140px;

      img {
        object-fit: cover;
        width: 100%;
        height: 100%;
        border-radius: 100%;
      }
    }

    .profile-info__container {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }
  }
}

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
