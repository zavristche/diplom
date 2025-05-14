<script setup>
import { ref, watch, defineProps, defineEmits } from "vue";
import { useRouter } from "vue-router";
import { useProfileAuth } from "../composables/useProfileAuth";
import { useAuthStore } from "../stores/auth";
import { useCollectionStore } from "../stores/collection";
import CollectionRecipeService from "../api/CollectionRecipeService";
import BaseIcon from "./BaseIcon.vue";

// Определяем пропсы с валидацией
const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
    default: false,
  },
  recipe_id: {
    type: [Number, String],
    required: true,
  },
});
const emit = defineEmits(["close"]);

const router = useRouter();
const { isAuthenticated, currentUser } = useProfileAuth();
const authStore = useAuthStore();
const collectionStore = useCollectionStore();

const collections = ref([]);
const selectedCollectionId = ref(null);
const error = ref(null);
const isLoading = ref(false);
const isSaving = ref(false);

// Загрузка коллекций при открытии модалки
watch(
  () => props.isOpen,
  async (newValue) => {
    if (!newValue) {
      return; // Ничего не делаем при закрытии
    }

    // Загружаем коллекции только если их нет
    if (collections.value.length > 0) {
      return;
    }

    if (!isAuthenticated.value) {
      error.value = "Пожалуйста, войдите в систему для добавления рецепта.";
      router.push({ name: "login" }).catch(() => {
        console.error("Маршрут /login не найден. Проверьте router.js");
        error.value = "Ошибка: страница входа недоступна";
      });
      return;
    }

    if (!currentUser.value?.id) {
      error.value = "Ошибка: данные пользователя недоступны.";
      return;
    }

    try {
      isLoading.value = true;
      error.value = null;
      await collectionStore.fetchUserCollections(currentUser.value.id);
      collections.value = collectionStore.collections || [];
      console.log("Collections loaded:", collections.value);
      if (collections.value.length === 0) {
        error.value = "У вас нет коллекций. Создайте новую коллекцию.";
      }
    } catch (err) {
      console.error("Ошибка при загрузке коллекций:", err.response?.data || err.message);
      error.value = "Не удалось загрузить коллекции. Попробуйте снова.";
    } finally {
      isLoading.value = false;
    }
  }
);

const selectCollection = (id) => {
  selectedCollectionId.value = id;
};

// Нормализация серверных ошибок
const normalizeServerErrors = (serverErrors) => {
  const normalized = {};
  for (const [key, value] of Object.entries(serverErrors)) {
    normalized[key] = Array.isArray(value) ? value[0] : value;
  }
  return normalized;
};

// Сохранение рецепта в коллекцию
const saveToCollection = async () => {
  if (!isAuthenticated.value) {
    error.value = "Пожалуйста, войдите в систему для добавления рецепта.";
    router.push({ name: "login" }).catch(() => {
      console.error("Маршрут /login не найден. Проверьте router.js");
      error.value = "Ошибка: страница входа недоступна";
    });
    return;
  }

  if (!selectedCollectionId.value) {
    error.value = "Выберите коллекцию.";
    return;
  }

  try {
    isSaving.value = true;
    error.value = null;
    console.log("Отправка запроса с authKey:", authStore.authKey);
    console.log("Data:", {
      collection_id: selectedCollectionId.value,
      recipe_id: props.recipe_id,
    });

    const response = await CollectionRecipeService.create({
      collection_id: selectedCollectionId.value,
      recipe_id: props.recipe_id,
    });

    if (response.data.success) {
      alert(response.data.message || JSON.stringify(response.data));
      selectedCollectionId.value = null;
      error.value = null;
      emit("close");
    } else {
      throw new Error(response.data.message || "Ошибка API");
    }
  } catch (err) {
    console.error("Ошибка при сохранении рецепта:", err.response?.data || err.message);
    if (err.response?.status === 401) {
      error.value = "Не авторизован. Пожалуйста, войдите снова.";
      authStore.clearUser();
      router.push({ name: "login" }).catch(() => {
        console.error("Маршрут /login не найден. Проверьте router.js");
        error.value = "Ошибка: страница входа недоступна";
      });
    } else if (err.response?.status === 409) {
      error.value = "Рецепт уже добавлен в эту коллекцию.";
    } else if (err.response?.data?.errors) {
      error.value = normalizeServerErrors(err.response.data.errors).general || "Ошибка валидации.";
    } else {
      error.value = err.response?.data?.message || "Произошла ошибка. Попробуйте снова.";
    }
  } finally {
    isSaving.value = false;
  }
};

const closeModal = () => {
  selectedCollectionId.value = null;
  error.value = null;
  // Не сбрасываем collections, чтобы избежать повторной загрузки
  emit("close");
};
</script>

<template>
  <div v-if="props.isOpen" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <div class="modal-header space">
        <h2 class="modal-title">Добавить в коллекцию</h2>
        <button class="btn-icon" @click="closeModal">
          <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-2" name="close" />
        </button>
      </div>
      <div class="modal-content">
        <div v-if="error" class="error-message">{{ error }}</div>
        <div v-if="isLoading" class="loading">Загрузка коллекций...</div>
        <div v-else-if="isSaving" class="saving">Сохранение рецепта...</div>
        <div v-else class="collections-list">
          <div
            v-for="collection in collections"
            :key="collection.id"
            class="collection-item"
            :class="{ selected: selectedCollectionId === collection.id }"
            @click="selectCollection(collection.id)"
          >
            <img
              v-if="collection.photo"
              :src="collection.photo"
              class="collection-image"
              alt="Collection preview"
            />
            <div v-else class="collection-placeholder"></div>
            <span class="collection-name">{{ collection.title }}</span>
          </div>
          <router-link to="/collection/create" class="collection-item create-collection">
            <BaseIcon
              viewBox="0 0 65 65"
              class="icon-dark-55-2 collection-icon"
              name="plus"
            />
            <span class="collection-name">Создать коллекцию</span>
          </router-link>
        </div>
      </div>
      <form @submit.prevent="saveToCollection">
        <input type="hidden" name="collection_id" :value="selectedCollectionId" />
        <input type="hidden" name="recipe_id" :value="props.recipe_id" />
        <button class="btn-save" type="submit" :disabled="isLoading || isSaving || !selectedCollectionId">
          Сохранить
        </button>
      </form>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;
@use "../assets/styles/modal" as *;

</style>