<script setup>
import { ref, watch, defineProps, defineEmits } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import BaseIcon from "./BaseIcon.vue";
import Input from "./Input.vue";
import RecipeAdminService from "../api/RecipeAdminService";

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
const emit = defineEmits(["close", "success"]);

const router = useRouter();
const authStore = useAuthStore();

const reason = ref("");
const errors = ref({ reason: "" });
const isLoading = ref(false);
const isSubmitting = ref(false);

const recipeService = new RecipeAdminService();

// Проверяем авторизацию при открытии модалки
watch(
  () => props.isOpen,
  (newValue) => {
    if (!newValue) {
      return; // Ничего не делаем при закрытии
    }

    if (!authStore.isAuthenticated) {
      errors.value.reason = "Пожалуйста, войдите в систему.";
      router.push({ name: "login" }).catch(() => {
        console.error("Маршрут /login не найден. Проверьте router.js");
        errors.value.reason = "Ошибка: страница входа недоступна";
      });
      return;
    }

    if (!authStore.isAdmin) {
      errors.value.reason = "У вас нет прав для выполнения этого действия.";
      emit("close");
      return;
    }

    if (!props.recipe_id) {
      errors.value.reason = "ID рецепта не указан.";
      emit("close");
      return;
    }
  }
);

// Отправка запроса на отклонение рецепта
const cancelRecipe = async () => {
  if (!authStore.isAuthenticated) {
    errors.value.reason = "Пожалуйста, войдите в систему.";
    router.push({ name: "login" }).catch(() => {
      console.error("Маршрут /login не найден. Проверьте router.js");
      errors.value.reason = "Ошибка: страница входа недоступна";
    });
    return;
  }

  if (!reason.value.trim()) {
    errors.value.reason = "Укажите причину отклонения.";
    return;
  }

  try {
    isSubmitting.value = true;
    errors.value.reason = "";
    console.log(`Preparing to cancel recipe with ID: ${props.recipe_id}`);

    const response = await recipeService.cancel(props.recipe_id, {
      admin_comment: reason.value, // Отправляем как admin_comment
    });

    if (response.data.success) {
      emit("success", response.data.message || "Рецепт успешно отклонён.");
      emit("close");
    } else {
      throw new Error(response.data.message || "Ошибка API");
    }
  } catch (err) {
    console.error(
      "Ошибка при отклонении рецепта:",
      err.response?.data || err.message
    );
    if (err.response?.status === 401) {
      errors.value.reason = "Не авторизован. Пожалуйста, войдите снова.";
      authStore.clearUser();
      router.push({ name: "login" }).catch(() => {
        console.error("Маршрут /login не найден. Проверьте router.js");
        errors.value.reason = "Ошибка: страница входа недоступна";
      });
    } else if (
      err.response?.status === 422 &&
      err.response?.data?.errors?.admin_comment
    ) {
      errors.value.reason = Array.isArray(err.response.data.errors.admin_comment)
        ? err.response.data.errors.admin_comment[0]
        : err.response.data.errors.admin_comment;
    } else if (err.response?.status === 404) {
      errors.value.reason = "Рецепт не найден.";
    } else {
      errors.value.reason =
        err.response?.data?.message || "Произошла ошибка. Попробуйте снова.";
    }
  } finally {
    isSubmitting.value = false;
  }
};

const closeModal = () => {
  reason.value = "";
  errors.value.reason = "";
  emit("close");
};
</script>

<template>
  <div v-if="props.isOpen" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <div class="modal-header space">
        <h2 class="modal-title">Отклонить публикацию</h2>
        <button class="btn-icon" @click="closeModal">
          <BaseIcon viewBox="0 0 65 65" class="icon-dark-70-2" name="close" />
        </button>
      </div>
      <div class="modal-content">
        <div v-if="isLoading" class="loading">Загрузка...</div>
        <div v-else-if="isSubmitting" class="submitting">Отправка...</div>
        <div v-else class="form-container">
          <Input
            label="Причина отклонения"
            name="admin-comment"
            type="textarea"
            placeholder="Укажите причину отклонения..."
            :modelValue="reason"
            :isInvalid="!!errors.reason"
            :errorMessage="errors.reason"
            @update:modelValue="reason = $event"
          />
        </div>
      </div>
      <form @submit.prevent="cancelRecipe">
        <input type="hidden" name="recipe_id" :value="props.recipe_id" />
        <div class="btn-group end">
          <button
            class="btn-danger"
            type="submit"
            :disabled="isLoading || isSubmitting"
          >
            Отклонить
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;
@use "../assets/styles/modal" as *;

</style>