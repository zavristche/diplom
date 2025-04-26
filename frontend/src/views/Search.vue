<script setup>
import { ref, computed } from "vue";
import { useSearchStore } from "../stores/search";
import BaseIcon from "../components/BaseIcon.vue";
import Tabs from "../components/Tabs.vue";

const searchStore = useSearchStore();
const data = ref(searchStore.searchData); // Берем данные из Pinia

// Активный таб
const activeTab = ref(0);
const updateActiveTab = (index) => {
  activeTab.value = index;
};

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

// Фильтрация меток
const filteredMarks = computed(() => {
  const marks = Object.values(data.value?.marks || {});
  if (!searchMark.value && !activeMarkType.value) return marks;

  return marks.filter((mark) => {
    const matchesType = activeMarkType.value ? mark.type_id === Number(activeMarkType.value) : true;
    const searchValue = searchMark.value.toLowerCase();
    const markTitle = mark.title.toLowerCase();
    const matchesSearch = searchValue ? markTitle.includes(searchValue) : true;
    return matchesType && matchesSearch;
  });
});

// Фильтрация продуктов
const filteredProducts = computed(() => {
  const products = Object.values(data.value?.products || {});
  if (!searchProduct.value && !activeProductType.value) return products;

  return products.filter((product) => {
    const matchesType = activeProductType.value ? product.type_id === Number(activeProductType.value) : true;
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
  if (!selectedMarks.value.some((m) => m.id === mark.id)) selectedMarks.value.push(mark);
  searchMark.value = "";
  isMarkInputFocused.value = false;
};

const removeMark = (markId) => {
  selectedMarks.value = selectedMarks.value.filter((m) => m.id !== markId);
};

const handleMarkBlur = () => {
  setTimeout(() => (isMarkInputFocused.value = false), 200);
};

const selectProductType = (typeId) => {
  activeProductType.value = typeId;
  searchProduct.value = "";
  isProductInputFocused.value = true;
};

const addProduct = (product) => {
  if (!selectedProducts.value.some((p) => p.id === product.id)) selectedProducts.value.push(product);
  searchProduct.value = "";
  isProductInputFocused.value = false;
};

const removeProduct = (productId) => {
  selectedProducts.value = selectedProducts.value.filter((p) => p.id !== productId);
};

const handleProductBlur = () => {
  setTimeout(() => (isProductInputFocused.value = false), 200);
};

const handleSubmit = (event) => {
  event.preventDefault();
  console.log({ activeTab: activeTab.value, marks: selectedMarks.value, products: selectedProducts.value });
};
</script>

<template>
  <form @submit="handleSubmit" class="create-form">
    <h1>Поиск</h1>
    <Tabs
      :tabs="['Рецепты', 'Коллекции', 'Пользователи']"
      @update:activeTab="updateActiveTab"
    />
    <div class="tab-content">
      <!-- Поиск по рецептам -->
      <div class="tab-pane" :class="{ active: activeTab === 0 }">
        <div class="label-group">
          <label for="title" class="label small">
            Заголовок
            <input
              type="text"
              name="title"
              class="input-form"
              placeholder="Введите заголовок"
            />
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
          <input
            v-for="product in selectedProducts"
            :key="product.id"
            type="hidden"
            name="products[]"
            :value="product.id"
          />
        </label>
      </div>

      <!-- Поиск по коллекциям -->
      <div class="tab-pane" :class="{ active: activeTab === 1 }">
        <div class="label-group">
          <label for="title" class="label small">
            Заголовок
            <input
              type="text"
              name="title"
              class="input-form"
              placeholder="Введите заголовок"
            />
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
          <input
            v-for="product in selectedProducts"
            :key="product.id"
            type="hidden"
            name="products[]"
            :value="product.id"
          />
        </label>
      </div>

      <!-- Поиск по пользователям -->
      <div class="tab-pane" :class="{ active: activeTab === 2 }">
        <div class="label-group">
          <label for="login" class="label small">
            Логин
            <input
              type="text"
              name="login"
              class="input-form"
              placeholder="Введите логин"
            />
          </label>
          <label for="name" class="label small">
            Имя
            <input
              type="text"
              name="name"
              class="input-form"
              placeholder="Введите имя"
            />
          </label>
          <label for="surname" class="label small">
            Фамилия
            <input
              type="text"
              name="surname"
              class="input-form"
              placeholder="Введите фамилия"
            />
          </label>
        </div>
      </div>
    </div>
    <div class="btn-group">
      <button class="btn-dark" type="submit">Отправить</button>
    </div>
  </form>
</template>

<style lang="scss">
@use "../assets/styles/variables" as *;

.create-form {
  display: flex;
  flex-direction: column;
  gap: 50px;
  width: 100%;
  max-width: 1200px; // Установите желаемую максимальную ширину
  margin: 0 auto; // Центрирование формы
  align-items: flex-start;
}

.tab-content {
  width: 100%;
}

.tab-pane {
  display: none;
  
  &.active {
    display: flex;
    flex-direction: column;
    gap: 30px;
  }
}

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
    padding: 10px;
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
      min-width: 100px;
    }
  }

  .tag-search {
    position: relative;
    width: 100%;
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

.btn-group {
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
  gap: 30px;
  justify-content: flex-end;

  &.start {
    justify-content: flex-start;
  }
}
</style>