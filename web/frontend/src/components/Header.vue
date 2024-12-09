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
        <a href="/recept/all">
          <div class="label-item">Рецепты</div>
        </a>
        <a href="/collection/all">
          <div class="label-item">Коллекции</div>
        </a>
        <a href="/author/all">
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
          <button type="button"><BaseIcon viewBox="0 0 45 45" class="icon-dark-45-1" name="filter"/></button>
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

.btn-container {
  display: flex;
  flex-direction: row;
  align-items: center;
  height: 100%;
}

header {
  position: sticky;
  top: 0;
  bottom: 50px;
  display: flex;
  justify-content: center;
  background-color: $background-dark;
  color: $light-text;
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
