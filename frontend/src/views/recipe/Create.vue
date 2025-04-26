<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useProfileAuth } from "../../composables/useProfileAuth";
import { useAuthStore } from "../../stores/auth";
import RecipeService from "../../api/RecipeService";
import BaseIcon from "../../components/BaseIcon.vue";
import Input from "../../components/Input.vue";
import Select from "../../components/Select.vue";

const route = useRoute();
const router = useRouter();
const { isAuthenticated, currentUser } = useProfileAuth();
const authStore = useAuthStore();
const data = route.meta.data.data;

// Проверка авторизации и данных
console.log("isAuthenticated:", isAuthenticated.value);
console.log("currentUser:", currentUser.value);
console.log("authStore.authKey:", authStore.authKey);
console.log("localStorage.auth_key:", localStorage.getItem("auth_key"));
console.log("localStorage.user:", localStorage.getItem("user"));

// Состояние формы
const title = ref("");
const description = ref("");
const time = ref("");
const selectedComplexity = ref("");
const selectedPrivate = ref("");
const portions = ref(1);
const ingredients = ref([]);
const steps = ref([{ description: "", previewUrl: null, file: null }]);
const previewUrl = ref(null);
const recipePhotoFile = ref(null);
const searchMark = ref("");
const selectedMarks = ref([]);
const activeMarkType = ref(null);
const isMarkInputFocused = ref(false);

// Ошибки валидации
const errors = ref({});

// Refs для textarea
const textareaRef = ref(null);
const stepTextareaRefs = ref([]);

// Добавление ингредиента
const addIngredient = () => {
  ingredients.value.push({ product_id: "", count: "", measure_id: "" });
};

// Удаление ингредиента
const removeIngredient = (index) => {
  ingredients.value.splice(index, 1);
};

// Обработка порций
const updatePortions = (delta) => {
  portions.value = Math.max(1, portions.value + delta);
};

// Добавление шага
const addStep = () => {
  steps.value.push({ description: "", previewUrl: null, file: null });
};

// Удаление шага
const removeStep = (index) => {
  if (steps.value.length > 1) {
    steps.value.splice(index, 1);
  }
};

// Обработка основного фото
const onFileSelected = (event) => {
  const file = event.target.files[0];
  if (file) {
    recipePhotoFile.value = file;
    previewUrl.value = URL.createObjectURL(file);
  }
};

// Обработка фото шага
const onStepFileSelected = (event, index) => {
  const file = event.target.files[0];
  if (file) {
    steps.value[index].file = file;
    steps.value[index].previewUrl = URL.createObjectURL(file);
  }
};

// Фильтрация меток
const filteredMarks = computed(() => {
  const marks = Object.values(data.marks || {});
  if (!searchMark.value && !activeMarkType.value) return marks;

  return marks.filter((mark) => {
    const matchesType = activeMarkType.value
      ? mark.type_id === Number(activeMarkType.value)
      : true;
    const searchValue = searchMark.value.toLowerCase();
    const markTitle = mark.title.toLowerCase();
    const matchesSearch = searchValue ? markTitle.includes(searchValue) : true;
    return matchesType && matchesSearch;
  });
});

// Обработка меток
const selectMarkType = (typeId) => {
  activeMarkType.value = typeId;
  searchMark.value = "";
  isMarkInputFocused.value = true;
};

const addMark = (mark) => {
  if (!selectedMarks.value.some((m) => m.id === mark.id)) {
    selectedMarks.value.push(mark);
  }
  searchMark.value = "";
  isMarkInputFocused.value = false;
};

const removeMark = (markId) => {
  selectedMarks.value = selectedMarks.value.filter((m) => m.id !== markId);
};

const handleBlur = () => {
  setTimeout(() => {
    isMarkInputFocused.value = false;
  }, 200);
};

// Автоматическая подстройка высоты textarea
const adjustTextareaHeight = (textarea) => {
  if (textarea) {
    textarea.style.height = "auto";
    textarea.style.height = `${textarea.scrollHeight}px`;
  }
};

watch(description, () => {
  adjustTextareaHeight(textareaRef.value);
});

watch(
  () => steps.value.map((step) => step.description),
  () => {
    stepTextareaRefs.value.forEach((textarea) => adjustTextareaHeight(textarea));
  },
  { deep: true }
);

onMounted(() => {
  adjustTextareaHeight(textareaRef.value);
  stepTextareaRefs.value.forEach((textarea) => adjustTextareaHeight(textarea));
});

// Нормализация серверных ошибок
const normalizeServerErrors = (serverErrors) => {
  const normalized = {};
  for (const [key, value] of Object.entries(serverErrors)) {
    normalized[key] = Array.isArray(value) ? value[0] : value;
  }
  return normalized;
};

// Валидация формы
const validateForm = () => {
  errors.value = {};

  if (!title.value) errors.value.title = "Заголовок обязателен";
  if (!description.value) errors.value.description = "Описание обязательно";
  if (!time.value) errors.value.time = "Укажите время приготовления";
  if (!selectedComplexity.value) errors.value.complexity_id = "Выберите сложность";
  if (!selectedPrivate.value) errors.value.private_id = "Выберите доступ";
  if (ingredients.value.length === 0) errors.value.ingredients = "Добавьте хотя бы один ингредиент";
  ingredients.value.forEach((ingredient, index) => {
    if (!ingredient.product_id) errors.value[`products[${index}][product_id]`] = "Выберите продукт";
    if (!ingredient.count) errors.value[`products[${index}][count]`] = "Укажите количество";
    if (!ingredient.measure_id) errors.value[`products[${index}][measure_id]`] = "Выберите единицу измерения";
  });
  if (steps.value.length === 0) errors.value.steps = "Добавьте хотя бы один шаг";
  steps.value.forEach((step, index) => {
    if (!step.description) errors.value[`steps[${index}][description]`] = "Описание шага обязательно";
  });

  return Object.keys(errors.value).length === 0;
};

// Отправка формы
const submitForm = async (event) => {
  event.preventDefault();

  // Проверка авторизации
  if (!isAuthenticated.value) {
    errors.value = { general: "Пожалуйста, войдите в систему для создания рецепта" };
    console.error("Пользователь не авторизован");
    router.push({ name: "login" }).catch(() => {
      console.error("Маршрут /login не найден. Проверьте router.js");
      errors.value = { general: "Ошибка: страница входа недоступна" };
    });
    return;
  }

  if (!validateForm()) {
    console.log("Validation errors:", errors.value);
    return;
  }

  const formData = new FormData();
  formData.append("title", title.value);
  formData.append("description", description.value);
  formData.append("time", time.value);
  formData.append("complexity_id", selectedComplexity.value);
  formData.append("private_id", selectedPrivate.value);
  formData.append("portions", portions.value);

  if (recipePhotoFile.value) {
    formData.append("recipe_photo", recipePhotoFile.value);
  }

  ingredients.value.forEach((ingredient, index) => {
    formData.append(`products[${index}][product_id]`, ingredient.product_id);
    formData.append(`products[${index}][count]`, ingredient.count);
    formData.append(`products[${index}][measure_id]`, ingredient.measure_id);
  });

  steps.value.forEach((step, index) => {
    formData.append(`steps[${index}][title]`, `Шаг ${index + 1}`);
    formData.append(`steps[${index}][description]`, step.description);
    if (step.file) {
      formData.append(`step_photos[${index}]`, step.file);
    }
  });

  selectedMarks.value.forEach((mark, index) => {
    formData.append(`marks[${index}]`, mark.id);
  });

  try {
    console.log("Отправка запроса с authKey:", authStore.authKey);
    console.log("Sending FormData:");
    for (let [key, value] of formData.entries()) {
      console.log(`${key}: ${value instanceof File ? value.name : value}`);
    }

    const response = await RecipeService.create(formData);
    console.log("Server response:", response.data);

    if (response.data.success) {
      const recipeId = response.data.recipe.id;
      router.push(`/recipe/${recipeId}`);
    } else {
      throw new Error(response.data.message || "Ошибка API");
    }
  } catch (error) {
    console.error("Ошибка при создании рецепта:", error.response?.data || error.message);
    if (error.response?.status === 401) {
      errors.value = { general: "Не авторизован. Пожалуйста, войдите снова." };
      authStore.clearUser();
      router.push({ name: "login" }).catch(() => {
        console.error("Маршрут /login не найден. Проверьте router.js");
        errors.value = { general: "Ошибка: страница входа недоступна" };
      });
    } else if (error.response?.data?.errors) {
      errors.value = normalizeServerErrors(error.response.data.errors);
      console.log("Normalized server errors:", errors.value);
    } else {
      errors.value = { general: error.response?.data?.message || "Произошла ошибка. Попробуйте снова." };
    }
  }
};
</script>

<template>
  <form @submit="submitForm" class="create-form">
    <h1>Создать рецепт</h1>
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
      <div v-if="errors.recipe_photo" class="error-message">
        {{ errors.recipe_photo }}
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
        <Input
          v-model="time"
          label="Время приготовления"
          name="time"
          placeholder="15 минут"
          :is-invalid="!!errors.time"
          :error-message="errors.time ? ` ${errors.time}` : ''"
        />
        <Select
          v-model="selectedComplexity"
          label="Сложность"
          name="complexity_id"
          placeholder="Выберите сложность"
          :options="data.complexities"
          :is-invalid="!!errors.complexity_id"
          :error-message="errors.complexity_id ? ` ${errors.complexity_id}` : ''"
        />
        <Select
          v-model="selectedPrivate"
          label="Кому доступен рецепт?"
          name="private_id"
          placeholder="Выберите доступ"
          :options="data.privates"
          :is-invalid="!!errors.private_id"
          :error-message="errors.private_id ? ` ${errors.private_id}` : ''"
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
            v-for="(type, id) in data.mark_types"
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
                @blur="handleBlur"
              />
            </div>
          </div>
          <div
            v-if="isMarkInputFocused && filteredMarks.length"
            class="mark-dropdown"
          >
            <div
              v-for="mark in filteredMarks"
              :key="mark.id"
              class="mark-option"
              @click="addMark(mark)"
            >
              {{ mark.title }}
            </div>
          </div>
        </div>
      </label>
    </div>
    <div class="cooking">
      <div class="ingredients">
        <h2>Ингредиенты</h2>
        <div class="portions-container">
          <span>Порции</span>
          <div class="portions">
            <button type="button" @click="updatePortions(-1)">
              <BaseIcon
                viewBox="0 0 65 65"
                class="icon-dark-45-2"
                name="minus"
              />
            </button>
            <Input
              v-model="portions"
              name="portions"
              type="number"
              :is-invalid="!!errors.portions"
              :error-message="errors.portions ? ` ${errors.portions}` : ''"
            />
            <button type="button" @click="updatePortions(1)">
              <BaseIcon
                viewBox="0 0 65 65"
                class="icon-dark-45-2"
                name="pluse"
              />
            </button>
          </div>
        </div>
        <div class="items">
          <button class="btn-dark" @click.prevent="addIngredient">
            <BaseIcon
              viewBox="0 0 65 65"
              class="icon-white-45-3"
              name="pluse"
            />
            Добавить продукт
          </button>
          <label
            v-for="(ingredient, index) in ingredients"
            :key="index"
            class="ingredient"
            for="products"
          >
            <button type="button" @click="removeIngredient(index)">
              <BaseIcon
                viewBox="0 0 65 65"
                class="icon-dark-45-2"
                name="minus"
              />
            </button>
            <div class="ingredient__container">
              <div class="ingredient__fields">
                <Select
                  v-model="ingredient.product_id"
                  :name="'products[' + index + '][product_id]'"
                  placeholder="Выберите продукт"
                  :options="data.products"
                  :is-invalid="!!errors[`products[${index}][product_id]`]"
                />
                <Input
                  v-model="ingredient.count"
                  :name="'products[' + index + '][count]'"
                  type="number"
                  placeholder="N"
                  :is-invalid="!!errors[`products[${index}][count]`]"
                />
                <Select
                  v-model="ingredient.measure_id"
                  :name="'products[' + index + '][measure_id]'"
                  placeholder="Ед."
                  :options="data.measures"
                  :is-invalid="!!errors[`products[${index}][measure_id]`]"
                />
              </div>
              <div
                v-if="
                  errors[`products[${index}][product_id]`] ||
                  errors[`products[${index}][count]`] ||
                  errors[`products[${index}][measure_id]`]
                "
                class="error-message"
              >
                {{
                  [
                    errors[`products[${index}][product_id]`],
                    errors[`products[${index}][count]`],
                    errors[`products[${index}][measure_id]`],
                  ]
                    .filter(Boolean)
                    .join(", ")
                }}
              </div>
            </div>
          </label>
          <div v-if="errors.ingredients" class="error-message">
            {{ errors.ingredients }}
          </div>
        </div>
      </div>
      <label for="steps" class="steps">
        <h2>Шаги приготовления</h2>
        <button class="btn-dark" @click.prevent="addStep">
          <BaseIcon viewBox="0 0 65 65" class="icon-white-45-3" name="pluse" />
          Добавить шаг
        </button>
        <section v-for="(step, index) in steps" :key="index" class="step">
          <div class="preview" v-if="step.previewUrl">
            <img :src="step.previewUrl" alt="Превью шага" />
          </div>
          <div class="btn-group start">
            <button class="btn-light" @click.prevent="removeStep(index)">
              <BaseIcon
                viewBox="0 0 29 29"
                class="icon-white-30-2"
                name="close"
              />
            </button>
            <label class="btn-dark line" style="cursor: pointer">
              <input
                type="file"
                accept="image/*"
                style="display: none"
                @change="onStepFileSelected($event, index)"
              />
              <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
              Загрузить фото
            </label>
          </div>
          <div v-if="errors[`step_photo_${index}`]" class="error-message">
            {{ errors[`step_photo_${index}`] }}
          </div>
          <div class="step__info">
            <div class="input-title-wrapper">
              <input
                type="text"
                class="input-title"
                :value="`Шаг ${index + 1}`"
                readonly
              />
            </div>
            <div class="input-description-wrapper">
              <textarea
                v-model="step.description"
                :ref="(el) => (stepTextareaRefs[index] = el)"
                :name="'steps[' + index + '][description]'"
                class="input-description"
                placeholder="Описание"
                rows="3"
                :class="{ invalid: !!errors[`steps[${index}][description]`] }"
              ></textarea>
              <div
                v-if="errors[`steps[${index}][description]`]"
                class="error-message"
              >
                {{ errors[`steps[${index}][description]`] }}
              </div>
            </div>
          </div>
        </section>
        <div v-if="errors.steps" class="error-message">
          {{ errors.steps }}
        </div>
      </label>
    </div>
    <div class="btn-group">
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

  .btn-group {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    justify-content: flex-end;
    gap: 30px;

    &.start {
      justify-content: flex-start;
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