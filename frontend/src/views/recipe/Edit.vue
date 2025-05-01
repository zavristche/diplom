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
const data = route.meta.data?.data;

// ID рецепта из параметров маршрута
const recipeId = route.params.id;

// Проверка авторизации и данных
console.log("isAuthenticated:", isAuthenticated.value);
console.log("currentUser:", currentUser.value);
console.log("authStore.authKey:", authStore.authKey);
console.log("localStorage.auth_key:", localStorage.getItem("auth_key"));
console.log("localStorage.user:", localStorage.getItem("user"));
console.log("recipeId:", recipeId);
console.log("meta data:", data);

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
const isLoading = ref(true);
const isEditable = ref(true);

// Ошибки валидации
const errors = ref({});

// Refs для textarea
const textareaRef = ref(null);
const stepTextareaRefs = ref([]);

// Загрузка данных рецепта
const loadRecipe = async () => {
    if (!recipeId) {
        console.error("Recipe ID is missing");
        errors.value = { general: "ID рецепта не указан." };
        isLoading.value = false;
        return;
    }

    try {
        isLoading.value = true;
        console.log(`Fetching recipe with ID: ${recipeId}`);
        const response = await RecipeService.getById(recipeId);
        console.log("Full response:", response);
        console.log("Response data:", response.data);

        if (!response || !response.data) {
            throw new Error("Ответ сервера пустой");
        }

        if (!response.data.id) {
            errors.value = {
                general: "Данные рецепта отсутствуют.",
            };
            console.log("No recipe data found in response.");
            return;
        }

        const recipeUserId = response.data.user_id;
        const currentUserId = authStore.userId;
        console.log("Recipe user_id:", recipeUserId, "Current user_id:", currentUserId);

        if (currentUserId && recipeUserId !== currentUserId) {
            isEditable.value = false;
            errors.value = {
                general: "Вы не можете редактировать этот рецепт, так как не являетесь его автором.",
            };
        } else {
            isEditable.value = true;
        }

        fillForm(response.data);
    } catch (error) {
        console.error("Ошибка при загрузке рецепта:", error);
        console.error("Полный объект ошибки:", error.response?.data || error.message);
        errors.value = {
            general: error.message || "Не удалось загрузить рецепт. Попробуйте снова.",
        };

        if (error.response?.status === 404) {
            console.log("Recipe not found (404), redirecting to home...");
            router.push({ name: "home" });
        } else if (error.response?.status === 401) {
            console.log("Unauthorized (401), redirecting to login...");
            authStore.clearUser();
            router.push({ name: "login" });
        }
    } finally {
        isLoading.value = false;
        console.log("Loading finished, isLoading:", isLoading.value);
    }
};

// Функция для заполнения формы
const fillForm = (recipe) => {
    if (!recipe) {
        console.error("Recipe data is undefined or null");
        errors.value = { general: "Данные рецепта недоступны." };
        return;
    }

    console.log("Filling form with recipe data:", recipe);

    title.value = recipe.title ?? "";
    description.value = recipe.description ?? "";
    time.value = recipe.time ?? "";
    selectedComplexity.value = recipe.complexity_id ?? "";
    selectedPrivate.value = recipe.private_id ?? "";
    portions.value = recipe.portions ?? 1;

    ingredients.value = recipe.products?.map((product) => ({
        id: product.id,
        product_id: product.product_id,
        count: product.count,
        measure_id: product.measure_id,
    })) ?? [];

    steps.value = recipe.steps?.map((step) => ({
        id: step.id,
        description: step.description ?? "",
        previewUrl: step.photo ? `${step.photo}` : null,
        file: null,
    })) ?? [{ description: "", previewUrl: null, file: null }];

    previewUrl.value = recipe.photo ? `${recipe.photo}` : null;

    selectedMarks.value = recipe.marks ?? [];

    console.log("Form fields after assignment:", {
        title: title.value,
        description: description.value,
        time: time.value,
        selectedComplexity: selectedComplexity.value,
        selectedPrivate: selectedPrivate.value,
        portions: portions.value,
        ingredients: ingredients.value,
        steps: steps.value,
        previewUrl: previewUrl.value,
        selectedMarks: selectedMarks.value,
    });
};

// Добавление ингредиента
const addIngredient = () => {
    if (!isEditable.value) return;
    ingredients.value.push({ product_id: "", count: "", measure_id: "" });
};

// Удаление ингредиента
const removeIngredient = (index) => {
    if (!isEditable.value) return;
    ingredients.value.splice(index, 1);
};

// Обработка порций
const updatePortions = (delta) => {
    if (!isEditable.value) return;
    portions.value = Math.max(1, portions.value + delta);
};

// Добавление шага
const addStep = () => {
    if (!isEditable.value) return;
    steps.value.push({ description: "", previewUrl: null, file: null });
};

// Удаление шага
const removeStep = (index) => {
    if (!isEditable.value) return;
    if (steps.value.length > 1) {
        steps.value.splice(index, 1);
    }
};

// Обработка основного фото
const onFileSelected = (event) => {
    if (!isEditable.value) return;
    const file = event.target.files[0];
    if (file) {
        recipePhotoFile.value = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

// Обработка фото шага
const onStepFileSelected = (event, index) => {
    if (!isEditable.value) return;
    const file = event.target.files[0];
    if (file) {
        steps.value[index].file = file;
        steps.value[index].previewUrl = URL.createObjectURL(file);
    }
};

// Фильтрация меток
const filteredMarks = computed(() => {
    const marks = Object.values(data?.marks || {});
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
    if (!isEditable.value) return;
    activeMarkType.value = typeId;
    searchMark.value = "";
    isMarkInputFocused.value = true;
};

const addMark = (mark) => {
    if (!isEditable.value) return;
    if (!selectedMarks.value.some((m) => m.id === mark.id)) {
        selectedMarks.value.push(mark);
    }
    searchMark.value = "";
    isMarkInputFocused.value = false;
};

const removeMark = (markId) => {
    if (!isEditable.value) return;
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
        stepTextareaRefs.value.forEach((textarea) =>
            adjustTextareaHeight(textarea)
        );
    },
    { deep: true }
);

onMounted(() => {
    console.log("Component mounted, loading recipe...");
    loadRecipe();
    adjustTextareaHeight(textareaRef.value);
    stepTextareaRefs.value.forEach((textarea) => adjustTextareaHeight(textarea));
});

// Нормализация серверных ошибок
const normalizeServerErrors = (serverErrors) => {
    console.log("Raw server errors:", serverErrors);
    const normalized = {};

    for (const [key, value] of Object.entries(serverErrors)) {
        if (key === "steps" || key === "products" || key === "marks") {
            normalized[key] = Array.isArray(value) ? value.join(", ") : value;
        } else if (key.startsWith("step_")) {
            const index = parseInt(key.replace("step_", ""), 10);
            if (typeof value === "string") {
                normalized[`steps[${index}][description]`] = value;
            } else if (value.imageFiles) {
                normalized[`step_photos_${index}`] = Array.isArray(value.imageFiles)
                    ? value.imageFiles.join(", ")
                    : value.imageFiles;
            } else {
                for (const [attr, messages] of Object.entries(value)) {
                    normalized[`steps[${index}][${attr}]`] = Array.isArray(messages)
                        ? messages.join(", ")
                        : messages;
                }
            }
        } else if (key.startsWith("product_")) {
            const index = parseInt(key.replace("product_", ""), 10);
            if (typeof value === "string") {
                normalized[`products[${index}][product_id]`] = value;
            } else {
                for (const [attr, messages] of Object.entries(value)) {
                    normalized[`products[${index}][${attr}]`] = Array.isArray(messages)
                        ? messages.join(", ")
                        : messages;
                }
            }
        } else if (key === "imageFile") {
            normalized["recipe_photo"] = Array.isArray(value) ? value.join(", ") : value;
        } else {
            normalized[key] = Array.isArray(value) ? value.join(", ") : value;
        }
    }

    console.log("Normalized server errors:", normalized);
    return normalized;
};

// Валидация формы
const validateForm = () => {
    errors.value = {};
    return true;
};

// Отправка формы
const submitForm = async (event) => {
    event.preventDefault();

    if (!isEditable.value) {
        errors.value = { general: "Редактирование запрещено: вы не автор этого рецепта." };
        return;
    }

    if (!isAuthenticated.value) {
        errors.value = {
            general: "Пожалуйста, войдите в систему для редактирования рецепта",
        };
        console.error("Пользователь не авторизован");
        router.push({ name: "login" }).catch(() => {
            console.error("Маршрут /login не найден. Проверьте router.js");
            errors.value = { general: "Ошибка: страница входа недоступна" };
        });
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

    // Отправляем все продукты, даже "невалидные"
    ingredients.value.forEach((ingredient, index) => {
        if (ingredient.id) {
            formData.append(`products[${index}][id]`, ingredient.id);
        }
        formData.append(`products[${index}][product_id]`, ingredient.product_id || "");
        formData.append(`products[${index}][count]`, ingredient.count || "");
        formData.append(`products[${index}][measure_id]`, ingredient.measure_id || "");
    });

    steps.value.forEach((step, index) => {
        if (step.id) {
            formData.append(`steps[${index}][id]`, step.id);
        }
        formData.append(`steps[${index}][title]`, `Шаг ${index + 1}`);
        formData.append(`steps[${index}][description]`, step.description);
        if (step.file) {
            formData.append(`step_photos[]`, step.file);
        }
    });

    if (selectedMarks.value.length > 0) {
        selectedMarks.value.forEach((mark, index) => {
            formData.append(`marks[${index}]`, mark.id);
        });
    }

    try {
        console.log("Отправка запроса с authKey:", authStore.authKey);
        console.log("Sending FormData:");
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value instanceof File ? value.name : value}`);
        }

        const response = await RecipeService.update(recipeId, formData);
        console.log("Server response:", response.data);

        if (response.data.success) {
            router.push(`/recipe/${recipeId}`);
        } else {
            throw new Error(response.data.message || "Ошибка API");
        }
    } catch (error) {
        console.error(
            "Ошибка при обновлении рецепта:",
            error.response?.data || error.message
        );
        if (error.response?.status === 401) {
            errors.value = { general: "Не авторизован. Пожалуйста, войдите снова." };
            authStore.clearUser();
            router.push({ name: "login" }).catch(() => {
                console.error("Маршрут /login не найден. Проверьте router.js");
                errors.value = { general: "Ошибка: страница входа недоступна" };
            });
        } else if (error.response?.status === 422 && error.response?.data?.errors) {
            errors.value = normalizeServerErrors(error.response.data.errors);
            console.log("Normalized server errors:", errors.value);
        } else {
            errors.value = {
                general:
                    error.response?.data?.message ||
                    error.message ||
                    "Произошла ошибка. Попробуйте снова.",
            };
        }
    }
};
</script>

<template>
    <div v-if="isLoading" class="loading">Загрузка рецепта...</div>
    <form v-else @submit="submitForm" class="create-form">
        <h1>Редактировать рецепт</h1>
        <div v-if="errors.general" class="error-message general-error">
            {{ errors.general }}
        </div>
        <div class="btn-group start">
            <div v-if="previewUrl" class="preview">
                <img :src="previewUrl" alt="Превью загружаемого фото" />
            </div>
            <label v-if="isEditable" class="btn-dark line" style="cursor: pointer">
                <input
                    type="file"
                    accept="image/*"
                    style="display: none"
                    @change="onFileSelected"
                    name="recipe_photo"
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
                    :disabled="!isEditable"
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
                    :error-message="errors.time ? ` ${errors.time}` : ''"
                    :disabled="!isEditable"
                />
                <Select
                    v-model="selectedComplexity"
                    label="Сложность"
                    name="complexity_id"
                    placeholder="Выберите сложность"
                    :options="data.complexities"
                    :is-invalid="!!errors.complexity_id"
                    :error-message="errors.complexity_id ? ` ${errors.complexity_id}` : ''"
                    :disabled="!isEditable"
                />
                <Select
                    v-model="selectedPrivate"
                    label="Кому доступен рецепт?"
                    name="private_id"
                    placeholder="Выберите доступ"
                    :options="data.privates"
                    :is-invalid="!!errors.private_id"
                    :error-message="errors.private_id ? ` ${errors.private_id}` : ''"
                    :disabled="!isEditable"
                />
            </div>
            <label for="marks" class="marks">
                Метки
                <div class="btn-group start">
                    <button
                        class="mark_type"
                        :class="{ active: activeMarkType === null }"
                        @click.prevent="selectMarkType(null)"
                        :disabled="!isEditable"
                    >
                        Все
                    </button>
                    <button
                        v-for="(type, id) in data.mark_types"
                        :key="id"
                        class="mark_type"
                        :class="{ active: activeMarkType === id }"
                        @click.prevent="selectMarkType(id)"
                        :disabled="!isEditable"
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
                                <button
                                    v-if="isEditable"
                                    class="mark-item__close"
                                    @click="removeMark(mark.id)"
                                >
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
                                :disabled="!isEditable"
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
                <div v-if="errors.marks" class="error-message">
                    {{ errors.marks }}
                </div>
            </label>
        </div>
        <div class="cooking">
            <div class="ingredients">
                <h2>Ингредиенты</h2>
                <div class="portions-container">
                    <span>Порции</span>
                    <div class="portions">
                        <button
                            type="button"
                            @click="updatePortions(-1)"
                            :disabled="!isEditable"
                        >
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
                            :disabled="!isEditable"
                        />
                        <button
                            type="button"
                            @click="updatePortions(1)"
                            :disabled="!isEditable"
                        >
                            <BaseIcon
                                viewBox="0 0 65 65"
                                class="icon-dark-45-2"
                                name="pluse"
                            />
                        </button>
                    </div>
                </div>
                <div class="items">
                    <button
                        v-if="isEditable"
                        class="btn-dark"
                        @click.prevent="addIngredient"
                    >
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
                        <button
                            v-if="isEditable"
                            type="button"
                            @click="removeIngredient(index)"
                        >
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
                                    :disabled="!isEditable"
                                />
                                <Input
                                    v-model="ingredient.count"
                                    :name="'products[' + index + '][count]'"
                                    type="number"
                                    placeholder="N"
                                    :is-invalid="!!errors[`products[${index}][count]`]"
                                    :disabled="!isEditable"
                                />
                                <Select
                                    v-model="ingredient.measure_id"
                                    :name="'products[' + index + '][measure_id]'"
                                    placeholder="Ед."
                                    :options="data.measures"
                                    :is-invalid="!!errors[`products[${index}][measure_id]`]"
                                    :disabled="!isEditable"
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
                    <div v-if="errors.products" class="error-message">
                        {{ errors.products }}
                    </div>
                </div>
            </div>
            <label for="steps" class="steps">
                <h2>Шаги приготовления</h2>
                <button v-if="isEditable" class="btn-dark" @click.prevent="addStep">
                    <BaseIcon viewBox="0 0 65 65" class="icon-white-45-3" name="pluse" />
                    Добавить шаг
                </button>
                <section v-for="(step, index) in steps" :key="index" class="step">
                    <div class="preview" v-if="step.previewUrl">
                        <img :src="step.previewUrl" alt="Превью шага" />
                    </div>
                    <div class="btn-group start">
                        <button
                            v-if="isEditable"
                            class="btn-light"
                            @click.prevent="removeStep(index)"
                        >
                            <BaseIcon
                                viewBox="0 0 29 29"
                                class="icon-white-30-2"
                                name="close"
                            />
                        </button>
                        <label
                            v-if="isEditable"
                            class="btn-dark line"
                            style="cursor: pointer"
                        >
                            <input
                                type="file"
                                accept="image/*"
                                style="display: none"
                                @change="onStepFileSelected($event, index)"
                                name="step_photos[]"
                            />
                            <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
                            Загрузить фото
                        </label>
                    </div>
                    <div v-if="errors[`step_photos_${index}`]" class="error-message">
                        {{ errors[`step_photos_${index}`] }}
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
                <div v-if="errors.steps" class="error-message">
                    {{ errors.steps }}
                </div>
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

<style lang="scss">
@use "../../assets/styles/variables" as *;

.loading {
  text-align: center;
  font-size: 20px;
  padding: 20px;
}

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
