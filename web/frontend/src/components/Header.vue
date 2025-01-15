<script setup>
import BaseIcon from "./BaseIcon.vue";
</script>

<template>
  <header>
    <div class="container">
      <a href="/">
        <BaseIcon class="logo"  name="logo"/>
      </a>
      <nav class="labels left">
        <a href="/search/recipe">
          <div class="label-item">Рецепты</div>
        </a>
        <a href="/search/collection">
          <div class="label-item">Коллекции</div>
        </a>
        <a href="/search/author">
          <div class="label-item">Авторы</div>
        </a>
      </nav>
      <div class="search">
        <div class="btn icon">
          <BaseIcon class="icon-dark-45-1" viewBox="0 0 45 45" name="search"/>
        </div>
        <input
          type="text"
          id="search"
          placeholder="Поиск по рецептам, подборкам и авторам"
        />
        <div class="btn-container">
          <button type="submit" id="btn-search" class="btn-salat">Найти</button>
        </div>
      </div>
      <nav class="labels left">
        <a href="/register">
          <div class="label-item">Регистрация</div>
        </a>
        <a href="/login">
          <div class="label-item">Вход</div>
        </a>
      </nav>
    </div>
  </header>
</template>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;

.logo {
  width: 57px;
  height: 64px;
}

header {
  position: sticky;
  z-index: 20;
  top: 0;
  bottom: 50px;
  display: flex;
  justify-content: center;
  background-color: $background-dark;
  color: $light-text;
  box-shadow: $shadow;
  font-weight: 500;
  width: 100%;
  height: 6.25rem;

  .container {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    width: 75rem;
    height: 100%;

    max-width: 75rem;
    min-width: 28.75rem;


  }
}


</style>
