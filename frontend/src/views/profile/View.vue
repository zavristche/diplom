<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useProfileStore } from "../../stores/profile";
import { useAuthStore } from "../../stores/auth";
import BaseIcon from "../../components/BaseIcon.vue";
import Recipe from "../../components/Recipe.vue";
import Tabs from "../../components/Tabs.vue";
import Collection from "../../components/Collection.vue";
import Setting from "../../components/Setting.vue";

const router = useRouter();
const route = useRoute();
const profileStore = useProfileStore();
const authStore = useAuthStore();
const profile = computed(() => profileStore.currentProfile);

const isSettingOpen = ref(false);
const activeTab = ref(0);

const isOwnProfile = computed(() => authStore.user?.id === profile.value?.id);

const loadProfile = async () => {
  const profileId = route.params.id || authStore.user?.id;
  if (profileId) {
    await profileStore.fetchProfileById(profileId);
  }
};

onMounted(() => {
  loadProfile();
});

const handleTabChange = (index) => {
  activeTab.value = index;
};

const handleLogout = async () => {
  await authStore.logout();
  router.push({ path: "/", replace: true });
};
</script>

<template>
  <Setting
    :isOpen="isSettingOpen"
    @close="isSettingOpen = false"
    :profile="profile"
  />
  <div v-if="profile" class="preview profile">
    <div class="profile_header" v-if="profile.photo_header">
      <img :src="profile.photo_header" />
    </div>
    <div class="profile-info">
      <div class="avatar">
        <img :src="profile.avatar" />
      </div>
      <div class="profile-info__container">
        <h1>{{ profile.login }}</h1>
        <span>{{ profile.status }}</span>
        <div class="btn-group start" v-if="isOwnProfile">
          <button class="btn-dark no-line" @click="isSettingOpen = true">
            <BaseIcon
              viewBox="0 0 29 27"
              class="icon-pale-30-2"
              name="setting"
            />
          </button>
          <button class="btn-dark no-line" @click="handleLogout">
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

  <div v-if="profile" class="profile-active">
    <Tabs
      :tabs="['Рецепты', 'Коллекции']"
      @update:activeTab="handleTabChange"
    />
    <div class="btn-group" v-if="isOwnProfile">
      <router-link to="/collection/create" class="btn-dark">
        Создать коллекцию
      </router-link>
      <router-link to="/recipe/create" class="btn-dark">
        Создать рецепт
      </router-link>
    </div>
  </div>
  <section v-if="profile && activeTab === 0" class="content-container">
    <Recipe
      v-for="recipe in profile.recipes"
      :key="recipe.id"
      :recipe="recipe"
    />
  </section>
  <section v-if="profile && activeTab === 1" class="content-container">
    <Collection
      v-for="collection in profile.collections"
      :key="collection.id"
      :collection="collection"
    />
  </section>
  <div v-if="!profile">Профиль не найден</div>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/style";

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

.content-container {
  display: grid;
  width: 100%;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}
</style>