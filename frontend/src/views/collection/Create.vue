<script setup>
import { useRoute } from "vue-router";
import BaseIcon from "../../components/BaseIcon.vue";
import { ref, onMounted, watch, computed } from "vue";

const route = useRoute();
const data = route.meta.data.data; // Доступ к данным из маршрута
console.log(data);

// Основное описание коллекции
const description = ref("");
const textareaRef = ref(null); // Для основного описания

// Превью изображения для основного фото
const previewUrl = ref(null);

// Выбор приватности
const selectedPrivate = ref("");

// Метки
const searchMark = ref("");
const selectedMarks = ref([]);
const activeMarkType = ref(null);
const isMarkInputFocused = ref(false);

// Продукты
const searchProduct = ref("");
const selectedProducts = ref([]);
const activeProductType = ref(null);
const isProductInputFocused = ref(false);

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

// Фильтрация продуктов
const filteredProducts = computed(() => {
  const products = Object.values(data.products);
  if (!searchProduct.value && !activeProductType.value) return products;

  return products.filter((product) => {
    const matchesType = activeProductType.value
      ? product.type_id === Number(activeProductType.value)
      : true;

    const searchValue = searchProduct.value.toLowerCase();
    const productTitle = product.title.toLowerCase();
    const matchesSearch = searchValue ? productTitle.includes(searchValue) : true;

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

const handleMarkBlur = () => {
  setTimeout(() => {
    isMarkInputFocused.value = false;
  }, 200);
};

const selectProductType = (typeId) => {
  activeProductType.value = typeId;
  searchProduct.value = "";
  isProductInputFocused.value = true;
};

const addProduct = (product) => {
  if (!selectedProducts.value.some((p) => p.id === product.id)) {
    selectedProducts.value.push(product);
  }
  searchProduct.value = "";
  isProductInputFocused.value = false;
};

const removeProduct = (productId) => {
  selectedProducts.value = selectedProducts.value.filter((p) => p.id !== productId);
};

const handleProductBlur = () => {
  setTimeout(() => {
    isProductInputFocused.value = false;
  }, 200);
};

// Отслеживание изменений в основном описании
watch(description, () => {
  adjustTextareaHeight(textareaRef.value);
});

// Инициализация высоты textarea при монтировании
onMounted(() => {
  adjustTextareaHeight(textareaRef.value);
});
</script>

<template>
  <form action="" class="create-form">
    <h1>Создать коллекцию</h1>
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
        <label for="private" class="label small">
          Кому доступна коллекция?
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
      <label for="marks" class="tag-container">
        Метки
        <div class="btn-group start">
          <button
            class="tag-type"
            :class="{ active: activeMarkType === null }"
            @click.prevent="selectMarkType(null)"
          >
            Все
          </button>
          <button
            v-for="(type, id) in data.mark_types"
            :key="id"
            class="tag-type"
            :class="{ active: activeMarkType === id }"
            @click.prevent="selectMarkType(id)"
          >
            {{ type }}
          </button>
        </div>
        <div class="tag-search">
          <div class="input-with-tags">
            <div class="tag-items">
              <span
                v-for="mark in selectedMarks"
                :key="mark.id"
                class="tag-item"
              >
                {{ mark.title }}
                <button class="tag-item__close" @click="removeMark(mark.id)">
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
                @blur="handleMarkBlur"
              />
            </div>
          </div>
          <div
            v-if="isMarkInputFocused && filteredMarks.length"
            class="tag-dropdown"
          >
            <div
              v-for="mark in filteredMarks"
              :key="mark.id"
              class="tag-option"
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
      <label for="products" class="tag-container">
        Продукты
        <div class="btn-group start">
          <button
            class="tag-type"
            :class="{ active: activeProductType === null }"
            @click.prevent="selectProductType(null)"
          >
            Все
          </button>
          <button
            v-for="(type, id) in data.product_types"
            :key="id"
            class="tag-type"
            :class="{ active: activeProductType === id }"
            @click.prevent="selectProductType(id)"
          >
            {{ type }}
          </button>
        </div>
        <div class="tag-search">
          <div class="input-with-tags">
            <div class="tag-items">
              <span
                v-for="product in selectedProducts"
                :key="product.id"
                class="tag-item"
              >
                {{ product.title }}
                <button class="tag-item__close" @click="removeProduct(product.id)">
                  <BaseIcon
                    viewBox="0 0 24 24"
                    class="icon-dark-15-1"
                    name="close"
                  />
                </button>
              </span>
              <input
                v-model="searchProduct"
                type="text"
                class="input-form"
                placeholder="Поиск продуктов"
                @focus="isProductInputFocused = true"
                @blur="handleProductBlur"
              />
            </div>
          </div>
          <div
            v-if="isProductInputFocused && filteredProducts.length"
            class="tag-dropdown"
          >
            <div
              v-for="product in filteredProducts"
              :key="product.id"
              class="tag-option"
              @click="addProduct(product)"
            >
              {{ product.title }}
            </div>
          </div>
        </div>
        <!-- Скрытые input для отправки продуктов в форму -->
        <input
          v-for="product in selectedProducts"
          :key="product.id"
          type="hidden"
          name="products[]"
          :value="product.id"
        />
      </label>
    </div>
    <div class="btn-group">
      <input class="btn-dark" type="submit" value="Отправить" />
    </div>
  </form>
</template>

<style lang="scss">
@use "../../assets/styles/variables" as *;

.tag-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
  font-weight: 400;
  width: 100%;

  .input-with-tags {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
  }

  .tag-items {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px;
    border: 1px solid $text-info-light;
    border-radius: $border;
    min-height: 50px;
    align-items: center;
    width: 100%;

    .tag-item {
      display: flex;
      flex-direction: row;
      gap: 5px;
      align-items: center;
      background-color: $background;
      padding: 5px 10px;
      border-radius: $border;
      box-shadow: $shadow;

      .tag-item__close {
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

  .tag-search {
    position: relative;
  }

  .tag-dropdown {
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

  .tag-option {
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
}
</style>