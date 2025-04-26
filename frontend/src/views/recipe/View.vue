<script setup>
import { ref, computed } from "vue";
import { useRecipeStore } from "../../stores/recipe";
import BaseIcon from "../../components/BaseIcon.vue";
import SaveRecipe from "../../components/SaveRecipe.vue";

const recipeStore = useRecipeStore();
const recipe = computed(() => recipeStore.currentRecipe); // Берем данные из хранилища

const portions = ref(null);
const baseIngredients = ref([]);
const formVisibility = ref({});
const commentTexts = ref({});
const previewUrls = ref({});
const fileNames = ref({});
const newCommentPreviewUrl = ref(null);
const newCommentFileName = ref(null);
const isSaveRecipeOpen = ref(false);

// Инициализация данных при первой загрузке
if (recipe.value) {
  portions.value = recipe.value.portions;
  baseIngredients.value = recipe.value.products.map((product) => ({
    ...product,
    count: product.count,
  }));
  document.title = recipe.value.title || "Рецепт";
}

const adjustedIngredients = computed(() => {
  if (!recipe.value) return []; // Проверка на случай, если данные не загрузились
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

const toggleForm = (id) => {
  formVisibility.value[id] = !formVisibility.value[id];
};

const onFileSelected = (event, id = null) => {
  const file = event.target.files[0];
  if (file) {
    if (id === null) {
      newCommentPreviewUrl.value = URL.createObjectURL(file);
      newCommentFileName.value = file.name;
    } else {
      previewUrls.value[id] = URL.createObjectURL(file);
      fileNames.value[id] = file.name;
    }
  }
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
    <div class="author">
      <img :src="recipe.user.avatar" alt="" />
      {{ recipe.user.login }}
    </div>
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
    <div class="btn-group">
      <button type="submit" class="btn-dark" @click="isSaveRecipeOpen = true">
        <BaseIcon
          viewBox="0 0 25 26"
          class="icon-white-30-2"
          name="book"
        />Сохранить
      </button>
      <button type="submit" class="btn-dark">
        <BaseIcon
          viewBox="0 0 29 26"
          class="icon-white-30-2"
          name="heartb"
        />Оценить
      </button>
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
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-45-2" name="pluse" />
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
  <div v-if="recipe" class="btn-group">
    <button
      v-for="(mark, index) in recipe.marks"
      :key="index"
      class="btn-dark line"
    >
      <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="mark" />{{
        mark.title
      }}
    </button>
  </div>
  <section v-if="recipe" class="comments">
    <h2>Комментарии</h2>
    <form action="submit" method="post" id="comment">
      <input name="recipe_id" type="hidden" :value="recipe.id" />
      <textarea
        name="text"
        rows="4"
        cols="50"
        placeholder="Введите текст комментария"
      ></textarea>
      <div class="btn-group">
        <div v-if="newCommentPreviewUrl" class="preview-container">
          <img :src="newCommentPreviewUrl" alt="Превью" class="preview-img" />
          <span class="file-name">{{ newCommentFileName }}</span>
        </div>
        <label class="btn-dark line" style="cursor: pointer">
          <input
            type="file"
            accept="image/*"
            style="display: none"
            @change="onFileSelected($event)"
          />
          <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
          Загрузить фото
        </label>
        <input class="btn-dark" type="submit" value="Отправить" />
      </div>
    </form>
    <div
      v-for="(comment, index) in recipe.comments"
      :key="index"
      class="comment"
    >
      <span class="time">{{ comment.created_at }}</span>
      <div class="author">
        <img :src="comment.user.avatar" alt="" />
        {{ comment.user.login }}
      </div>
      <p>{{ comment.text }}</p>
      <button class="btn-small" @click="toggleForm(comment.id)">
        Ответить
      </button>
      <form v-if="formVisibility[comment.id]" action="post" id="comment-answer">
        <input name="parent_id" type="hidden" :value="comment.id" />
        <textarea
          :id="'comment-' + comment.id"
          name="text"
          rows="4"
          cols="50"
          placeholder="Введите текст комментария"
          v-model="commentTexts[comment.id]"
        ></textarea>
        <div class="btn-group">
          <div v-if="previewUrls[comment.id]" class="preview-container">
            <img
              :src="previewUrls[comment.id]"
              alt="Превью"
              class="preview-img"
            />
            <span class="file-name">{{ fileNames[comment.id] }}</span>
          </div>
          <label class="btn-dark line" style="cursor: pointer">
            <input
              type="file"
              accept="image/*"
              style="display: none"
              @change="onFileSelected($event, comment.id)"
            />
            <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
            Загрузить фото
          </label>
          <input class="btn-dark" type="submit" value="Отправить" />
        </div>
      </form>
    </div>
  </section>
  <div v-else>Рецепт не найден</div>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;
@use "../../assets/styles/style";

// Стили остаются без изменений, копирую их как есть
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

.content-container {
  display: grid;
  width: 100%;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}

.content-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.btn-group {
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
  justify-content: end;
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

.comments {
  display: flex;
  flex-direction: column;
  gap: 30px;

  form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 30px;

    textarea {
      box-shadow: 0rem 0rem 0.61rem 0rem rgba(0, 0, 0, 0.1);
      background: $background;
      border-radius: 25px;
      padding: 30px;
    }
  }

  .comment {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
}

.preview-container {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 20px;
  color: $text-info;
}

.preview-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: $border;
}
</style>
