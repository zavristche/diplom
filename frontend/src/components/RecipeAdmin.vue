<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { defineProps, defineEmits } from "vue";
import { useAuthStore } from "../stores/auth";
import { useRecipeStore } from "../stores/recipe";
import BaseIcon from "./BaseIcon.vue";
import CancelRecipe from "./Cancel.vue";
import Status from "./StatusContent.vue";
import RecipeAdminService from "../api/RecipeAdminService";

const authStore = useAuthStore();
const recipeStore = useRecipeStore();
const isCancelRecipeOpen = ref(false);

const emit = defineEmits(["updateList"]);

const handleCancelSuccess = (message) => {
  alert(message);
  emit("updateList");
};

const handleApplySuccess = (message) => {
  alert(message || "Рецепт успешно опубликован!");
  emit("updateList");
};

const recipeService = new RecipeAdminService();

const applyRecipe = async (recipeId) => {
  if (!authStore.isAuthenticated || !authStore.isAdmin) {
    alert("У вас нет прав для выполнения этого действия.");
    return;
  }

  try {
    const startTime = performance.now();
    console.log(
      `Starting apply request for recipe ID: ${recipeId} at ${startTime}ms`
    );

    const response = await recipeService.apply(recipeId);

    const endTime = performance.now();
    console.log(
      `Apply request completed in ${(endTime - startTime).toFixed(2)}ms`
    );

    if (response.data.success) {
      handleApplySuccess(response.data.message);
    } else {
      throw new Error(response.data.message || "Ошибка при публикации");
    }
  } catch (err) {
    console.error(
      "Ошибка при публикации рецепта:",
      err.response?.data || err.message
    );
    alert(err.response?.data?.message || "Произошла ошибка. Попробуйте снова.");
  }
};

defineProps({
  recipe: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <CancelRecipe
    :isOpen="isCancelRecipeOpen"
    :recipe_id="recipe?.id"
    @close="isCancelRecipeOpen = false"
    @success="handleCancelSuccess"
  />
  <section class="recipe-row" v-if="recipe && recipe.id">
    <router-link :to="`/recipe/${recipe.id}`" class="recipe-preview">
      <img :src="recipe.photo" alt="Recipe photo" width="100" height="100" />
    </router-link>
    <div class="recipe-details">
      <div class="recipe-header">
        <div class="recipe-text">
          <Status :status="recipe.status.title" />
          <h3>
            <router-link :to="`/recipe/${recipe.id}`">{{
              recipe.title
            }}</router-link>
          </h3>
          <router-link :to="`/profile/${recipe.user.id}`" class="recipe-author">
            {{ recipe.user.login }}
          </router-link>
          <div class="recipe-metadata">
            <span class="date">{{ recipe.created_at }}</span>
            <span class="marker">•</span>
            <span class="reaction">
              <BaseIcon
                viewBox="0 0 12 12"
                class="icon-light-12-1"
                name="bookmark"
              />
              <span class="reaction-count">{{ recipe.saved }}</span>
            </span>
            <span class="marker">•</span>
            <span class="reaction">
              <BaseIcon
                viewBox="0 0 13 13"
                class="icon-light-13-1"
                name="heart"
              />
              <span class="reaction-count">{{ recipe.likes }}</span>
            </span>
            <span class="marker">•</span>
            <span class="description">{{ recipe.description }}</span>
          </div>
        </div>
        <!-- Контейнер для кнопок действий -->
        <div class="btn-group end" v-if="recipe.status.title === 'Новое'">
          <button class="btn-dark line" @click="applyRecipe(recipe.id)">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-1" name="check" />
            Опубликовать
          </button>
          <button class="btn-dark line" @click="isCancelRecipeOpen = true">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-1" name="close" />
            Отклонить
          </button>
        </div>
        <div
          class="btn-group end"
          v-if="recipe.status.title === 'Опубликовано'"
        >
          <button class="btn-dark line" @click="isCancelRecipeOpen = true">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-55-1" name="close" />
            Отклонить
          </button>
        </div>
        <!-- Для "Отклонено" ничего не отображаем -->
      </div>
    </div>
  </section>
</template>

<style scoped lang="scss">
@use "../assets/styles/_variables.scss" as *;

.recipe-row {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 15px 25px;
  border-radius: $border;
  box-shadow: $shadow;
  width: 100%;

  .btn-dark {
    &.line {
      padding: 0 10px 0 0;
    }
  }

}

.recipe-preview {
  flex-shrink: 0;
  height: 100%;
  img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: $border;
  }
}

.recipe-details {
  flex: 1;
  display: flex;
  justify-content: space-between;
  flex-direction: column;
  gap: 10px;
}

.recipe-header {
  display: flex;
  align-items: center;
  gap: 15px;
  width: 100%;
  justify-content: space-between;
}

.author-avatar {
  img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
  }
}

.recipe-text {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 5px;
  h3 {
    font-size: 20px;
    font-weight: 500;
    color: $background-dark;
    margin: 0;
  }

  .recipe-author {
    font-size: 16px;
    color: $text-info;
    text-decoration: none;
  }

  .recipe-metadata {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    color: $text-info-light;

    .date {
      font-weight: 400;
    }

    .reaction {
      display: flex;
      align-items: center;
      gap: 3px;
    }

    .reaction-count {
      font-weight: 400;
    }

    .marker {
      font-size: 8px;
    }

    .description {
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 300px;
    }
  }
}

.btn-group {
  display: flex;
  gap: 10px;
  padding-top: 10px;

  .btn-item {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: $border;
    background-color: $light;
    border: 1px solid $text-info-light;
    transition: background-color 0.3s;

    &:hover {
      background-color: darken($light, 5%);
    }

    .icon-dark-55-1 {
      margin-right: 5px;
    }
  }
}

.reaction-container {
  flex: 1;
}
</style>
