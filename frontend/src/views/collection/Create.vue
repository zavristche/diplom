<script setup>
import { ref, watch } from "vue";
import { useCollectionStore } from "../../stores/collection";
import { useProfileAuth } from "../../composables/useProfileAuth";
import { useAuthStore } from "../../stores/auth";
import { useRouter } from "vue-router";
import collectionService from "../../api/collectionService";
import SelectMultiple from "../../components/SelectMultiple.vue";
import BaseIcon from "../../components/BaseIcon.vue";
import Input from "../../components/Input.vue";
import Select from "../../components/Select.vue";

const router = useRouter();
const collectionStore = useCollectionStore();
const { isAuthenticated, currentUser } = useProfileAuth();
const authStore = useAuthStore();
const data = ref(collectionStore.createData);

// Проверка авторизации и данных
console.log("isAuthenticated:", isAuthenticated.value);
console.log("currentUser:", currentUser.value);
console.log("authStore.authKey:", authStore.authKey);
console.log("localStorage.auth_key:", localStorage.getItem("auth_key"));
console.log("localStorage.user:", localStorage.getItem("user"));

// Доступ к данным
const marks = ref(data.value.data.marks);
const mark_types = ref(data.value.data.mark_types);
const product_types = ref(data.value.data.product_types);
const products = ref(data.value.data.products);
const privates = ref(data.value.data.privates);

// Состояние формы
const title = ref("");
const description = ref("");
const selectedPrivate = ref("");
const previewUrl = ref(null);
const collectionPhotoFile = ref(null);
const selectedMarks = ref([]);
const selectedProducts = ref([]);
const errors = ref({});
const textareaRef = ref(null);

// Отладка данных
console.log("createData:", data.value);
console.log("marks:", marks.value);
console.log("mark_types:", mark_types.value);
console.log("products:", products.value);
console.log("product_types:", product_types.value);
console.log("privates:", privates.value);

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
    errors.value = { general: "Пожалуйста, войдите в систему для создания коллекции" };
    console.error("Пользователь не авторизован");
    router.push("/login");
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
    console.log("Отправка запроса с authKey:", authStore.authKey);
    const response = await collectionService.create(formData);
    if (response.data.success) {
      const collectionId = response.data.collection.id;
      router.push(`/collection/${collectionId}`);
    } else {
      throw new Error(response.data.message || "Ошибка API");
    }
  } catch (error) {
    console.error("Ошибка при создании коллекции:", error.response?.data || error.message);
    if (error.response?.status === 401) {
      errors.value = { general: "Не авторизован. Пожалуйста, войдите снова." };
      authStore.clearUser();
      router.push("/login");
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
    <h1>Создать коллекцию</h1>
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
        Загрузить фото
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
        Метки
        <SelectMultiple
          v-model="selectedMarks"
          name="mark"
          :query="{}"
        />
        <div v-if="errors.marks" class="error-message">
          {{ errors.marks }}
        </div>
      </label>
      <label for="products" class="marks">
        Продукты
        <SelectMultiple
          v-model="selectedProducts"
          name="product"
          :query="{}"
        />
        <div v-if="errors.products" class="error-message">
          {{ errors.products }}
        </div>
      </label>
    </div>
    <div class="btn-group end">
      <input class="btn-dark" type="submit" value="Отправить" />
    </div>
  </form>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;

.marks {
  display: flex;
  flex-direction: column;
  gap: 15px;
  font-weight: 400;
  width: 100%;
}

.create-form {
  display: flex;
  flex-direction: column;
  gap: 50px;

  .input-title {
    width: 100%;
    font-size: 32px;
    font-weight: 600;
    border: none;
    padding: 15px 0;
    border-radius: 0;

    &.invalid {
      border-bottom: 2px solid $error;
    }
  }

  .input-title-wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .input-description {
    display: flex;
    width: 100%;
    font-size: 20px;
    font-weight: 400;
    border: none;
    resize: none;
    overflow: hidden;
    line-height: 150%;
    padding: 15px 0;
    border-radius: 0;

    &::placeholder {
      font-weight: 300;
    }

    &.invalid {
      border-bottom: 2px solid $error;
    }
  }

  .input-description-wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .label-group {
    display: flex;
    flex-direction: row;
    gap: 30px;
    width: 100%;
    justify-content: space-between;
  }

  .label {
    display: flex;
    flex-direction: column;
    font-weight: 400;
    gap: 10px;
    width: 100%;
  }
}

.preview {
  display: flex;
  flex-shrink: 0;
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

.cooking {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  width: 100%;
  align-items: start;
}

.general-error {
  font-size: 18px;
  color: $error;
  text-align: center;
  padding: 10px;
  background-color: rgba($error, 0.1);
  border-radius: $border;
}

.ingredients {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  gap: 30px;
  width: 100%;
  height: auto;
  position: sticky;
  top: 6.25rem;

  .portions-container {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    font-weight: 400;
    width: 100%;
    justify-content: flex-end;
  }

  .portions {
    display: flex;
    flex-direction: row;
    align-items: center;
    border: 1px solid $text-info;
    border-radius: $border;
    font-size: 24px;
    font-weight: 400;

    input {
      width: 70px;
      text-align: center;
      border: none;
      background: transparent;
    }
  }

  .items {
    display: flex;
    flex-direction: column;
    gap: 20px;
    width: 100%;

    .btn-dark {
      justify-content: center;
      padding: 5px;
    }

    .ingredient {
      display: flex;
      align-items: center;
      gap: 15px;
      width: 100%;

      .ingredient__container {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        border-bottom: 1px solid $text-info-light;
        padding: 10px 0;

        .ingredient__fields {
          display: flex;
          flex-direction: row;
          align-items: center;
          gap: 10px;
          width: 100%;

          .label {
            width: auto;
            gap: 0;
          }

          .input-form {
            border: none;
            padding: 15px 0;
            width: 100%;
            font-size: 20px;
            font-weight: 400;
          }

          .custom-select {
            padding-right: 30px;
          }

          .select-arrow {
            right: 0;
            top: 50%;
            transform: translateY(-50%);
          }

          > *:first-child {
            flex-grow: 1;
          }

          > *:nth-child(2) {
            width: 60px;
            flex-shrink: 0;
          }

          > *:last-child {
            width: 90px;
            flex-shrink: 0;
          }
        }
      }
    }
  }
}

.error-message {
  font-size: 16px;
  color: $error;
  margin-top: 4px;
}
</style>