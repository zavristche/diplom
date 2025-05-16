<script setup>
import { ref, computed, watch, onMounted, nextTick } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useRecipeStore } from "../stores/recipe";
import { useCollectionStore } from "../stores/collection";
import { useUserStore } from "../stores/user";
import SearchService from "../api/searchService";
import Tabs from "../components/Tabs.vue";
import Select from "../components/Select.vue";
import SelectMultiple from "../components/SelectMultiple.vue";
import Recipe from "../components/Recipe.vue";
import Collection from "../components/Collection.vue";
import User from "../components/User.vue";

const router = useRouter();
const route = useRoute();
const recipeStore = useRecipeStore();
const collectionStore = useCollectionStore();
const userStore = useUserStore();

const isSwitching = ref(false);
const isLoading = ref(true);
const recipes = ref([]);
const collections = ref([]);
const users = ref([]);

const recipeTitle = ref("");
const collectionTitle = ref("");
const userLogin = ref("");
const userName = ref("");
const userSurname = ref("");
const selectedComplexity = ref("");
const selectedMarks = ref([]);
const selectedProducts = ref([]);

const complexities = ref({});
const activeTab = ref(0);

const typeToTab = {
  recipe: 0,
  collection: 1,
  user: 2,
};
const tabToType = ["recipe", "collection", "user"];

if (route.params.type && typeToTab[route.params.type] !== undefined) {
  activeTab.value = typeToTab[route.params.type];
} else {
  activeTab.value = 0;
  router.replace({ name: "search", params: { type: "recipe" }, query: {} });
}

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

const isQueryEmpty = computed(() => {
  if (activeTab.value === 0) {
    return (
      !recipeTitle.value &&
      !selectedComplexity.value &&
      selectedMarks.value.length === 0 &&
      selectedProducts.value.length === 0
    );
  } else if (activeTab.value === 1) {
    return (
      !collectionTitle.value &&
      selectedMarks.value.length === 0 &&
      selectedProducts.value.length === 0
    );
  } else if (activeTab.value === 2) {
    return !userLogin.value && !userName.value && !userSurname.value;
  }
  return true;
});

const initializeFromQuery = async () => {
  if (activeTab.value === 0 && route.query) {
    if (route.query.title) recipeTitle.value = route.query.title;
    if (route.query.complexity_id)
      selectedComplexity.value = route.query.complexity_id;
  } else if (activeTab.value === 1 && route.query) {
    if (route.query.title) collectionTitle.value = route.query.title;
  } else if (activeTab.value === 2 && route.query) {
    if (route.query.login) userLogin.value = route.query.login;
    if (route.query.name) userName.value = route.query.name;
    if (route.query.surname) userSurname.value = route.query.surname;
  }
};

const loadInitialSearchData = async () => {
  try {
    const response = await SearchService.getData();
    if (
      response.data &&
      response.data.data &&
      response.data.data.complexities
    ) {
      const complexitiesData = response.data.data.complexities;
      complexities.value = Object.fromEntries(
        Object.entries(complexitiesData).map(([key, value]) => {
          return [key, value.title];
        })
      );
    }
  } catch (error) {
    console.error("Search.vue: Error loading search data:", error);
  }
};

const loadTabData = async (tabIndex) => {
  isLoading.value = true;
  try {
    if (tabIndex === 0) {
      await recipeStore.searchRecipes({});
      recipes.value = recipeStore.recipes;
    } else if (tabIndex === 1) {
      await collectionStore.searchCollections({});
      collections.value = collectionStore.collections;
      console.log("Search.vue: Loaded collections:", collections.value);
    } else if (tabIndex === 2) {
      await userStore.searchUsers({});
      users.value = userStore.users;
    }
  } catch (error) {
    console.error("Search.vue: Error loading tab data:", error);
  } finally {
    isLoading.value = false;
  }
};

const performSearchFromQuery = async () => {
  isLoading.value = true;
  try {
    let query = {};
    if (activeTab.value === 0) {
      query = {
        title: recipeTitle.value || undefined,
        marks: selectedMarks.value.length
          ? selectedMarks.value.map((mark) => mark.id)
          : undefined,
        products: selectedProducts.value.length
          ? selectedProducts.value.map((product) => product.id)
          : undefined,
        complexity_id: selectedComplexity.value || undefined,
      };
      await recipeStore.searchRecipes(query);
      recipes.value = recipeStore.recipes;
    } else if (activeTab.value === 1) {
      query = {
        title: collectionTitle.value || undefined,
        marks: selectedMarks.value.length
          ? selectedMarks.value.map((mark) => mark.id)
          : undefined,
        products: selectedProducts.value.length
          ? selectedProducts.value.map((product) => product.id)
          : undefined,
      };
      await collectionStore.searchCollections(query);
      collections.value = collectionStore.collections;
      console.log("Search.vue: Search collections result:", collections.value);
    } else if (activeTab.value === 2) {
      query = {
        login: userLogin.value || undefined,
        name: userName.value || undefined,
        surname: userSurname.value || undefined,
      };
      await userStore.searchUsers(query);
      users.value = userStore.users;
    }
  } catch (error) {
    console.error("Search.vue: Error performing search from query:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(async () => {
  await loadInitialSearchData();
  await initializeFromQuery();
  if (Object.keys(route.query).length) {
    await performSearchFromQuery();
  } else {
    await loadTabData(activeTab.value);
  }
});

const resetCurrentFields = async () => {
  if (activeTab.value === 0) {
    recipeTitle.value = "";
    selectedMarks.value = [];
    selectedProducts.value = [];
    selectedComplexity.value = "";
  } else if (activeTab.value === 1) {
    collectionTitle.value = "";
    selectedMarks.value = [];
    selectedProducts.value = [];
  } else if (activeTab.value === 2) {
    userLogin.value = "";
    userName.value = "";
    userSurname.value = "";
  }
  await nextTick();
  const type = tabToType[activeTab.value];
  await router.replace({ name: "search", params: { type }, query: {} });
  await loadTabData(activeTab.value);
};

const updateActiveTab = async (index) => {
  isSwitching.value = true;
  activeTab.value = index;
  await resetCurrentFields();

  const type = tabToType[index];
  if (route.params.type !== type) {
    await nextTick();
    await router.replace({ name: "search", params: { type }, query: {} });
  }

  await initializeFromQuery();
  if (Object.keys(route.query).length) {
    await performSearchFromQuery();
  } else {
    await loadTabData(index);
  }
  isSwitching.value = false;
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
        marks: selectedMarks.value.length
          ? selectedMarks.value.map((mark) => mark.id)
          : undefined,
        products: selectedProducts.value.length
          ? selectedProducts.value.map((product) => product.id)
          : undefined,
        complexity_id: selectedComplexity.value || undefined,
      };
      await recipeStore.searchRecipes(query);
      recipes.value = recipeStore.recipes;
    } else if (activeTab.value === 1) {
      query = {
        title: collectionTitle.value || undefined,
        marks: selectedMarks.value.length
          ? selectedMarks.value.map((mark) => mark.id)
          : undefined,
        products: selectedProducts.value.length
          ? selectedProducts.value.map((product) => product.id)
          : undefined,
      };
      await collectionStore.searchCollections(query);
      collections.value = collectionStore.collections;
      console.log(
        "Search.vue: Submitted search collections:",
        collections.value
      );
    } else if (activeTab.value === 2) {
      query = {
        login: userLogin.value || undefined,
        name: userName.value || undefined,
        surname: userSurname.value || undefined,
      };
      await userStore.searchUsers(query);
      users.value = userStore.users;
    }

    await nextTick();
    await router.replace({ name: "search", params: { type }, query });
  } catch (error) {
    console.error("Search.vue: Search error:", error);
  } finally {
    isLoading.value = false;
  }
};

watch(
  () => route.params.type,
  async (newType) => {
    if (
      newType &&
      typeToTab[newType] !== undefined &&
      typeToTab[newType] !== activeTab.value &&
      !isSwitching.value
    ) {
      activeTab.value = typeToTab[newType];
      await initializeFromQuery();
      if (Object.keys(route.query).length) {
        await performSearchFromQuery();
      } else {
        await loadTabData(activeTab.value);
      }
    }
  },
  { immediate: true }
);

watch(
  () => route.query,
  async () => {
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
      <div class="tab-content">
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
              :options="complexities"
            />
          </div>
          <SelectMultiple
            v-model="selectedMarks"
            name="mark"
            :query="route.query"
          />
          <SelectMultiple
            v-model="selectedProducts"
            name="product"
            :query="route.query"
          />
        </div>
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
          <SelectMultiple
            v-model="selectedMarks"
            name="mark"
            :query="route.query"
          />
          <SelectMultiple
            v-model="selectedProducts"
            name="product"
            :query="route.query"
          />
        </div>
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
      <div class="btn-group end">
        <button class="btn-dark" type="submit">Поиск</button>
        <button class="btn-dark line" type="button" @click="resetCurrentFields">
          Сброс
        </button>
      </div>
    </form>
    <section class="content-grid">
      <div v-if="isLoading" v-for="n in 6" :key="n" class="skeleton-card"></div>
      <template v-else>
        <Recipe
          v-if="activeTab === 0"
          v-for="recipe in recipes"
          :key="recipe.id"
          :recipe="recipe"
        />
        <Collection
          v-else-if="activeTab === 1"
          v-for="collection in collections"
          :key="collection.id"
          :collection="collection"
          @vue:mounted="
            () => console.log('Search.vue: Collection mounted:', collection)
          "
        />
        <User
          v-else-if="activeTab === 2"
          v-for="user in users"
          :key="user.id"
          :user="user"
          @vue:mounted="
            () =>
              console.log('Search.vue: User card mounted:', {
                id: user.id,
                login: user.login,
                status: user.status,
              })
          "
        />
        <p v-if="recipes.length === 0 && activeTab === 0" class="no-results">
          {{ isQueryEmpty ? "Результаты отсутствуют" : "Рецепты не найдены" }}
        </p>
        <p
          v-if="collections.length === 0 && activeTab === 1"
          class="no-results"
        >
          {{ isQueryEmpty ? "Результаты отсутствуют" : "Коллекции не найдены" }}
        </p>
        <p v-if="users.length === 0 && activeTab === 2" class="no-results">
          {{
            isQueryEmpty ? "Результаты отсутствуют" : "Пользователи не найдены"
          }}
        </p>
      </template>
    </section>
  </div>
</template>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;
@use "../assets/styles/form" as *;

.search-wrapper {
  display: flex;
  flex-direction: column;
  gap: 3.125rem; // 50px
  width: 100%;
}

.tab-content {
  width: 100%;
}

.tab-pane {
  display: none;

  &.active {
    display: flex;
    flex-direction: column;
    gap: 1.875rem; // 30px
  }
}

.no-results {
  grid-column: 1 / -1;
  text-align: center;
  color: $text-info;
  font-size: 1.25rem; // 20px
  font-family: Rubik, sans-serif;
  padding: 1.25rem; // 20px
}

// Адаптивность
@media (max-width: 1200px) {
  .search-wrapper {
    gap: 2.5rem; // 40px
  }

  .tab-pane.active {
    gap: 1.25rem; // 20px
  }

  .no-results {
    font-size: 1.125rem; // 18px
    padding: 1rem; // 16px
  }
}

@media (max-width: 768px) {
  .search-wrapper {
    gap: 1.875rem; // 30px
  }

  .tab-pane.active {
    gap: 1rem; // 16px
  }

  .no-results {
    font-size: 1rem; // 16px
    padding: 0.75rem; // 12px
  }
}

@media (max-width: 480px) {
  .search-wrapper {
    gap: 1.25rem; // 20px
  }

  .tab-pane.active {
    gap: 0.75rem; // 12px
  }

  .no-results {
    font-size: 0.875rem; // 14px
    padding: 0.5rem; // 8px
  }
}
</style>
