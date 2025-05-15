<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useProfileAuth } from "../../composables/useProfileAuth";
import { useAuthStore } from "../../stores/auth";
import RecipeService from "../../api/RecipeService";
import SelectMultiple from "../../components/SelectMultiple.vue";
import BaseIcon from "../../components/BaseIcon.vue";
import Input from "../../components/Input.vue";
import Select from "../../components/Select.vue";
import Portions from "../../components/Portions.vue";
import Ingredient from "../../components/Ingredient.vue";

const route = useRoute();
const router = useRouter();
const { isAuthenticated } = useProfileAuth();
const authStore = useAuthStore();
const data = route.meta.data?.data;
const recipe = route.meta.recipe;
const recipeId = route.params.id;

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
const selectedMarks = ref([]);
const isEditable = ref(true);
const errors = ref({});

const textareaRef = ref(null);
const stepTextareaRefs = ref([]);

const tasteMeasureId = ref(null);

const initializeForm = () => {
  if (!recipeId || !recipe) {
    errors.value = { general: "Рецепт не найден." };
    router.push({ name: "home" });
    return;
  }

  const recipeUserId = recipe.user_id;
  const currentUserId = authStore.userId;

  if (currentUserId && recipeUserId !== currentUserId) {
    isEditable.value = false;
    errors.value = { general: "Вы не можете редактировать этот рецепт." };
  } else if (!isAuthenticated.value) {
    errors.value = { general: "Пожалуйста, войдите в систему." };
    router.push({ name: "login" });
    return;
  }

  fillForm(recipe);
};

const fillForm = async (recipe) => {
  if (!recipe) {
    errors.value = { general: "Данные рецепта недоступны." };
    return;
  }

  title.value = recipe.title ?? "";
  description.value = recipe.description ?? "";
  time.value = recipe.time ?? "";
  selectedComplexity.value = recipe.complexity_id ?? "";
  selectedPrivate.value = recipe.private_id ?? "";
  portions.value = recipe.portions ?? 1;

  ingredients.value =
    recipe.products?.map((product) => ({
      id: product.id,
      product_id: product.product_id,
      count: product.count != null ? Number(product.count) : null,
      measure_id: product.measure_id,
    })) ?? [];

  await nextTick();

  steps.value = recipe.steps?.map((step) => ({
    id: step.id,
    description: step.description ?? "",
    previewUrl: step.photo ? `${step.photo}` : null,
    file: null,
  })) ?? [{ description: "", previewUrl: null, file: null }];

  previewUrl.value = recipe.photo ? `${recipe.photo}` : null;
  selectedMarks.value = recipe.marks ?? [];
};

const addIngredient = () => {
  if (!isEditable.value) return;
  ingredients.value.push({
    id: `new-${Date.now()}`,
    product_id: null,
    count: null,
    measure_id: null,
  });
};

const removeIngredient = (index) => {
  if (!isEditable.value) return;
  ingredients.value = ingredients.value.filter((_, i) => i !== index);
};

const updatePortions = (delta) => {
  if (!isEditable.value) return;
  portions.value = Math.max(1, portions.value + delta);
};

const addStep = () => {
  if (!isEditable.value) return;
  steps.value.push({ description: "", previewUrl: null, file: null });
};

const removeStep = (index) => {
  if (!isEditable.value || steps.value.length <= 1) return;
  steps.value.splice(index, 1);
};

const onFileSelected = (event) => {
  if (!isEditable.value) return;
  const file = event.target.files[0];
  if (file) {
    recipePhotoFile.value = file;
    previewUrl.value = URL.createObjectURL(file);
  }
};

const onStepFileSelected = (event, index) => {
  if (!isEditable.value) return;
  const file = event.target.files[0];
  if (file) {
    steps.value[index].file = file;
    steps.value[index].previewUrl = URL.createObjectURL(file);
  }
};

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
    stepTextareaRefs.value.forEach((textarea) =>
      adjustTextareaHeight(textarea)
    );
  },
  { deep: true }
);

onMounted(() => {
  initializeForm();
  adjustTextareaHeight(textareaRef.value);
  stepTextareaRefs.value.forEach((textarea) => adjustTextareaHeight(textarea));
});

const normalizeServerErrors = (serverErrors) => {
  const normalized = {};
  for (const [key, value] of Object.entries(serverErrors)) {
    if (["steps", "products", "marks", "imageFile"].includes(key)) {
      normalized[key] = Array.isArray(value) ? value.join(", ") : value;
    } else if (key.startsWith("step_")) {
      const index = parseInt(key.replace("step_", ""), 10);
      if (typeof value === "string") {
        normalized[`steps[${index}][description]`] = value;
      } else if (typeof value === "object") {
        for (const [attr, messages] of Object.entries(value)) {
          normalized[
            attr === "imageFile"
              ? `step_photos_${index}`
              : `steps[${index}][${attr}]`
          ] = Array.isArray(messages) ? messages.join(", ") : messages;
        }
      }
    } else if (key.startsWith("product_")) {
      const index = parseInt(key.replace("product_", ""), 10);
      normalized[`products[${index}]`] =
        typeof value === "string"
          ? value
          : Object.values(value).flat().filter(Boolean).join(", ");
    } else if (key.startsWith("mark_")) {
      const index = parseInt(key.replace("mark_", ""), 10);
      normalized[`marks[${index}]`] =
        typeof value === "string"
          ? value
          : Object.values(value).flat().join(", ");
    } else {
      normalized[key] = Array.isArray(value) ? value.join(", ") : value;
    }
  }
  return normalized;
};

const validateForm = () => {
  errors.value = {};
  ingredients.value.forEach((ingredient, index) => {
    const productErrors = [];
    if (!ingredient.product_id) productErrors.push("Выберите продукт");
    if (!ingredient.measure_id) productErrors.push("Выберите меру");
    if (!ingredient.count && ingredient.measure_id !== tasteMeasureId.value)
      productErrors.push("Укажите количество");
    if (productErrors.length)
      errors.value[`products[${index}]`] = productErrors.join(", ");
  });
  steps.value.forEach((step, index) => {
    if (!step.description)
      errors.value[`steps[${index}][description]`] = "Укажите описание шага";
    if (!step.previewUrl && !step.file)
      errors.value[`step_photos_${index}`] = "Загрузите фото шага";
  });
  if (!title.value) errors.value.title = "Укажите заголовок";
  if (!description.value) errors.value.description = "Укажите описание рецепта";
  if (!time.value) errors.value.time = "Укажите время приготовления";
  if (!selectedComplexity.value)
    errors.value.complexity_id = "Выберите сложность";
  if (!selectedPrivate.value) errors.value.private_id = "Выберите доступ";
  if (!portions.value) errors.value.portions = "Укажите количество порций";
  if (!previewUrl.value && !recipePhotoFile.value)
    errors.value.imageFile = "Загрузите фото рецепта";
  if (!selectedMarks.value.length)
    errors.value.marks = "Выберите хотя бы одну метку";
  return !Object.keys(errors.value).length;
};

const submitForm = async (event) => {
  event.preventDefault();
  if (!isEditable.value) {
    errors.value = { general: "Редактирование запрещено." };
    return;
  }
  if (!isAuthenticated.value) {
    errors.value = { general: "Пожалуйста, войдите в систему." };
    router.push({ name: "login" });
    return;
  }
  if (!validateForm()) return;

  const formData = new FormData();
  formData.append("title", title.value || "");
  formData.append("description", description.value || "");
  formData.append("time", time.value || "");
  formData.append("complexity_id", selectedComplexity.value || "");
  formData.append("private_id", selectedPrivate.value || "");
  formData.append("portions", portions.value || "");
  if (recipePhotoFile.value)
    formData.append("recipe_photo", recipePhotoFile.value);

  ingredients.value.forEach((ingredient, index) => {
    if (ingredient.id) formData.append(`products[${index}][id]`, ingredient.id);
    formData.append(
      `products[${index}][product_id]`,
      ingredient.product_id || ""
    );
    formData.append(`products[${index}][count]`, ingredient.count ?? "");
    formData.append(
      `products[${index}][measure_id]`,
      ingredient.measure_id || ""
    );
  });

  steps.value.forEach((step, index) => {
    if (step.id) formData.append(`steps[${index}][id]`, step.id);
    formData.append(`steps[${index}][title]`, `Шаг ${index + 1}`);
    formData.append(`steps[${index}][description]`, step.description || "");
    if (step.file) formData.append(`step_photos[${index}]`, step.file);
  });

  selectedMarks.value.forEach((mark, index) => {
    formData.append(`marks[${index}]`, mark.id);
  });

  try {
    const response = await RecipeService.update(recipeId, formData);
    if (response.data.success) {
      router.push(`/recipe/${recipeId}`);
    } else {
      throw new Error(response.data.message || "Ошибка API");
    }
  } catch (error) {
    if (error.response?.status === 401) {
      errors.value = { general: "Не авторизован. Войдите снова." };
      authStore.clearUser();
      router.push({ name: "login" });
    } else if (error.response?.status === 422 && error.response?.data?.errors) {
      errors.value = normalizeServerErrors(error.response.data.errors);
    } else {
      errors.value = {
        general: error.response?.data?.message || "Произошла ошибка.",
      };
    }
  }
};
</script>

<template>
  <form @submit="submitForm" class="create-form">
    <h1>Редактировать рецепт</h1>
    <div v-if="errors.general" class="error-message general-error">
      {{ errors.general }}
    </div>
    <div class="btn-group start">
      <div v-if="previewUrl" class="preview">
        <img :src="previewUrl" alt="Превью фото" />
      </div>
      <div class="photo-upload-wrapper">
        <label v-if="isEditable" class="btn-dark line">
          <input
            type="file"
            accept="image/*"
            name="recipe_photo"
            style="display: none"
            @change="onFileSelected"
          />
          <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
          Загрузить фото
        </label>
        <div v-if="errors.imageFile" class="photo-error">
          {{ errors.imageFile }}
        </div>
      </div>
      <div class="input-title-wrapper">
        <input
          v-model="title"
          type="text"
          name="title"
          class="input-title"
          placeholder="Заголовок"
          :class="{ invalid: errors.title }"
          :disabled="!isEditable"
        />
        <div v-if="errors.title" class="error-message">{{ errors.title }}</div>
      </div>
      <div class="input-description-wrapper">
        <textarea
          v-model="description"
          ref="textareaRef"
          name="description"
          class="input-description"
          placeholder="Описание"
          rows="3"
          :class="{ invalid: errors.description }"
          :disabled="!isEditable"
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
          :error-message="errors.time"
          :disabled="!isEditable"
        />
        <Select
          v-model="selectedComplexity"
          label="Сложность"
          name="complexity_id"
          placeholder="Выберите сложность"
          :options="data.complexities"
          :is-invalid="!!errors.complexity_id"
          :error-message="errors.complexity_id"
          :disabled="!isEditable"
        />
        <Select
          v-model="selectedPrivate"
          label="Кому доступен рецепт?"
          name="private_id"
          placeholder="Выберите доступ"
          :options="data.privates"
          :is-invalid="!!errors.private_id"
          :error-message="errors.private_id"
          :disabled="!isEditable"
        />
      </div>
      <label for="marks" class="marks">
        <SelectMultiple
          v-model="selectedMarks"
          name="mark"
          :query="route.query"
        />
        <div v-if="errors.marks" class="error-message">{{ errors.marks }}</div>
      </label>
    </div>
    <div class="cooking">
      <div class="ingredients">
        <h2>Ингредиенты</h2>
        <Portions
          v-model:portions="portions"
          :errors="errors"
          :is-editable="isEditable"
        />
        <div class="items">
          <button
            v-if="isEditable"
            class="btn-dark"
            @click.prevent="addIngredient"
          >
            <BaseIcon viewBox="0 0 65 65" class="icon-white-45-3" name="plus" />
            Добавить продукт
          </button>
          <Ingredient
            v-for="(ingredient, index) in ingredients"
            :key="ingredient.id"
            :ingredient="ingredient"
            :index="index"
            :data="data"
            :errors="errors"
            :is-editable="isEditable"
            @remove="removeIngredient"
          />
          <div v-if="errors.products" class="error-message">
            {{ errors.products }}
          </div>
        </div>
      </div>
      <label for="steps" class="steps">
        <h2>Шаги приготовления</h2>
        <button v-if="isEditable" class="btn-dark" @click.prevent="addStep">
          <BaseIcon viewBox="0 0 65 65" class="icon-white-45-3" name="plus" />
          Добавить шаг
        </button>
        <section
          v-for="(step, index) in steps"
          :key="step.id || index"
          class="step"
        >
          <div class="step-actions">
            <button
              v-if="isEditable"
              class="btn-light btn-icon btn-small"
              @click.prevent="removeStep(index)"
            >
              <BaseIcon
                viewBox="0 0 29 29"
                class="icon-white-30-2"
                name="close"
              />
            </button>
          </div>
          <div class="photo-upload-wrapper">
            <label v-if="isEditable" class="btn-dark line full-width">
              <input
                type="file"
                accept="image/*"
                :name="'step_photos[' + index + ']'"
                style="display: none"
                @change="onStepFileSelected($event, index)"
              />
              <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
              Загрузить фото
            </label>
            <div v-if="errors[`step_photos_${index}`]" class="photo-error">
              {{ errors[`step_photos_${index}`] }}
            </div>
          </div>
          <div v-if="step.previewUrl" class="preview">
            <img :src="step.previewUrl" alt="Превью шага" />
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
                :class="{ invalid: errors[`steps[${index}][description]`] }"
                :disabled="!isEditable"
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
        <div v-if="errors.steps" class="error-message">{{ errors.steps }}</div>
      </label>
    </div>
    <div class="btn-group end">
      <input
        v-if="isEditable"
        class="btn-dark"
        type="submit"
        value="Сохранить изменения"
      />
      <button
        v-else
        class="btn-dark"
        @click.prevent="router.push(`/recipe/${recipeId}`)"
      >
        Вернуться к рецепту
      </button>
    </div>
  </form>
</template>

<style lang="scss" scoped>
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/form" as *;

.create-form {
  display: flex;
  flex-direction: column;
  gap: 2rem; // 32px

  .input-title {
    width: 100%;
    font-size: clamp(1.5rem, 5vw, 2rem); // 24-32px
    font-weight: 600;
    border: none;
    padding: 0.75rem 0; // 12px
    border-radius: 0;
    &.invalid {
      border-bottom: 2px solid $error;
    }
  }

  .input-title-wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.25rem; // 4px
  }

  .input-description {
    width: 100%;
    font-size: clamp(1rem, 3vw, 1.25rem); // 16-20px
    font-weight: 400;
    border: none;
    resize: none;
    overflow: hidden;
    line-height: 150%;
    padding: 0.75rem 0; // 12px
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
    gap: 0.25rem; // 4px
  }

  .label-group {
    display: flex;
    flex-direction: column;
    gap: 1rem; // 16px
    width: 100%;

    @media (min-width: 768px) {
      flex-direction: row;
      gap: 1.875rem; // 30px
    }
  }

  .preview {
    flex-shrink: 0;
    width: 100%;
    height: auto;
    max-height: 31.25rem; // 500px
    img {
      box-shadow: $shadow;
      object-fit: cover;
      width: 100%;
      height: 100%;
      border-radius: $border;
    }
  }

  .cooking {
    display: flex;
    flex-direction: column;
    gap: 2rem; // 32px
    width: 100%;

    @media (min-width: 992px) {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.875rem; // 30px
    }
  }

  .general-error {
    font-size: 1rem; // 16px
    color: $error;
    text-align: center;
    padding: 0.625rem; // 10px
    background: rgba($error, 0.1);
    border-radius: $border;

    @media (min-width: 768px) {
      font-size: 1.125rem; // 18px
    }
  }

  .photo-upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.125rem; // 2px
    width: 100%;
  }

  .step-actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 0.625rem; // 10px
  }

  .btn-dark.full-width {
    width: 100%;
    box-sizing: border-box;
  }
}

.marks {
  display: flex;
  flex-direction: column;
  gap: 0.9375rem; // 15px
  font-weight: 400;
  width: 100%;
}

.ingredients {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  gap: 1.25rem; // 20px
  width: 100%;
  height: auto;

  @media (min-width: 992px) {
    position: sticky;
    top: 6.25rem; // 100px
    gap: 1.875rem; // 30px
  }

  .items {
    display: flex;
    flex-direction: column;
    gap: 1rem; // 16px
    width: 100%;

    @media (min-width: 768px) {
      gap: 1.25rem; // 20px
    }

    .btn-dark {
      justify-content: center;
      padding: 0.3125rem; // 5px
    }
  }
}

.error-message {
  font-size: 0.875rem; // 14px
  color: $error;
  margin-top: 0.25rem; // 4px

  @media (min-width: 768px) {
    font-size: 1rem; // 16px
  }
}

.photo-error {
  font-size: 0.875rem; // 14px
  color: $error;
  margin-top: 0.125rem; // 2px

  @media (min-width: 768px) {
    font-size: 1rem; // 16px
  }
}

// Дополнительные адаптивные стили
.btn-group {
  &.start,
  &.end {
    display: flex;
    flex-direction: column;
    gap: 1rem; // 16px
  }
}

.step {
  margin-bottom: 1.5rem; // 24px

  &:last-child {
    margin-bottom: 0;
  }
}

// Оптимизация для очень маленьких экранов
@media (max-width: 480px) {
  .create-form {
    gap: 1.5rem; // 24px
  }

  .ingredients {
    gap: 1rem; // 16px
  }

  .step {
    margin-bottom: 1rem; // 16px
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
    gap: 30px;
    width: 100%;
    justify-content: space-between;
  }

  .preview {
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
    background: rgba($error, 0.1);
    border-radius: $border;
  }

  .photo-upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 2px;
    width: 100%;
  }

  .step-actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
  }

  .btn-dark.full-width {
    width: 100%;
    box-sizing: border-box;
  }
}

.marks {
  display: flex;
  flex-direction: column;
  gap: 15px;
  font-weight: 400;
  width: 100%;
}

.ingredients {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  gap: 1.875rem; // 30px
  width: 100%;
  height: auto;
  position: sticky;
  top: 6.25rem;

  .items {
    display: flex;
    flex-direction: column;
    gap: 1.25rem; // 20px
    width: 100%;

    .btn-dark {
      justify-content: center;
      padding: 0.3125rem; // 5px
    }
  }
}

.error-message {
  font-size: 16px;
  color: $error;
  margin-top: 4px;
}

.photo-error {
  font-size: 16px;
  color: $error;
  margin-top: 2px;
}

@media (max-width: 1200px) {
  .cooking {
    grid-template-columns: 1fr;
    gap: 1.25rem; // 20px
  }

  .ingredients {
    gap: 1.25rem; // 20px
    position: static;

    .items {
      gap: 1rem; // 16px
    }
  }
}

@media (max-width: 768px) {
  .cooking {
    gap: 1rem; // 16px
  }

  .ingredients {
    gap: 1rem; // 16px

    .items {
      gap: 0.75rem; // 12px
    }
  }
}

@media (max-width: 480px) {
  .cooking {
    gap: 0.75rem; // 12px
  }

  .ingredients {
    gap: 0.75rem; // 12px
  }
}
</style>
