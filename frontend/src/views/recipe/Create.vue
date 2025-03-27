<script setup>
import { useRoute } from "vue-router";
import BaseIcon from "../../components/BaseIcon.vue";
import { ref, onMounted, watch, computed } from "vue";

const route = useRoute();
const data = route.meta.data.data; // Доступ к данным из маршрута

// Состояние для ингредиентов
const portions = ref(1);
const ingredients = ref([]);

// Состояние для шагов
const steps = ref([
  { description: "", previewUrl: null }, // Изначально один шаг
]);

// Добавление нового продукта
const addIngredient = () => {
  ingredients.value.push({
    product_id: "",
    count: "",
    measure_id: "",
  });
};

// Удаление продукта
const removeIngredient = (index) => {
  ingredients.value.splice(index, 1);
};

// Обработка изменения порций
const updatePortions = (delta) => {
  portions.value = Math.max(1, portions.value + delta);
};

// Добавление нового шага
const addStep = () => {
  steps.value.push({ description: "", previewUrl: null });
};

// Удаление шага
const removeStep = (index) => {
  if (steps.value.length > 1) {
    // Оставляем минимум один шаг
    steps.value.splice(index, 1);
  }
};

// Основное описание рецепта
const description = ref("");
const textareaRef = ref(null); // Для основного описания
const stepTextareaRefs = ref([]); // Для текстовых полей шагов

// Превью изображения для основного фото
const previewUrl = ref(null);

// Выбор сложности и приватности
const selectedComplexity = ref("");
const selectedPrivate = ref("");

// Метки
const searchMark = ref("");
const selectedMarks = ref([]);
const activeMarkType = ref(null);
const isMarkInputFocused = ref(false);

// Автоматическая подстройка высоты textarea
const adjustTextareaHeight = (textarea) => {
  if (textarea) {
    textarea.style.height = "auto";
    textarea.style.height = `${textarea.scrollHeight}px`;
  }
};

// Обработка загрузки фото для основного изображения
const onFileSelected = (event) => {
  const file = event.target.files[0];
  if (file) {
    previewUrl.value = URL.createObjectURL(file);
  }
};

// Обработка загрузки фото для шага
const onStepFileSelected = (event, index) => {
  const file = event.target.files[0];
  if (file) {
    steps.value[index].previewUrl = URL.createObjectURL(file);
  }
};

// Фильтрация меток
const filteredMarks = computed(() => {
  const marks = Object.values(data.marks);
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

// Отслеживание изменений в основном описании
watch(description, () => {
  adjustTextareaHeight(textareaRef.value);
});

// Отслеживание изменений в описании шагов
watch(
  () => steps.value.map((step) => step.description),
  () => {
    stepTextareaRefs.value.forEach((textarea) =>
      adjustTextareaHeight(textarea)
    );
  },
  { deep: true }
);

// Инициализация высоты всех textarea при монтировании
onMounted(() => {
  adjustTextareaHeight(textareaRef.value);
  stepTextareaRefs.value.forEach((textarea) => adjustTextareaHeight(textarea));
});
</script>

<template>
  <form action="" class="create-form">
    <h1>Создать рецепт</h1>
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
      <input
        type="text"
        name="title"
        class="input-title"
        placeholder="Заголовок"
      />
      <textarea
        v-model="description"
        ref="textareaRef"
        name="description"
        class="input-description"
        placeholder="Описание"
        rows="3"
      ></textarea>
      <div class="label-group">
        <label for="time" class="label small">
          Время приготовления
          <input
            type="text"
            name="time"
            class="input-form"
            placeholder="15 минут"
          />
        </label>
        <label for="complexity" class="label small">
          Сложность
          <div class="select-wrapper">
            <select
              v-model="selectedComplexity"
              name="complexity"
              class="input-form custom-select"
              id="complexity"
            >
              <option value="" disabled selected>Выберите сложность</option>
              <option
                v-for="(complexity, id) in data.complexities"
                :key="id"
                :value="id"
              >
                {{ complexity.title }}
              </option>
            </select>
            <BaseIcon
              viewBox="0 0 29 16"
              class="icon-dark-25-2 select-arrow"
              name="arrow"
            />
          </div>
        </label>
        <label for="private" class="label small">
          Кому доступен рецепт?
          <div class="select-wrapper">
            <select
              v-model="selectedPrivate"
              name="private"
              class="input-form custom-select"
              id="private"
            >
              <option value="" disabled selected>Выберите доступ</option>
              <option
                v-for="(value, key) in data.privates"
                :key="key"
                :value="key"
              >
                {{ value }}
              </option>
            </select>
            <BaseIcon
              viewBox="0 0 29 16"
              class="icon-dark-25-2 select-arrow"
              name="arrow"
            />
          </div>
        </label>
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
        <!-- Скрытые input для отправки меток в форму -->
        <input
          v-for="mark in selectedMarks"
          :key="mark.id"
          type="hidden"
          name="marks[]"
          :value="mark.id"
        />
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
            <input
              v-model="portions"
              class=""
              id="portions"
              type="number"
              min="1"
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
              <div class="select-wrapper">
                <select
                  v-model="ingredient.product_id"
                  :name="'product_id_' + index"
                  class="input-form custom-select"
                >
                  <option value="" disabled selected>Выберите продукт</option>
                  <option
                    v-for="(product, key) in data.products"
                    :key="key"
                    :value="key"
                  >
                    {{ product.title }}
                  </option>
                </select>
                <BaseIcon
                  viewBox="0 0 29 16"
                  class="icon-dark-15-2 select-arrow"
                  name="arrow"
                />
              </div>
              <input
                v-model="ingredient.count"
                :name="'count_' + index"
                type="number"
                min="1"
                class="input-count"
                placeholder="N"
              />
              <div class="select-wrapper">
                <select
                  v-model="ingredient.measure_id"
                  :name="'measure_id_' + index"
                  class="input-form custom-select"
                >
                  <option value="" disabled selected>Ед.</option>
                  <option
                    v-for="(value, key) in data.measures"
                    :key="key"
                    :value="key"
                  >
                    {{ value }}
                  </option>
                </select>
                <BaseIcon
                  viewBox="0 0 29 16"
                  class="icon-dark-15-2 select-arrow"
                  name="arrow"
                />
              </div>
            </div>
          </label>
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
          <div class="step__info">
            <input type="text" class="input-title" placeholder="Шаг" />
            <textarea
              v-model="step.description"
              :ref="(el) => (stepTextareaRefs[index] = el)"
              name="step-description"
              class="input-description"
              placeholder="Описание"
              rows="3"
            ></textarea>
          </div>
        </section>
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
      min-width: 100px; /* Чтобы input не сжимался слишком сильно */
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

    &::placeholder {
      font-weight: 300;
    }
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

  .select-wrapper {
    position: relative;
    width: 100%;
  }

  .input-form {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    border-radius: $border;
    font-size: 20px;
    font-weight: 400;
    border: 1px solid $text-info-light;
    width: 100%;

    &::placeholder {
      font-weight: 300;
    }
  }

  .custom-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding-right: 40px;
  }

  .select-arrow {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
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

  .steps {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    gap: 30px;
    line-height: 150%;

    .btn-dark {
      justify-content: center;
      padding: 5px;
      width: 100%;
    }

    .step {
      display: flex;
      flex-direction: column;
      gap: 30px;
      width: 100%;

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

      .input-title {
        width: 100%;
        font-size: 25px;
        font-weight: 500;
      }

      h3 {
        font-size: 25px;
        font-weight: 500;
      }

      .step__info {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }
    }
  }

  .cooking {
    display: grid;
    grid-template-columns: 1fr 1fr; // Две колонки равной ширины
    gap: 30px;
    width: 100%; // Полная ширина контейнера
    align-items: start; // Выравнивание элементов по началу
  }
}

.ingredients {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  gap: 30px;
  width: 100%;
  position: sticky;
  top: 6.25rem; // Отступ от верха равен высоте header
  max-height: calc(100vh - 6.25rem); // Учитываем высоту header в максимальной высоте
  overflow-y: auto; // Прокрутка внутри блока, если контент превышает высоту

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
        flex-direction: row;
        gap: 10px;
        align-items: center;
        width: 100%;
        border-bottom: 1px solid $text-info-light;
        padding: 10px 0;

        .input-count {
          width: 60px;
          height: 40px;
          padding: 10px 0;
          text-align: center;
          font-size: 20px;
          flex-shrink: 0;
          display: block;
        }

        .select-wrapper {
          &:first-child {
            flex-grow: 1;
          }

          &:last-child {
            width: 90px;
            flex-shrink: 0;
          }

          .custom-select {
            border: none;
            background: transparent;
            padding: 15px 30px 15px 0;
            width: 100%;
          }
        }
      }
    }
  }
}
</style>
