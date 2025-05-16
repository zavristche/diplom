<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useProfileAuth } from "../../composables/useProfileAuth";
import { useAuthStore } from "../../stores/auth";
import { useProfileStore } from "../../stores/profile";
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
const profileStore = useProfileStore();
const data = route.meta.data?.data;

const title = ref("");
const description = ref("");
const time = ref("");
const selectedComplexity = ref("");
const selectedPrivate = ref("");
const portions = ref(1);
const ingredients = ref([{ product_id: null, count: null, measure_id: null }]);
const steps = ref([{ description: "", previewUrl: null, file: null }]);
const previewUrl = ref(null);
const recipePhotoFile = ref(null);
const selectedMarks = ref([]);
const errors = ref({});

const textareaRef = ref(null);
const stepTextareaRefs = ref([]);

const tasteMeasureId = ref(null);

const initializeForm = () => {
  if (!isAuthenticated.value) {
    errors.value = { general: "Пожалуйста, войдите в систему." };
    console.error("User not authenticated");
    router.push({ name: "login" });
  }
};

const addIngredient = () => {
  ingredients.value.push({ product_id: null, count: null, measure_id: null });
  console.log("Added new ingredient:", ingredients.value);
};

const removeIngredient = (index) => {
  ingredients.value = ingredients.value.filter((_, i) => i !== index);
  console.log("Removed ingredient at index:", index);
};

const addStep = () => {
  steps.value.push({ description: "", previewUrl: null, file: null });
  console.log("Added new step:", steps.value);
};

const removeStep = (index) => {
  if (steps.value.length <= 1) return;
  steps.value.splice(index, 1);
  console.log("Removed step at index:", index);
};

const onFileSelected = (event) => {
  const file = event.target.files[0];
  if (file) {
    recipePhotoFile.value = file;
    previewUrl.value = URL.createObjectURL(file);
    console.log("Selected recipe photo:", file.name);
  }
};

const onStepFileSelected = (event, index) => {
  const file = event.target.files[0];
  if (file) {
    steps.value[index].file = file;
    steps.value[index].previewUrl = URL.createObjectURL(file);
    console.log(`Selected step photo for step ${index}:`, file.name);
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
  console.log("Form validation errors:", errors.value);
  return !Object.keys(errors.value).length;
};

const submitForm = async (event) => {
  event.preventDefault();
  if (!isAuthenticated.value) {
    errors.value = { general: "Пожалуйста, войдите в систему." };
    console.error("User not authenticated");
    router.push({ name: "login" });
    return;
  }
  if (!validateForm()) {
    console.warn("Form validation failed");
    return;
  }

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
    formData.append(`steps[${index}][title]`, `Шаг ${index + 1}`);
    formData.append(`steps[${index}][description]`, step.description || "");
    if (step.file) formData.append(`step_photos[${index}]`, step.file);
  });

  selectedMarks.value.forEach((mark, index) => {
    formData.append(`marks[${index}]`, mark.id);
  });

  try {
    console.log("Sending recipe creation request");
    const response = await RecipeService.create(formData);
    if (response.data.success) {
      const recipeId = response.data.id || response.data.recipe?.id;
      if (!recipeId) throw new Error("Recipe ID not returned by API");
      console.log("Recipe created successfully, ID:", recipeId);
      if (authStore.user?.id) {
        await profileStore.updateProfile(authStore.user.id);
        console.log("Profile updated after recipe creation");
      } else {
        console.warn("No user ID found for profile update");
      }
      router.push({ name: "RecipeView", params: { id: recipeId } });
    } else {
      throw new Error(response.data.message || "Ошибка API");
    }
  } catch (error) {
    console.error(
      "Error creating recipe:",
      error.response?.data || error.message
    );
    if (error.response?.status === 401) {
      errors.value = { general: "Не авторизован. Войдите снова." };
      authStore.clearUser();
      router.push({ name: "login" });
    } else if (error.response?.status === 422 && error.response?.data?.errors) {
      errors.value = normalizeServerErrors(error.response.data.errors);
      console.log("Normalized server errors:", errors.value);
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
    <h1>Создать рецепт</h1>
    <div v-if="errors.general" class="error-message general-error">
      {{ errors.general }}
    </div>
    <div class="btn-group start">
      <div v-if="previewUrl" class="preview">
        <img :src="previewUrl" alt="Превью фото" />
      </div>
      <div class="photo-upload-wrapper">
        <label class="btn-dark line">
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
        />
        <Select
          v-model="selectedComplexity"
          label="Сложность"
          name="complexity_id"
          placeholder="Выберите слож	complexity_id"
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
        Метки
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
        <Portions v-model:portions="portions" :errors="errors" />
        <div class="items">
          <button class="btn-dark" @click.prevent="addIngredient">
            <BaseIcon viewBox="0 0 65 65" class="icon-white-45-3" name="plus" />
            Добавить продукт
          </button>
          <Ingredient
            v-for="(ingredient, index) in ingredients"
            :key="`ingredient-${index}`"
            :ingredient="ingredient"
            :index="index"
            :data="data"
            :errors="errors"
            @remove="removeIngredient"
          />
          <div v-if="errors.products" class="error-message">
            {{ errors.products }}
          </div>
        </div>
      </div>
      <label for="steps" class="steps">
        <h2>Шаги приготовления</h2>
        <button class="btn-dark" @click.prevent="addStep">
          <BaseIcon viewBox="0 0 65 65" class="icon-white-45-3" name="plus" />
          Добавить шаг
        </button>
        <section
          v-for="(step, index) in steps"
          :key="`step-${index}`"
          class="step"
        >
          <div class="step-actions">
            <button
              v-if="steps.length > 1"
              class="btn-danger btn-small btn-icon"
              @click.prevent="removeStep(index)"
            >
              <BaseIcon
                viewBox="0 0 65 65"
                class="icon-white-55-2"
                name="close"
              />
            </button>
          </div>
          <div class="photo-upload-wrapper">
            <label class="btn-dark line full-width">
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
      <input class="btn-dark" type="submit" value="Создать рецепт" />
    </div>
  </form>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;

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
  gap: 30px;
  width: 100%;
  height: auto;
  position: sticky;
  top: 6.25rem;

  .items {
    display: flex;
    flex-direction: column;
    gap: 20px;
    width: 100%;

    .btn-dark {
      justify-content: center;
      padding: 5px;
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
    gap: 20px;
  }

  .ingredients {
    position: static;
  }
}

@media (max-width: 768px) {
  .create-form {
    gap: 30px;
  }

  .input-title {
    font-size: 24px;
  }

  .input-description {
    font-size: 18px;
  }

  .label-group {
    flex-direction: column;
    gap: 15px;
  }

  .preview {
    height: 300px;
  }
}

@media (max-width: 480px) {
  .create-form {
    gap: 20px;
  }

  .input-title {
    font-size: 20px;
  }

  .input-description {
    font-size: 16px;
  }

  .preview {
    height: 200px;
  }
}
</style>
