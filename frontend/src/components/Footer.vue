<script setup>
import BaseIcon from "./BaseIcon.vue";
</script>

<template>
  <footer>
    <div class="container">
      <div class="container-row">
        <BaseIcon viewBox="0 0 35 35" class="icon-dark-45-0" name="vk" />
        <a to="/">
          <BaseIcon class="logo" name="logo" />
        </a>
        <BaseIcon viewBox="0 0 35 35" class="icon-dark-45-0" name="tg" />
      </div>
      <div class="container-row">
        <nav>
          <router-link to="/search/recipe?marks=42">
            <div class="label-item">Заготовки</div>
          </router-link>
          <router-link to="/search/recipe?marks=1">
            <div class="label-item">Супы</div>
          </router-link>
          <router-link to="/search/recipe?marks=9">
            <div class="label-item">Десерты</div>
          </router-link>
          <router-link to="/search/recipe?marks=4">
            <div class="label-item">Гарниры</div>
          </router-link>
          <router-link to="/search/recipe?marks=5">
            <div class="label-item">Закуски</div>
          </router-link>
          <router-link to="/search/recipe?marks=7">
            <div class="label-item">Выпечка</div>
          </router-link>
        </nav>
      </div>
      <div class="container-col">
        <span class="slogan">Готовь просто – удивляй вкусно!</span>
        <span class="slogan">© 2024 Заврище. Все права защищены.</span>
      </div>
    </div>
  </footer>
</template>

<style lang="scss">
footer {
  display: flex;
  justify-content: center;
  width: 100%;
  height: 11rem;
  margin-top: 3rem;

  .logo {
    width: 57px;
    height: 64px;
  }

  nav {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    height: auto;
    justify-content: center;
    gap: 0.2rem;
    font-weight: 500;
  }

  .container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 0.3rem;

    width: 75rem;
    height: 100%;

    max-width: 75rem;
    min-width: 28.75rem;

    .slogan {
      font-size: inherit;
    }

    .container-row {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 0.5rem;
    }

    .container-col {
      align-items: center;
      height: auto;
    }
  }
}
</style>
