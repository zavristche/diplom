<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useSearchStore } from "../stores/search";
import RecipeService from "../api/RecipeService";
import CollectionService from "../api/CollectionService";
import UserService from "../api/UserService";
import BaseIcon from "../components/BaseIcon.vue";
import Tabs from "../components/Tabs.vue";
import Select from "../components/Select.vue";

const router = useRouter();
const route = useRoute();
const searchStore = useSearchStore();

// Загружаем данные при монтировании
onMounted(async () => {
  console.log('Search.vue: Mounting, checking searchData');
  await searchStore.fetchSearchData();
  console.log('Search.vue: searchData after mount:', searchStore.searchData);
});

const data = computed(() => {
  const metaData = searchStore.searchData || {};
  console.log('Search.vue: data.value:', metaData);
  return {
    marks: metaData.marks || {},
    products: metaData.products || {},
    mark_types: metaData.mark_types || {},
    product_types: metaData.product_types || {},
    complexities: metaData.complexities || [],
  };
});

// Активный таб
const activeTab = ref(0);

// Синхронизация activeTab с route.params.type
const typeToTab = {
  recipe: 0,
  collection: 1,
  author: 2,
};
const tabToType = ["recipe", "collection", "author"];
if (route.params.type && typeToTab[route.params.type] !== undefined) {
  activeTab.value = typeToTab[route.params.type];
} else {
  // По умолчанию Рецепты
  router.replace({ name: "search", params: { type: "recipe" } });
}

// Динамический заголовок
const searchTitle = computed(() => {
  switch (activeTab.value) {
    case 0:
      return "Поиск по рецептам";
    case 1:
      return "Поиск по коллекциям";
    case 2:
      return "Поиск по пользователям";
    default:
      return "Поиск";
  }
});

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

// Поля для поиска
const recipeTitle = ref("");
const collectionTitle = ref("");
const userLogin = ref("");
const userName = ref("");
const userSurname = ref("");
const selectedComplexity = ref("");

// Сброс полей для указанного таба
const resetFields = (tabIndex) => {
  if (tabIndex === 0) {
    recipeTitle.value = "";
    selectedMarks.value = [];
    selectedProducts.value = [];
    selectedComplexity.value = "";
  } else if (tabIndex === 1) {
    collectionTitle.value = "";
    selectedMarks.value = [];
    selectedProducts.value = [];
  } else if (tabIndex === 2) {
    userLogin.value = "";
    userName.value = "";
    userSurname.value = "";
  }
};

// Переключение табов
const updateActiveTab = (index) => {
  if (activeTab.value !== index) {
    resetFields(activeTab.value); // Сбрасываем поля предыдущего таба
    activeTab.value = index;
    const type = tabToType[index];
    router.push({ name: "search", params: { type }, query: {} }); // Очищаем query при смене таба
  }
};

// Фильтрация меток
const filteredMarks = computed(() => {
  const marksList = Object.values(data.value.marks || {});
  if (!searchMark.value && !activeMarkType.value) return marksList;
  return marksList.filter((mark) => {
    const matchesType = activeMarkType.value ? mark.type_id === Number(activeMarkType.value) : true;
    const matchesSearch = searchMark.value.toLowerCase() ? mark.title.toLowerCase().includes(searchMark.value.toLowerCase()) : true;
    return matchesType && matchesSearch;
  });
});

// Фильтрация продуктов
const filteredProducts = computed(() => {
  const productsList = Object.values(data.value.products || {});
  if (!searchProduct.value && !activeProductType.value) return productsList;
  return productsList.filter((product) => {
    const matchesType = activeProductType.value ? product.type_id === Number(activeProductType.value) : true;
    const matchesSearch = searchProduct.value.toLowerCase() ? product.title.toLowerCase().includes(searchProduct.value.toLowerCase()) : true;
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
  setTimeout(() => (isMarkInputFocused.value = false), 200);
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
  setTimeout(() => (isProductInputFocused.value = false), 200);
};

const handleSubmit = async (event) => {
  event.preventDefault();

  try {
    let response;
    const type = tabToType[activeTab.value];
    let query = {};

    if (activeTab.value === 0) {
      // Поиск по рецептам
      query = {
        title: recipeTitle.value || undefined,
        marks: selectedMarks.value.map((mark) => mark.id),
        products: selectedProducts.value.map((product) => product.id),
        complexity_id: selectedComplexity.value || undefined,
      };
      response = await RecipeService.search(query);
      console.log("Search results (Recipes):", response.data);
    } else if (activeTab.value === 1) {
      // Поиск по коллекциям
      query = {
        title: collectionTitle.value || undefined,
        marks: selectedMarks.value.map((mark) => mark.id),
        products: selectedProducts.value.map((product) => product.id),
      };
      response = await CollectionService.search(query);
      console.log("Search results (Collections):", response.data);
    } else if (activeTab.value === 2) {
      // Поиск по пользователям
      query = {
        login: userLogin.value || undefined,
        name: userName.value || undefined,
        surname: userSurname.value || undefined,
      };
      response = await UserService.search(query);
      console.log("Search results (Users):", response.data);
    }

    // Обновляем URL с query-параметрами
    router.push({ name: "search", params: { type }, query });
  } catch (error) {
    console.error("Search error:", error.response?.data || error.message);
  }
};

// Синхронизация activeTab при изменении route.params.type
watch(
  () => route.params.type,
  (newType) => {
    if (newType && typeToTab[newType] !== undefined && typeToTab[newType] !== activeTab.value) {
      resetFields(activeTab.value);
      activeTab.value = typeToTab[newType];
    }
  }
);
</script>

<template>
<form @submit="handleSubmit" class="create-form">
  <h1>{{ searchTitle }}</h1>
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
            v-model="recipeTitle"
            type="text"
            name="title"
            class="input-form"
            placeholder="Введите заголовок"
          />
        </label>
        <Select
          v-model="selectedComplexity"
          label="Сложность"
          name="complexity_id"
          placeholder="Выберите сложность"
          :options="data.complexities"
        />
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
                @blur="handleMarkBlur"
              />
            </div>
          </div>
          <div v-if="isMarkInputFocused" class="mark-dropdown">
            <div v-if="filteredMarks.length" class="mark-options">
              <div
                v-for="mark in filteredMarks"
                :key="mark.id"
                class="mark-option"
                @click="addMark(mark)"
              >
                {{ mark.title }}
              </div>
            </div>
            <div v-else class="mark-option">Нет подходящих меток</div>
          </div>
        </div>
      </label>
      <label for="products" class="marks">
        Продукты
        <div class="btn-group start">
          <button
            class="mark_type"
            :class="{ active: activeProductType === null }"
            @click.prevent="selectProductType(null)"
          >
            Все
          </button>
          <button
            v-for="(type, id) in data.product_types"
            :key="id"
            class="mark_type"
            :class="{ active: activeProductType === id }"
            @click.prevent="selectProductType(id)"
          >
            {{ type }}
          </button>
        </div>
        <div class="mark-search">
          <div class="input-with-marks">
            <div class="mark-items">
              <span
                v-for="product in selectedProducts"
                :key="product.id"
                class="mark-item"
              >
                {{ product.title }}
                <button class="mark-item__close" @click="removeProduct(product.id)">
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
          <div v-if="isProductInputFocused" class="mark-dropdown">
            <div v-if="filteredProducts.length" class="mark-options">
              <div
                v-for="product in filteredProducts"
                :key="product.id"
                class="mark-option"
                @click="addProduct(product)"
              >
                {{ product.title }}
              </div>
            </div>
            <div v-else class="mark-option">Нет подходящих продуктов</div>
          </div>
        </div>
      </label>
    </div>

    <!-- Поиск по коллекциям -->
    <div class="tab-pane" :class="{ active: activeTab === 1 }">
      <div class="label-group">
        <label for="title" class="label small">
          Заголовок
          <input
            v-model="collectionTitle"
            type="text"
            name="title"
            class="input-form"
            placeholder="Введите заголовок"
          />
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
                @blur="handleMarkBlur"
              />
            </div>
          </div>
          <div v-if="isMarkInputFocused" class="mark-dropdown">
            <div v-if="filteredMarks.length" class="mark-options">
              <div
                v-for="mark in filteredMarks"
                :key="mark.id"
                class="mark-option"
                @click="addMark(mark)"
              >
                {{ mark.title }}
              </div>
            </div>
            <div v-else class="mark-option">Нет подходящих меток</div>
          </div>
        </div>
      </label>
      <label for="products" class="marks">
        Продукты
        <div class="btn-group start">
          <button
            class="mark_type"
            :class="{ active: activeProductType === null }"
            @click.prevent="selectProductType(null)"
          >
            Все
          </button>
          <button
            v-for="(type, id) in data.product_types"
            :key="id"
            class="mark_type"
            :class="{ active: activeProductType === id }"
            @click.prevent="selectProductType(id)"
          >
            {{ type }}
          </button>
        </div>
        <div class="mark-search">
          <div class="input-with-marks">
            <div class="mark-items">
              <span
                v-for="product in selectedProducts"
                :key="product.id"
                class="mark-item"
              >
                {{ product.title }}
                <button class="mark-item__close" @click="removeProduct(product.id)">
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
          <div v-if="isProductInputFocused" class="mark-dropdown">
            <div v-if="filteredProducts.length" class="mark-options">
              <div
                v-for="product in filteredProducts"
                :key="product.id"
                class="mark-option"
                @click="addProduct(product)"
              >
                {{ product.title }}
              </div>
            </div>
            <div v-else class="mark-option">Нет подходящих продуктов</div>
          </div>
        </div>
      </label>
    </div>

    <!-- Поиск по пользователям -->
    <div class="tab-pane" :class="{ active: activeTab === 2 }">
      <div class="label-group">
        <label for="login" class="label small">
          Логин
          <input
            v-model="userLogin"
            type="text"
            name="login"
            class="input-form"
            placeholder="Введите логин"
          />
        </label>
        <label for="name" class="label small">
          Имя
          <input
            v-model="userName"
            type="text"
            name="name"
            class="input-form"
            placeholder="Введите имя"
          />
        </label>
        <label for="surname" class="label small">
          Фамилия
          <input
            v-model="userSurname"
            type="text"
            name="surname"
            class="input-form"
            placeholder="Введите фамилию"
          />
        </label>
      </div>
    </div>
  </div>
  <div class="btn-group">
    <button class="btn-dark" type="submit">Поиск</button>
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
  max-width: 1200px;
  margin: 0 auto;
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

  .mark-options {
    display: flex;
    flex-direction: column;
  }

  .mark-option {
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
</style>