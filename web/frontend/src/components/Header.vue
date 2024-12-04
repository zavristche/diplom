<script setup>
// import BaseIcon from "./BaseIcon.vue";
</script>

<template>
  <header>
    <div class="container">
      <a href="/">
        <use xlink:href="../assets/sprite.svg#logo"></use>
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
        <input
          type="text"
          id="search"
          placeholder="Поиск по рецептам, подборкам и авторам"
        />
        <a href="/search"><button type="button" class="btn">✏️</button></a>
        <button type="submit" id="btn-search" class="btn light">Найти</button>
      </div>
    </div>
  </header>
</template>

<style lang="scss">
@use "../assets/styles/_variables.scss" as *;

header {
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

    nav {
      display: flex;
      flex-direction: row;

      .label-item {
        display: flex;
        align-items: center;
        height: 100%;
        padding: 0 1rem;
      }
    }
  }
}

.search {
  display: flex;
  flex-direction: row;
  align-items: center;

  background-color: $background;
  height: 2.8rem;
  border-radius: 0.5rem;
  ::placeholder {
    font-weight: 300;
    color: $background-dark;
  }

  .btn {
    height: 100%;
    border-radius: 0 0.5rem 0.5rem 0;
    font-weight: 400;
  }
}
</style>
