<script setup>
import { ref, computed, watch, onMounted, nextTick } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useRecipeStore } from "../stores/recipe";
import { useCollectionStore } from "../stores/collection";
import { useUserStore } from "../stores/user";
import { useSearchStore } from "../stores/search";
import BaseIcon from "../components/BaseIcon.vue";
import Tabs from "../components/Tabs.vue";
import Select from "../components/Select.vue";
import Recipe from "../components/Recipe.vue";
import Collection from "../components/Collection.vue";
import User from "../components/User.vue";

const router = useRouter();
const route = useRoute();
const recipeStore = useRecipeStore();
const collectionStore = useCollectionStore();
const userStore = useUserStore();
const searchStore = useSearchStore();

// Флаг для блокировки рендеринга
const isSwitching = ref(false);
// Состояние загрузки
const isLoading = ref(true);
// Результаты поиска
const recipes = ref([]);
const collections = ref([]);
const users = ref([]);

// Данные из searchStore
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
  user: 2,
};
const tabToType = ["recipe", "collection", "user"];

// Инициализация activeTab
if (route.params.type && typeToTab[route.params.type] !== undefined) {
  activeTab.value = typeToTab[route.params.type];
  console.log('Search.vue: Initial activeTab from route:', activeTab.value, 'Route:', route.fullPath);
} else {
  activeTab.value = 0;
  router.replace({ name: "search", params: { type: "recipe" }, query: {} });
  console.log('Search.vue: Default activeTab:', activeTab.value, 'Route:', route.fullPath);
}

// Динамический заголовок
const searchTitle = computed(() => {
  console.log('Search.vue: searchTitle computed:', activeTab.value);
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

// Инициализация полей формы на основе query параметров
const initializeFromQuery = async () => {
  if (activeTab.value === 0 && route.query) {
    if (route.query.title) {
      recipeTitle.value = route.query.title;
    }
    if (route.query.complexity_id) {
      selectedComplexity.value = route.query.complexity_id;
    }
    if (route.query.marks) {
      const markIds = Array.isArray(route.query.marks) ? route.query.marks : [route.query.marks];
      const marksList = Object.values(data.value.marks || {});
      selectedMarks.value = marksList.filter(mark => markIds.includes(String(mark.id)));
    }
    if (route.query.products) {
      const productIds = Array.isArray(route.query.products) ? route.query.products : [route.query.products];
      const productsList = Object.values(data.value.products || {});
      selectedProducts.value = productsList.filter(product => productIds.includes(String(product.id)));
    }
  } else if (activeTab.value === 1 && route.query) {
    if (route.query.title) {
      collectionTitle.value = route.query.title;
    }
    if (route.query.marks) {
      const markIds = Array.isArray(route.query.marks) ? route.query.marks : [route.query.marks];
      const marksList = Object.values(data.value.marks || {});
      selectedMarks.value = marksList.filter(mark => markIds.includes(String(mark.id)));
    }
    if (route.query.products) {
      const productIds = Array.isArray(route.query.products) ? route.query.products : [route.query.products];
      const productsList = Object.values(data.value.products || {});
      selectedProducts.value = productsList.filter(product => productIds.includes(String(product.id)));
    }
  } else if (activeTab.value === 2 && route.query) {
    if (route.query.login) {
      userLogin.value = route.query.login;
    }
    if (route.query.name) {
      userName.value = route.query.name;
    }
    if (route.query.surname) {
      userSurname.value = route.query.surname;
    }
  }
};

// Загрузка данных для текущего таба
const loadTabData = async (tabIndex) => {
  isLoading.value = true;
  try {
    if (tabIndex === 0) {
      await recipeStore.fetchRecipes();
      recipes.value = recipeStore.recipes;
      console.log('Search.vue: Loaded recipes:', recipes.value);
    } else if (tabIndex === 1) {
      await collectionStore.fetchCollections();
      collections.value = collectionStore.collections;
      console.log('Search.vue: Loaded collections:', collections.value);
    } else if (tabIndex === 2) {
      await userStore.searchUsers({});
      users.value = userStore.users;
      console.log('Search.vue: Loaded users:', users.value);
    }
  } catch (error) {
    console.error('Search.vue: Error loading tab data:', error);
  } finally {
    isLoading.value = false;
  }
};

// Выполнение поиска на основе query параметров
const performSearchFromQuery = async () => {
  if (!Object.keys(route.query).length) return;

  isLoading.value = true;
  try {
    let query = {};
    if (activeTab.value === 0) {
      query = {
        title: recipeTitle.value || undefined,
        marks: selectedMarks.value.length ? selectedMarks.value.map((mark) => mark.id) : undefined,
        products: selectedProducts.value.length ? selectedProducts.value.map((product) => product.id) : undefined,
        complexity_id: selectedComplexity.value || undefined,
      };
      await recipeStore.searchRecipes(query);
      recipes.value = recipeStore.recipes;
      console.log('Search.vue: Search results from query (Recipes):', recipes.value);
    } else if (activeTab.value === 1) {
      query = {
        title: collectionTitle.value || undefined,
        marks: selectedMarks.value.length ? selectedMarks.value.map((mark) => mark.id) : undefined,
        products: selectedProducts.value.length ? selectedProducts.value.map((product) => product.id) : undefined,
      };
      await collectionStore.searchCollections(query);
      collections.value = collectionStore.collections;
      console.log('Search.vue: Search results from query (Collections):', collections.value);
    } else if (activeTab.value === 2) {
      query = {
        login: userLogin.value || undefined,
        name: userName.value || undefined,
        surname: userSurname.value || undefined,
      };
      await userStore.searchUsers(query);
      users.value = userStore.users;
      console.log('Search.vue: Search results from query (Users):', users.value);
    }
  } catch (error) {
    console.error('Search.vue: Error performing search from query:', error);
  } finally {
    isLoading.value = false;
  }
};

// Загрузка начальных данных
onMounted(async () => {
  await loadTabData(activeTab.value);
  await initializeFromQuery();
  await performSearchFromQuery();
  console.log('Search.vue: Initial route:', route.fullPath, 'Query:', route.query);
});

// Сброс полей текущего таба
const resetCurrentFields = async () => {
  console.log('Search.vue: resetCurrentFields for tab:', activeTab.value);
  if (activeTab.value === 0) {
    recipeTitle.value = "";
    selectedMarks.value = [];
    selectedProducts.value = [];
    selectedComplexity.value = "";
    searchMark.value = "";
    searchProduct.value = "";
    activeMarkType.value = null;
    activeProductType.value = null;
    await recipeStore.fetchRecipes();
    recipes.value = recipeStore.recipes;
  } else if (activeTab.value === 1) {
    collectionTitle.value = "";
    selectedMarks.value = [];
    selectedProducts.value = [];
    searchMark.value = "";
    searchProduct.value = "";
    activeMarkType.value = null;
    activeProductType.value = null;
    await collectionStore.fetchCollections();
    collections.value = collectionStore.collections;
  } else if (activeTab.value === 2) {
    userLogin.value = "";
    userName.value = "";
    userSurname.value = "";
    await userStore.searchUsers({});
    users.value = userStore.users;
  }
  await nextTick();
  const type = tabToType[activeTab.value];
  await router.replace({ name: "search", params: { type }, query: {} });
};

// Переключение табов
const updateActiveTab = async (index) => {
  if (activeTab.value === index || isSwitching.value) {
    console.log('Search.vue: updateActiveTab: Ignored:', { index, isSwitching: isSwitching.value });
    return;
  }

  console.log('Search.vue: updateActiveTab:', { oldTab: activeTab.value, newTab: index });
  isSwitching.value = true;
  activeTab.value = index;
  await resetCurrentFields();

  const type = tabToType[index];
  if (route.params.type !== type) {
    await nextTick();
    await router.replace({ name: "search", params: { type }, query: {} });
    console.log('Search.vue: router.replace triggered:', type, 'New URL:', `/search/${type}`, 'Query:', route.query);
  }

  await loadTabData(index);
  await initializeFromQuery();
  await performSearchFromQuery();
  isSwitching.value = false;
  console.log('Search.vue: isSwitching reset, Current route:', route.fullPath, 'Query:', route.query);
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
    isLoading.value = true;
    const type = tabToType[activeTab.value];
    let query = {};

    if (activeTab.value === 0) {
      query = {
        title: recipeTitle.value || undefined,
        marks: selectedMarks.value.length ? selectedMarks.value.map((mark) => mark.id) : undefined,
        products: selectedProducts.value.length ? selectedProducts.value.map((product) => product.id) : undefined,
        complexity_id: selectedComplexity.value || undefined,
      };
      await recipeStore.searchRecipes(query);
      recipes.value = recipeStore.recipes;
      console.log('Search.vue: Search results (Recipes):', recipes.value);
    } else if (activeTab.value === 1) {
      query = {
        title: collectionTitle.value || undefined,
        marks: selectedMarks.value.length ? selectedMarks.value.map((mark) => mark.id) : undefined,
        products: selectedProducts.value.length ? selectedProducts.value.map((product) => product.id) : undefined,
      };
      await collectionStore.searchCollections(query);
      collections.value = collectionStore.collections;
      console.log('Search.vue: Search results (Collections):', collections.value);
    } else if (activeTab.value === 2) {
      query = {
        login: userLogin.value || undefined,
        name: userName.value || undefined,
        surname: userSurname.value || undefined,
      };
      await userStore.searchUsers(query);
      users.value = userStore.users;
      console.log('Search.vue: Search results (Users):', users.value);
    }

    await nextTick();
    await router.replace({ name: "search", params: { type }, query });
    console.log('Search.vue: Search route:', route.fullPath, 'Query:', route.query);
  } catch (error) {
    console.error('Search.vue: Search error:', error);
  } finally {
    isLoading.value = false;
  }
};

// Синхронизация activeTab при изменении route.params.type
watch(
  () => route.params.type,
  async (newType) => {
    if (newType && typeToTab[newType] !== undefined && typeToTab[newType] !== activeTab.value && !isSwitching.value) {
      console.log('Search.vue: watch route.params.type:', { newType, newTab: typeToTab[newType] });
      activeTab.value = typeToTab[newType];
      await initializeFromQuery();
      await performSearchFromQuery();
    }
  },
  { immediate: true }
);

// Реакция на изменение query параметров
watch(
  () => route.query,
  async () => {
    console.log('Search.vue: Query changed:', route.query);
    await initializeFromQuery();
    await performSearchFromQuery();
  },
  { deep: true }
);
</script>

<template>
  <div class="search-wrapper">
    <form @submit="handleSubmit" class="create-form">
      <h1 :key="activeTab">{{ searchTitle }}</h1>
      <Tabs
        :tabs="['Рецепты', 'Коллекции', 'Пользователи']"
        v-model:activeTab="activeTab"
        @update:activeTab="updateActiveTab"
      />
      <div v-if="!isSwitching" class="tab-content">
        <!-- Поиск по рецептам -->
        <div :key="0" class="tab-pane" :class="{ active: activeTab === 0 }">
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
                <div class="mark-items" :key="activeTab">
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
                <div class="mark-items" :key="activeTab">
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
        <div :key="1" class="tab-pane" :class="{ active: activeTab === 1 }">
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
                <div class="mark-items" :key="activeTab">
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
                <div class="mark-items" :key="activeTab">
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
        <div :key="2" class="tab-pane" :class="{ active: activeTab === 2 }">
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
      <div v-if="!isSwitching" class="btn-group end">
        <button class="btn-dark" type="submit">Поиск</button>
        <button class="btn-light" type="button" @click="resetCurrentFields">Сбросить</button>
      </div>
    </form>
    <section class="content-container">
      <div  v-if="isLoading" v-for="n in 6" :key="n" class="skeleton-card"></div>
      <Recipe v-else-if="activeTab === 0" v-for="recipe in recipes" :key="recipe.id" :recipe="recipe" />
      <Collection v-else-if="activeTab === 1" v-for="collection in collections" :key="collection.id" :collection="collection" />
      <User
        v-else-if="activeTab === 2"
        v-for="user in users"
        :key="user.id"
        :user="user"
        @vue:mounted="() => console.log('Search.vue: User card mounted:', { id: user.id, login: user.login, status: user.status })"
      />
      <p v-else-if="!recipes.length && activeTab === 0" class="no-results">Нет рецептов</p>
      <p v-else-if="!collections.length && activeTab === 1" class="no-results">Нет коллекций</p>
      <p v-else-if="!users.length && activeTab === 2" class="no-results">Нет пользователей</p>
    </section>
  </div>
</template>

<style lang="scss">
@use "../assets/styles/variables" as *;

.content-container {
  display: grid;
  width: 100%;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}

.skeleton-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}

.skeleton-card {
  height: 280px;
  background: #eee;
  border-radius: 8px;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.hero {
  display: flex;
  align-items: center;

  h1 {  
    font-weight: 600;
    font-size: 100px;
  }

  .search {
    box-shadow: $shadow;
  }

  .container-col {
    height: 19rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  img {
    width: 524px;
    height: 580px;
    object-fit: cover;
  }
}

.slogan {
  font-size: 32px;
}

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

.btn-dark {
  background-color: $background-dark;
  color: $light;
  padding: 10px 20px;
  border: none;
  border-radius: $border;
  font-size: 18px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.btn-light {
  background-color: $light;
  color: $background-dark;
  padding: 10px 20px;
  border: 1px solid $text-info-light;
  border-radius: $border;
  font-size: 18px;
  cursor: pointer;
  transition: background-color 0.2s ease;

  &:hover {
    background-color: $background;
  }
}

.search-wrapper {
  display: flex;
  flex-direction: column;
  gap: 50px;
  width: 100%;
}

.no-results {
  grid-column: 1 / -1;
  text-align: center;
  color: $text-info;
  font-size: 16px;
  font-family: Rubik, sans-serif;
  padding: 20px;
}

</style>