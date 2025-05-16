<script setup>
import { ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useRecipeStore } from "../../stores/recipe";
import { useAuthStore } from "../../stores/auth";

import BaseIcon from "../../components/BaseIcon.vue";
import SaveRecipe from "../../components/SaveRecipe.vue";
import Comments from "../../components/Comments.vue";
import ReactionButton from "../../components/ReactionButton.vue";
import DeleteButton from "../../components/DeleteButton.vue";
import Mark from "../../components/Mark.vue";
import Step from "../../components/Step.vue";
import Portions from "../../components/Portions.vue";

const route = useRoute();
const recipeStore = useRecipeStore();
const recipe = computed(() => recipeStore.currentRecipe);
const authStore = useAuthStore();

recipeStore.fetchRecipeById(route.params.id);

const portions = ref(null);
const baseIngredients = ref([]);
const isSaveRecipeOpen = ref(false);

const isAuthor = computed(() => {
  return (
    authStore.isAuthenticated &&
    authStore.userId &&
    recipe.value &&
    authStore.userId === recipe.value.user_id
  );
});

watch(
  recipe,
  (newRecipe) => {
    if (newRecipe) {
      portions.value = newRecipe.portions || 1; // Устанавливаем значение по умолчанию
      baseIngredients.value = newRecipe.products.map((product) => ({
        ...product,
        count: product.count,
      }));
      document.title = newRecipe.title || "Рецепт";
    }
  },
  { immediate: true }
);

watch(
  [() => authStore.userId, recipe],
  () => {
    console.log("authStore.userId:", authStore.userId);
    console.log("recipe:", recipe.value);
    console.log("recipe.user_id:", recipe.value?.user_id);
    console.log("recipe.likes:", recipe.value?.likes);
    console.log("isAuthor:", isAuthor.value);
    console.log("Should render buttons:", isAuthor.value && !!recipe.value);
  },
  { immediate: true }
);

const adjustedIngredients = computed(() => {
  if (!recipe.value || portions.value === null) return [];
  return baseIngredients.value.map((product) => ({
    ...product,
    count:
      product.count === null
        ? null
        : (product.count * portions.value) / recipe.value.portions,
  }));
});

const formatCount = (count) => {
  if (count === null) return "";
  const rounded = Number(count.toFixed(1));
  return rounded % 1 === 0 ? rounded.toString() : rounded.toFixed(1);
};
</script>

<template>
  <SaveRecipe
    :isOpen="isSaveRecipeOpen"
    :recipe_id="recipe?.id"
    @close="isSaveRecipeOpen = false"
  />
  <div v-if="recipe" class="preview">
    <img :src="recipe.photo" alt="" />
  </div>
  <div v-if="recipe" class="content-info">
    <span class="time">{{ recipe.created_at }}</span>
    <h1>{{ recipe.title }}</h1>
    <router-link :to="`/profile/${recipe.user.id}`" class="author">
      <img :src="recipe.user.avatar" alt="" />
      {{ recipe.user.login }}
    </router-link>
    <div class="cards-info">
      <div class="card-info">
        <span class="card-info__title">Время приготовления</span>
        <div class="card-info__var">
          <BaseIcon viewBox="0 0 29 29" class="icon-orange-30-2" name="time" />
          {{ recipe.time }}
        </div>
      </div>
      <div class="card-info">
        <span class="card-info__title">Сложность</span>
        <div class="card-info__var">
          <BaseIcon
            viewBox="0 0 29 25"
            class="icon-orange-30-2"
            name="complexity"
          />
          {{ recipe.complexity.value }}/5
        </div>
      </div>
    </div>
    <div class="description">
      {{ recipe.description }}
    </div>
    <div class="btn-group end">
      <div v-if="isAuthor" class="btn-group">
        <router-link :to="`/recipe/edit/${recipe.id}`" class="btn-dark">
          Редактировать
        </router-link>
        <DeleteButton :entity-id="recipe.id" entity-type="recipe" />
      </div>
      <button type="submit" class="btn-dark" @click="isSaveRecipeOpen = true">
        <BaseIcon
          viewBox="0 0 25 26"
          class="icon-white-30-2"
          name="book"
        />Сохранить
      </button>
      <ReactionButton
        :entity-type="'recipe'"
        :entity-id="recipe.id"
        :count="recipe.likes"
      />
    </div>
  </div>
  <div v-if="recipe" class="cooking">
    <div class="ingredients">
      <h2>Ингредиенты</h2>
      <Portions v-model:portions="portions" :errors="{}" :is-editable="true" />
      <div class="items">
        <label
          v-for="(product, index) in adjustedIngredients"
          :key="index"
          class="ingredient"
        >
          <input type="checkbox" />
          <span class="ingredient__checkbox"></span>
          <div class="ingredient__container">
            <span class="ingredient__title">{{ product.product.title }}</span>
            <span class="ingredient__value">
              <span v-if="product.count !== null">{{
                formatCount(product.count)
              }}</span>
              {{ product.measure.title }}
            </span>
          </div>
        </label>
      </div>
    </div>
    <div class="steps">
      <h2>Шаги приготовления</h2>
      <Step
        v-for="(step, index) in recipe.steps"
        :key="index"
        :step="step"
        :index="index"
        mode="view"
      />
    </div>
  </div>
  <div v-if="recipe" class="btn-group end">
    <Mark
      v-for="(mark, index) in recipe.marks"
      :key="index"
      :mark="mark"
      markType="mark"
      contentType="recipe"
    />
  </div>
  <Comments v-if="recipe" :comments="recipe.comments" :recipeId="recipe.id" />
  <div v-else>Рецепт не найден</div>
</template>

<style lang="scss" scoped>
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/form" as *;

.preview {
  display: flex;
  width: 100%;
  height: 31.25rem; // 500px
  img {
    box-shadow: $shadow;
    object-fit: cover;
    width: 100%;
    height: 100%;
    border-radius: $border;
  }
}

.cards-info {
  display: flex;
  flex-direction: row;
  gap: 2.5rem; // 40px
}

.card-info {
  display: flex;
  gap: 0.625rem; // 10px
  flex-direction: column;

  .card-info__title {
    font-weight: 500;
  }

  .card-info__var {
    display: flex;
    gap: 0.625rem; // 10px
    align-items: center;
    flex-direction: row;
  }
}

.time {
  color: $text-info;
  font-weight: 400;
}

.author {
  display: flex;
  align-items: center;
  flex-direction: row;
  gap: 0.625rem; // 10px
  font-weight: 400;

  img {
    object-fit: cover;
    width: 2.5rem; // 40px
    height: 2.5rem; // 40px
    border-radius: 100%;
  }
}

.content-info {
  display: flex;
  flex-direction: column;
  gap: 1.25rem; // 20px
}

.description {
  line-height: 150%;
}

.cooking {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.875rem; // 30px
  width: 100%;
  align-items: start;
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

  .items {
    display: flex;
    flex-direction: column;
    gap: 1.25rem; // 20px
    width: 100%;

    .ingredient {
      display: flex;
      align-items: center;
      gap: 0.9375rem; // 15px
      width: 100%;
      cursor: pointer;

      input {
        display: none;

        &:checked + .ingredient__checkbox {
          background-color: $accent-color-2;
          border-color: $accent-color-2;

          &::after {
            content: "✓";
            display: flex;
            align-items: center;
            justify-content: center;
            color: $light-text;
            font-size: 0.875rem; // 14px
            font-weight: bold;
          }
        }
      }

      .ingredient__checkbox {
        flex-shrink: 0;
        width: 1.875rem; // 30px
        height: 1.875rem; // 30px
        border: 1px solid $text-info-light;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s, border-color 0.3s;
      }

      .ingredient__container {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
        border-bottom: 1px solid $text-info-light;
        padding: 0.625rem 0; // 10px
      }
    }
  }
}

.steps {
  display: flex;
  flex-direction: column;
  gap: 1.875rem; // 30px
  width: 100%;
}

@media (max-width: 1200px) {
  .preview {
    height: 25rem; // 400px
  }

  .cards-info {
    gap: 1.875rem; // 30px
  }

  .card-info {
    gap: 0.5rem; // 8px
  }

  .author {
    gap: 0.5rem; // 8px

    img {
      width: 2rem; // 32px
      height: 2rem; // 32px
    }
  }

  .content-info {
    gap: 1rem; // 16px
  }

  .cooking {
    grid-template-columns: 1fr;
    gap: 1.25rem; // 20px
  }

  .ingredients {
    gap: 1.25rem; // 20px
    position: static;

    .items {
      gap: 1rem; // 16px

      .ingredient {
        gap: 0.625rem; // 10px

        .ingredient__checkbox {
          width: 1.5rem; // 24px
          height: 1.5rem; // 24px
        }

        .ingredient__container {
          padding: 0.5rem 0; // 8px
        }
      }
    }
  }

  .steps {
    gap: 1.25rem; // 20px
  }
}

@media (max-width: 768px) {
  .preview {
    height: 18.75rem; // 300px
  }

  .cards-info {
    gap: 1.25rem; // 20px
  }

  .card-info {
    gap: 0.375rem; // 6px
  }

  .author {
    gap: 0.375rem; // 6px

    img {
      width: 1.75rem; // 28px
      height: 1.75rem; // 28px
    }
  }

  .content-info {
    gap: 0.75rem; // 12px
  }

  .cooking {
    gap: 1rem; // 16px
  }

  .ingredients {
    gap: 1rem; // 16px

    .items {
      gap: 0.75rem; // 12px

      .ingredient {
        gap: 0.5rem; // 8px

        .ingredient__checkbox {
          width: 1.25rem; // 20px
          height: 1.25rem; // 20px
        }

        .ingredient__container {
          padding: 0.375rem 0; // 6px
        }
      }
    }
  }

  .steps {
    gap: 1rem; // 16px
  }
}

@media (max-width: 480px) {
  .preview {
    height: 12.5rem; // 200px
  }

  .cards-info {
    gap: 0.75rem; // 12px
    flex-direction: column;
  }

  .card-info {
    gap: 0.25rem; // 4px
  }

  .author {
    gap: 0.25rem; // 4px

    img {
      width: 1.5rem; // 24px
      height: 1.5rem; // 24px
    }
  }

  .content-info {
    gap: 0.5rem; // 8px
  }

  .cooking {
    gap: 0.75rem; // 12px
  }

  .ingredients {
    gap: 0.75rem; // 12px

    .items {
      gap: 0.5rem; // 8px

      .ingredient {
        gap: 0.375rem; // 6px

        .ingredient__checkbox {
          width: 1rem; // 16px
          height: 1rem; // 16px
        }

        .ingredient__container {
          padding: 0.25rem 0; // 4px
        }
      }
    }
  }

  .steps {
    gap: 0.75rem; // 12px
  }
}
</style>
