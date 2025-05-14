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

const route = useRoute();
const recipeStore = useRecipeStore();
const recipe = computed(() => recipeStore.currentRecipe);
const authStore = useAuthStore();

// Загружаем рецепт
recipeStore.fetchRecipeById(route.params.id);

const portions = ref(null);
const baseIngredients = ref([]);
const isSaveRecipeOpen = ref(false);

// Проверка, является ли текущий пользователь автором
const isAuthor = computed(() => {
  return (
    authStore.isAuthenticated &&
    authStore.userId &&
    recipe.value &&
    authStore.userId === recipe.value.user_id
  );
});

// Инициализация данных при загрузке рецепта
watch(
  recipe,
  (newRecipe) => {
    if (newRecipe) {
      portions.value = newRecipe.portions;
      baseIngredients.value = newRecipe.products.map((product) => ({
        ...product,
        count: product.count,
      }));
      document.title = newRecipe.title || "Рецепт";
    }
  },
  { immediate: true }
);

// Отладка
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
  if (!recipe.value) return [];
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

const decreasePortions = () => {
  if (portions.value > 1) portions.value--;
};

const increasePortions = () => {
  portions.value++;
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
      <div class="portions-container">
        <span>Порции</span>
        <div class="portions">
          <button type="button" @click="decreasePortions">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-45-2" name="minus" />
          </button>
          <input
            class=""
            id="portions"
            type="number"
            min="1"
            v-model="portions"
          />
          <button type="button" @click="increasePortions">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-45-2" name="plus" />
          </button>
        </div>
      </div>
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
      <section v-for="(step, index) in recipe.steps" :key="index" class="step">
        <div class="preview">
          <img :src="step.photo" alt="" />
        </div>
        <div class="step__info">
          <h3>{{ step.title }}</h3>
          <p>{{ step.description }}</p>
        </div>
      </section>
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

<style lang="scss">
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/style";

// Восстановлены оригинальные стили
.preview {
  display: flex;
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

.cards-info {
  display: flex;
  flex-direction: row;
  gap: 40px;
}

.card-info {
  display: flex;
  gap: 10px;
  flex-direction: column;

  .card-info__title {
    font-weight: 500;
  }

  .card-info__var {
    display: flex;
    gap: 10px;
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
  gap: 10px;
  font-weight: 400;

  img {
    object-fit: cover;
    width: 40px;
    height: 40px;
    border-radius: 100%;
  }
}

.content-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.description {
  line-height: 150%;
}

.cooking {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  width: 100%;
  align-items: start;
}

.ingredients {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 30px;
  width: 100%;

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
    }
  }

  .items {
    display: flex;
    flex-direction: column;
    gap: 20px;
    width: 100%;

    .ingredient {
      display: flex;
      align-items: center;
      gap: 15px;
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
            font-size: 14px;
            font-weight: bold;
          }
        }
      }

      .ingredient__checkbox {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
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
        padding: 10px 0;
      }
    }
  }
}
</style>
