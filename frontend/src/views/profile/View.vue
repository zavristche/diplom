<script setup>
import { ref, computed, onMounted, watch } from "vue";
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
const isLoading = ref(false); // Добавляем состояние загрузки

const isSettingOpen = ref(false);
const activeTab = ref(0);

const isOwnProfile = computed(() => authStore.user?.id === profile.value?.id);

const loadProfile = async () => {
  const profileId = route.params.id || authStore.user?.id;
  if (!profileId) {
    console.error("No profile ID available");
    router.push({ name: "login" });
    return;
  }

  try {
    isLoading.value = true; // Устанавливаем загрузку
    console.log("Loading profile for ID:", profileId);

    // Если есть query-параметр reload, принудительно обновляем профиль
    if (route.query.reload) {
      console.log("Reload query detected, forcing profile update");
      await profileStore.updateProfile(profileId);
    } else {
      // Загружаем профиль только если он отсутствует или ID изменился
      if (!profileStore.currentProfile || profileStore.currentProfile.id !== profileId) {
        await profileStore.fetchProfileById(profileId);
      } else {
        console.log("Using cached profile data:", profileStore.currentProfile);
      }
    }
    console.log("Profile loaded:", profileStore.currentProfile);
  } catch (error) {
    console.error("Error loading profile:", error);
    router.push({ name: "home" });
  } finally {
    isLoading.value = false; // Сбрасываем загрузку
  }
};

onMounted(() => {
  loadProfile();
});

// Перезагружаем профиль при изменении маршрута
watch(
  () => route.params.id,
  () => {
    console.log("Route changed, reloading profile");
    loadProfile();
  },
  { immediate: true }
);

const handleLogout = async () => {
  try {
    await authStore.logout();
    console.log("User logged out successfully");
    router.push({ path: "/", replace: true });
  } catch (error) {
    console.error("Error during logout:", error);
  }
};
</script>

<template>
  <Setting
    :isOpen="isSettingOpen"
    @close="isSettingOpen = false"
    :profile="profile"
  />
  <div v-if="isLoading" class="loading">
    Загрузка...
  </div>
  <div v-else-if="profile" class="preview profile">
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

  <div v-if="profile && !isLoading" class="profile-active">
    <Tabs
      :tabs="['Рецепты', 'Коллекции']"
      v-model:activeTab="activeTab"
    />
    <div class="btn-group" v-if="isOwnProfile">
      <router-link to="/recipe/create" class="btn-dark">
        Создать рецепт
      </router-link>
      <router-link to="/collection/create" class="btn-dark">
        Создать коллекцию
      </router-link>
    </div>
  </div>
  <section v-if="profile && !isLoading && activeTab === 0" class="content-grid">
    <Recipe
      v-for="recipe in profile.recipes"
      :key="recipe.id"
      :recipe="recipe"
    />
  </section>
  <section v-if="profile && !isLoading && activeTab === 1" class="content-grid">
    <Collection
      v-for="collection in profile.collections"
      :key="collection.id"
      :collection="collection"
    />
  </section>
  <div v-if="!profile && !isLoading" class="error">
    Профиль не найден
  </div>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/style";

.profile-active {
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
  justify-content: space-between;
  width: 100%;
  margin-top: 20px;

  .btn-group {
    width: auto;
  }
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

      h1 {
        font-size: 28px;
        margin: 0;
      }

      span {
        font-size: 16px;
        color: $text-info;
      }
    }
  }
}

.loading {
  text-align: center;
  font-size: 18px;
  padding: 20px;
  color: $text-info;
}

.error {
  text-align: center;
  font-size: 18px;
  padding: 20px;
  color: $error;
}

// Адаптивность
@media (max-width: 768px) {
  .profile-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 20px;
    padding: 20px;

    .avatar {
      width: 100px;
      height: 100px;
    }

    .profile-info__container {
      h1 {
        font-size: 24px;
      }
    }
  }

  .profile_header {
    height: 200px;
  }
}

@media (max-width: 480px) {
  .profile-info {
    padding: 15px;

    .avatar {
      width: 80px;
      height: 80px;
    }

    .profile-info__container {
      h1 {
        font-size: 20px;
      }

      span {
        font-size: 14px;
      }
    }
  }

  .profile_header {
    height: 150px;
  }

  .profile-active {
    flex-direction: column;
    align-items: stretch;

    .btn-group {
      width: 100%;
      flex-direction: column;
      gap: 10px;
    }
  }
}
</style>