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
const steps = ref([{ description: "", preview: null, file: null }]);
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
      errors.value[`products[${index}]`] = productErrors;
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
          placeholder="Выберите сложность"
          :options="data.complexities"
          :is-invalid="!!errors.complexity_id"
          :error-message="errors.complexity_id"
        />
        <Select
          v-model="selectedPrivate"
          label="Кому доступен рецепт?"
          name="private_id"
          placeholder="Выберите доступ"
          :options="data.privates"
          :is-invalid="!!errors.private_id"
          :error-message="errors.private_id"
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

<style lang="scss" scoped>
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/form" as *;

.create-form {
  display: flex;
  flex-direction: column;
  gap: 3.125rem; // 50px
  width: 100%;
  max-width: 75rem; // 1200px
  margin: 0 auto;
  padding: 0 1.5rem;
  align-items: flex-start;
  box-sizing: border-box;

  h1 {
    font-size: 2.25rem; // 36px
    font-weight: 600;
    margin: 0;
    line-height: 1.2;
  }

  .photo-upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.25rem; // 4px
    width: 100%;
  }

  .step-actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 0.5rem; // 8px
  }

  .btn-dark {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem; // 8px
    padding: 0.75rem 1.25rem; // 12px 20px
    font-size: 1rem; // 16px
    font-weight: 500;
    border-radius: $border;
    min-height: 2.75rem; // 44px
    box-sizing: border-box;
    &.full-width {
      width: 100%;
    }
  }

  .btn-danger.btn-small.btn-icon {
    padding: 0.5rem; // 8px
    width: 2.25rem; // 36px
    height: 2.25rem; // 36px
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: $border;
  }
}

.marks {
  display: flex;
  flex-direction: column;
  gap: 0.75rem; // 12px
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
  position: sticky;
  top: 6.25rem; // 100px

  h2 {
    font-size: 1.75rem; // 28px
    font-weight: 600;
    margin: 0;
  }

  .items {
    display: flex;
    flex-direction: column;
    gap: 1.25rem; // 20px
    width: 100%;

    .btn-dark {
      padding: 0.625rem 1rem; // 10px 16px
      font-size: 0.9375rem; // 15px
      max-width: 18.75rem; // 300px
      width: 100%;
    }
  }
}

.steps {
  display: flex;
  flex-direction: column;
  gap: 1.875rem; // 30px
  width: 100%;

  h2 {
    font-size: 1.75rem; // 28px
    font-weight: 600;
    margin: 0;
  }

  .step {
    display: flex;
    flex-direction: column;
    gap: 0.75rem; // 12px
  }
}

.cooking {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.875rem; // 30px
  width: 100%;
  align-items: start;
}

.photo-error {
  font-size: 1rem; // 16px
  color: $error;
  margin-top: 0.25rem; // 4px
}

@media (max-width: 1200px) {
  .create-form {
    gap: 2.5rem; // 40px
    max-width: 62.5rem; // 1000px
    padding: 0 1.5rem;

    h1 {
      font-size: 2rem; // 32px
    }
  }

  .cooking {
    grid-template-columns: 1fr;
    gap: 1.25rem; // 20px
  }

  .ingredients {
    gap: 1.25rem; // 20px
    position: static;

    h2 {
      font-size: 1.5rem; // 24px
    }

    .items {
      gap: 1rem; // 16px

      .btn-dark {
        max-width: 15.625rem; // 250px
      }
    }
  }

  .steps {
    gap: 1.25rem; // 20px

    h2 {
      font-size: 1.5rem; // 24px
    }
  }

  .btn-dark {
    padding: 0.625rem 1rem; // 10px 16px
    font-size: 0.9375rem; // 15px
    min-height: 2.5rem; // 40px
  }

  .btn-danger.btn-small.btn-icon {
    width: 2rem; // 32px
    height: 2rem; // 32px
    padding: 0.375rem; // 6px
  }
}

@media (max-width: 768px) {
  .create-form {
    gap: 1.875rem; // 30px
    padding: 0 1rem;

    h1 {
      font-size: 1.75rem; // 28px
    }
  }

  .cooking {
    gap: 1rem; // 16px
  }

  .ingredients {
    gap: 1rem; // 16px

    h2 {
      font-size: 1.375rem; // 22px
    }

    .items {
      gap: 0.75rem; // 12px

      .btn-dark {
        padding: 0.5rem 0.75rem; // 8px 12px
        font-size: 0.875rem; // 14px
        max-width: 12.5rem; // 200px
      }
    }
  }

  .steps {
    gap: 1rem; // 16px

    h2 {
      font-size: 1.375rem; // 22px
    }

    .step {
      gap: 0.625rem; // 10px
    }

    .btn-dark {
      padding: 0.5rem 0.75rem; // 8px 12px
      font-size: 0.875rem; // 14px
    }
  }

  .btn-dark {
    padding: 0.5rem 0.75rem; // 8px 12px
    font-size: 0.875rem; // 14px
    min-height: 2.25rem; // 36px
  }

  .btn-danger.btn-small.btn-icon {
    width: 1.75rem; // 28px
    height: 1.75rem; // 28px
    padding: 0.25rem; // 4px
  }

  .photo-error {
    font-size: 0.875rem; // 14px
  }
}

@media (max-width: 480px) {
  .create-form {
    gap: 1.25rem; // 20px
    padding: 0 0.5rem;

    h1 {
      font-size: 1.5rem; // 24px
    }
  }

  .cooking {
    gap: 0.75rem; // 12px
  }

  .ingredients {
    gap: 0.75rem; // 12px

    h2 {
      font-size: 1.25rem; // 20px
    }

    .items {
      gap: 0.5rem; // 8px

      .btn-dark {
        padding: 0.375rem 0.625rem; // 6px 10px
        font-size: 0.8125rem; // 13px
        max-width: 11.25rem; // 180px
      }
    }
  }

  .steps {
    gap: 0.75rem; // 12px

    h2 {
      font-size: 1.25rem; // 20px
    }

    .step {
      gap: 0.5rem; // 8px
    }

    .btn-dark {
      padding: 0.375rem 0.625rem; // 6px 10px
      font-size: 0.8125rem; // 13px
    }
  }

  .btn-dark {
    padding: 0.375rem 0.625rem; // 6px 10px
    font-size: 0.8125rem; // 13px
    min-height: 2rem; // 32px
  }

  .btn-danger.btn-small.btn-icon {
    width: 1.5rem; // 24px
    height: 1.5rem; // 24px
    padding: 0.1875rem; // 3px
  }

  .photo-error {
    font-size: 0.75rem; // 12px
  }
}
</style>