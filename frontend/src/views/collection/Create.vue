<script setup>
import { ref, watch, computed } from "vue";
import { useCollectionStore } from "../../stores/collection";
import { useProfileAuth } from "../../composables/useProfileAuth";
import { useAuthStore } from "../../stores/auth"; // Добавляем импорт
import { useRouter } from "vue-router";
import collectionService from "../../api/collectionService";
import BaseIcon from "../../components/BaseIcon.vue";
import Input from "../../components/Input.vue";
import Select from "../../components/Select.vue";

const router = useRouter();
const collectionStore = useCollectionStore();
const { isAuthenticated, currentUser } = useProfileAuth();
const authStore = useAuthStore(); // Инициализируем authStore
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
const searchMark = ref("");
const selectedMarks = ref([]);
const activeMarkType = ref(null);
const isMarkInputFocused = ref(false);
const searchProduct = ref("");
const selectedProducts = ref([]);
const activeProductType = ref(null);
const isProductInputFocused = ref(false);
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

// Фильтрация меток
const filteredMarks = computed(() => {
  const marksList = Object.values(marks.value || {});
  if (!searchMark.value && !activeMarkType.value) return marksList;

  return marksList.filter((mark) => {
    const matchesType = activeMarkType.value
      ? mark.type_id === Number(activeMarkType.value)
      : true;
    const searchValue = searchMark.value.toLowerCase();
    const markTitle = mark.title.toLowerCase();
    const matchesSearch = searchValue ? markTitle.includes(searchValue) : true;
    return matchesType && matchesSearch;
  });
});

// Фильтрация продуктов
const filteredProducts = computed(() => {
  const productsList = Object.values(products.value || {});
  if (!searchProduct.value && !activeProductType.value) return productsList;

  return productsList.filter((product) => {
    const matchesType = activeProductType.value
      ? product.type_id === Number(activeProductType.value)
      : true;
    const searchValue = searchProduct.value.toLowerCase();
    const productTitle = product.title.toLowerCase();
    const matchesSearch = searchValue ? productTitle.includes(searchValue) : true;
    return matchesType && matchesSearch;
  });
});

// Отладка фильтрованных списков
watch(filteredMarks, (newMarks) => {
  console.log("filteredMarks:", newMarks);
});
watch(filteredProducts, (newProducts) => {
  console.log("filteredProducts:", newProducts);
});

// Обработка меток
const selectMarkType = (typeId) => {
  activeMarkType.value = typeId;
  searchMark.value = "";
  isMarkInputFocused.value = true;
};

const addMark = (mark) => {
  if (!selectedMarks.value.some((m) => m.id === mark.id))
    selectedMarks.value.push(mark);
  searchMark.value = "";
  isMarkInputFocused.value = false;
};

const removeMark = (markId) => {
  selectedMarks.value = selectedMarks.value.filter((m) => m.id !== markId);
};

const handleMarkBlur = () => {
  setTimeout(() => (isMarkInputFocused.value = false), 200);
};

// Обработка продуктов
const selectProductType = (typeId) => {
  activeProductType.value = typeId;
  searchProduct.value = "";
  isProductInputFocused.value = true;
};

const addProduct = (product) => {
  if (!selectedProducts.value.some((p) => p.id === product.id))
    selectedProducts.value.push(product);
  searchProduct.value = "";
  isProductInputFocused.value = false;
};

const removeProduct = (productId) => {
  selectedProducts.value = selectedProducts.value.filter((p) => p.id !== productId);
};

const handleProductBlur = () => {
  setTimeout(() => (isProductInputFocused.value = false), 200);
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
        <div class="btn-group start">
          <button
            class="mark_type"
            :class="{ active: activeMarkType === null }"
            @click.prevent="selectMarkType(null)"
          >
            Все
          </button>
          <button
            v-for="(type, id) in mark_types"
            :key="id"
            class="mark_type"
            :class="{ active: activeMarkType === id }"
            @click.prevent="selectMarkType(id)"
          >
            {{ type }}
          </button>
        </div>
        <div class="mark-search">
          <div class="input-with-marks">
            <div class="mark-items">
              <span
                v-for="mark in selectedMarks"
                :key="mark.id"
                class="mark-item"
              >
                {{ mark.title }}
                <button class="mark-item__close" @click="removeMark(mark.id)">
                  <BaseIcon
                    viewBox="0 0 24 24"
                    class="icon-dark-15-1"
                    name="close"
                  />
                </button>
              </span>
              <input
                v-model="searchMark"
                type="text"
                class="input-form"
                placeholder="Поиск меток"
                @focus="isMarkInputFocused = true"
                @blur="handleMarkBlur"
              />
            </div>
          </div>
          <div v-if="isMarkInputFocused" class="mark-dropdown">
            <div v-if="filteredMarks.length" class="mark-options">
              <div
                v-for="mark in filteredMarks"
                :key="mark.id"
                class="mark-option"
                @click="addMark(mark)"
              >
                {{ mark.title }}
              </div>
            </div>
            <div v-else class="mark-option">Нет подходящих меток</div>
          </div>
        </div>
        <div v-if="errors.marks" class="error-message">
          {{ errors.marks }}
        </div>
      </label>
      <label for="products" class="marks">
        Продукты
        <div class="btn-group start">
          <button
            class="mark_type"
            :class="{ active: activeProductType === null }"
            @click.prevent="selectProductType(null)"
          >
            Все
          </button>
          <button
            v-for="(type, id) in product_types"
            :key="id"
            class="mark_type"
            :class="{ active: activeProductType === id }"
            @click.prevent="selectProductType(id)"
          >
            {{ type }}
          </button>
        </div>
        <div class="mark-search">
          <div class="input-with-marks">
            <div class="mark-items">
              <span
                v-for="product in selectedProducts"
                :key="product.id"
                class="mark-item"
              >
                {{ product.title }}
                <button class="mark-item__close" @click="removeProduct(product.id)">
                  <BaseIcon
                    viewBox="0 0 24 24"
                    class="icon-dark-15-1"
                    name="close"
                  />
                </button>
              </span>
              <input
                v-model="searchProduct"
                type="text"
                class="input-form"
                placeholder="Поиск продуктов"
                @focus="isProductInputFocused = true"
                @blur="handleProductBlur"
              />
            </div>
          </div>
          <div v-if="isProductInputFocused" class="mark-dropdown">
            <div v-if="filteredProducts.length" class="mark-options">
              <div
                v-for="product in filteredProducts"
                :key="product.id"
                class="mark-option"
                @click="addProduct(product)"
              >
                {{ product.title }}
              </div>
            </div>
            <div v-else class="mark-option">Нет подходящих продуктов</div>
          </div>
        </div>
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

  .input-with-marks {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
  }

  .mark-items {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px;
    border: 1px solid $text-info-light;
    border-radius: $border;
    min-height: 50px;
    align-items: center;
    width: 100%;

    .mark-item {
      display: flex;
      flex-direction: row;
      gap: 5px;
      align-items: center;
      background-color: $background;
      padding: 5px 10px;
      border-radius: $border;
      box-shadow: $shadow;

      .mark-item__close {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
      }
    }

    .input-form {
      border: none;
      flex-grow: 1;
      padding: 5px;
      font-size: 20px;
      font-weight: 400;
      background: transparent;
      outline: none;
      min-width: 100px;
    }
  }

  .mark-search {
    position: relative;
  }

  .mark-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background-color: $light;
    border: 1px solid $text-info-light;
    border-radius: $border;
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
    box-shadow: $shadow;
  }

  .mark-option {
    padding: 10px 20px;
    cursor: pointer;

    &:hover {
      background-color: $background;
    }
  }
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