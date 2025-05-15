<script setup>
import { ref, watch, computed } from "vue";
import { useCollectionStore } from "../../stores/collection";
import { useProfileAuth } from "../../composables/useProfileAuth";
import { useAuthStore } from "../../stores/auth";
import { useRouter, useRoute } from "vue-router";
import collectionService from "../../api/collectionService";
import apiClient from "../../api/apiClient";
import SelectMultiple from "../../components/SelectMultiple.vue";
import BaseIcon from "../../components/BaseIcon.vue";
import Input from "../../components/Input.vue";
import Select from "../../components/Select.vue";

const router = useRouter();
const route = useRoute();
const collectionStore = useCollectionStore();
const { isAuthenticated, currentUser } = useProfileAuth();
const authStore = useAuthStore();

// Данные коллекции и createData из route.meta
const collection = ref(route.meta.collection || {});
const data = ref(route.meta.data || { data: { marks: {}, mark_types: {}, product_types: {}, products: {}, privates: {} } });

// Проверка авторизации и данных
console.log("isAuthenticated:", isAuthenticated.value);
console.log("currentUser:", currentUser.value);
console.log("authStore.authKey:", authStore.authKey);
console.log("authStore.user:", authStore.user);
console.log("route.params.id:", route.params.id);
console.log("collection:", collection.value);
console.log("createData:", data.value);

// Доступ к данным
const marks = ref(data.value.data.marks);
const mark_types = ref(data.value.data.mark_types);
const product_types = ref(data.value.data.product_types);
const products = ref(data.value.data.products);
const privates = ref(data.value.data.privates);

// Состояние формы, инициализированное данными коллекции
const title = ref(collection.value.title || "");
const description = ref(collection.value.description || "");
const selectedPrivate = ref(collection.value.private_id || "");
const previewUrl = ref(collection.value.photo || null);
const collectionPhotoFile = ref(null);
const selectedMarks = ref(
  collection.value.marks?.filter(mark => mark && mark.id) || []
);
const selectedProducts = ref(
  collection.value.products?.filter(product => product && product.id) || []
);
const errors = ref({});
const textareaRef = ref(null);

// Отладка данных
console.log("marks:", marks.value);
console.log("mark_types:", mark_types.value);
console.log("products:", products.value);
console.log("product_types:", product_types.value);
console.log("privates:", privates.value);
console.log("selectedMarks:", selectedMarks.value);
console.log("selectedProducts:", selectedProducts.value);

// Обработка фото
const onFileSelected = (event) => {
  const file = event.target.files[0];
  if (file) {
    collectionPhotoFile.value = file;
    previewUrl.value = URL.createObjectURL(file);
  }
};

// Автоматическая подстройка высоты textarea
const adjustTextareaHeight = (textarea) => {
  if (textarea) {
    textarea.style.height = "auto";
    textarea.style.height = `${textarea.scrollHeight}px`;
  }
};

watch(description, () => adjustTextareaHeight(textareaRef.value));

// Нормализация серверных ошибок
const normalizeServerErrors = (serverErrors) => {
  const normalized = {};
  for (const [key, value] of Object.entries(serverErrors)) {
    normalized[key] = Array.isArray(value) ? value[0] : value;
  }
  return normalized;
};

// Отправка формы
const submitForm = async (event) => {
  event.preventDefault();

  // Проверка авторизации
  if (!isAuthenticated.value) {
    errors.value = { general: "Пожалуйста, войдите в систему для редактирования коллекции" };
    console.error("Пользователь не авторизован");
    router.push({ name: "login" });
    return;
  }

  // Проверка route.params.id
  if (!route.params.id) {
    errors.value = { general: "ID коллекции не указан" };
    console.error("route.params.id is undefined");
    router.push({ name: "home" });
    return;
  }

  // Проверка, что коллекция принадлежит пользователю
  if (collection.value.user_id !== authStore.user?.id) {
    errors.value = { general: "Вы не можете редактировать эту коллекцию" };
    console.error("Коллекция не принадлежит текущему пользователю", {
      collectionUserId: collection.value.user_id,
      currentUserId: authStore.user?.id,
    });
    router.push({ name: "home" });
    return;
  }

  // Синхронизация пользователя перед отправкой
  try {
    console.log("Синхронизация пользователя...");
    await authStore.syncUser();
    console.log("Пользователь после синхронизации:", authStore.user);
    console.log("authKey после синхронизации:", authStore.authKey);
  } catch (error) {
    console.error("Ошибка синхронизации пользователя:", error);
    errors.value = { general: "Ошибка авторизации. Пожалуйста, войдите снова." };
    authStore.clearUser();
    router.push({ name: "login" });
    return;
  }

  const formData = new FormData();
  formData.append("title", title.value);
  formData.append("description", description.value);
  formData.append("private_id", selectedPrivate.value);
  if (collectionPhotoFile.value) {
    formData.append("photo", collectionPhotoFile.value);
  }
  selectedMarks.value.forEach((mark, index) => {
    formData.append(`marks[${index}]`, mark.id);
  });
  selectedProducts.value.forEach((product, index) => {
    formData.append(`products[${index}]`, product.id);
  });

  try {
    console.log("Отправка PATCH-запроса с authKey:", authStore.authKey);
    console.log("FormData:", [...formData.entries()]);
    const response = await apiClient.patch(`/api/collection/${route.params.id}`, formData);
    console.log("API response:", response.data);
    
    if (response.data.success && response.data.collection?.id) {
      const collectionId = response.data.collection.id;
      router.push(`/collection/${collectionId}`);
    } else {
      throw new Error(response.data.message || "Ошибка API: некорректный ответ");
    }
  } catch (error) {
    console.error("Ошибка при редактировании коллекции:", error.response?.data || error.message);
    console.log("Статус ошибки:", error.response?.status);
    console.log("Данные ошибки:", error.response?.data);
    if (error.response?.status === 401) {
      errors.value = { general: "Не авторизован. Пожалуйста, войдите снова." };
      authStore.clearUser();
      router.push({ name: "login" });
    } else if (error.response?.status === 403) {
      errors.value = { general: "Вы не можете редактировать эту коллекцию. Возможно, неверный authKey или пользователь." };
      console.error("403 Forbidden:", {
        collectionUserId: collection.value.user_id,
        currentUserId: authStore.user?.id,
        authKey: authStore.authKey,
      });
      router.push({ name: "home" });
    } else if (error.response?.status === 404) {
      errors.value = { general: "Коллекция не найдена." };
      router.push({ name: "home" });
    } else if (error.response?.data?.errors) {
      errors.value = normalizeServerErrors(error.response.data.errors);
      console.log("Normalized server errors:", errors.value);
    } else {
      errors.value = { general: "Произошла ошибка. Попробуйте снова." };
    }
  }
};
</script>

<template>
  <form @submit="submitForm" class="create-form">
    <h1>Редактировать коллекцию</h1>
    <div v-if="errors.general" class="error-message general-error">
      {{ errors.general }}
    </div>
    <div class="btn-group start">
      <div v-if="previewUrl" class="preview">
        <img :src="previewUrl" alt="Превью загружаемого фото" />
      </div>
      <label class="btn-dark line" style="cursor: pointer">
        <input
          type="file"
          accept="image/*"
          style="display: none"
          @change="onFileSelected"
        />
        <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
        Загрузить новое фото
      </label>
      <div v-if="errors.photo" class="error-message">
        {{ errors.photo }}
      </div>
      <div class="input-title-wrapper">
        <input
          v-model="title"
          type="text"
          name="title"
          class="input-title"
          placeholder="Заголовок"
          :class="{ invalid: !!errors.title }"
        />
        <div v-if="errors.title" class="error-message">
          {{ errors.title }}
        </div>
      </div>
      <div class="input-description-wrapper">
        <textarea
          v-model="description"
          ref="textareaRef"
          name="description"
          class="input-description"
          placeholder="Описание"
          rows="3"
          :class="{ invalid: !!errors.description }"
        ></textarea>
        <div v-if="errors.description" class="error-message">
          {{ errors.description }}
        </div>
      </div>
      <div class="label-group">
        <Select
          v-model="selectedPrivate"
          label="Кому доступна коллекция?"
          name="private_id"
          placeholder="Выберите доступ"
          :options="privates"
          :is-invalid="!!errors.private_id"
          :error-message="errors.private_id"
        />
      </div>
      <label for="marks" class="marks">
        <SelectMultiple
          v-model="selectedMarks"
          name="mark"
          :query="route.query"
        />
        <div v-if="errors.marks" class="error-message">
          {{ errors.marks }}
        </div>
      </label>
      <label for="products" class="marks">
        <SelectMultiple
          v-model="selectedProducts"
          name="product"
          :query="route.query"
        />
        <div v-if="errors.products" class="error-message">
          {{ errors.products }}
        </div>
      </label>
    </div>
    <div class="btn-group end">
      <input class="btn-dark" type="submit" value="Сохранить изменения" />
    </div>
  </form>
</template>

<style lang="scss" scoped>
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/form" as *;

.marks {
  display: flex;
  flex-direction: column;
  gap: 0.9375rem; // 15px
  font-weight: 400;
  width: 100%;
}

.cooking {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.875rem; // 30px
  width: 100%;
  align-items: start;
}

// Адаптивность
@media (max-width: 1200px) {
  .cooking {
    grid-template-columns: 1fr;
    gap: 1.25rem; // 20px
  }
}

@media (max-width: 768px) {
  .cooking {
    gap: 1rem; // 16px
  }
}

@media (max-width: 480px) {
  .cooking {
    gap: 0.75rem; // 12px
  }
}
</style>