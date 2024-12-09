<script setup>
import BaseIcon from "../components/BaseIcon.vue";
</script>
<template>
  <section class="hero">
    <div class="container-col">
      <h1>РЕЦЕПТИЩЕ</h1>
      <p class="slogan">Готовь просто – удивляй вкусно! </p>
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
      <button type="submit" id="" class="btn-dark line"><BaseIcon viewBox="0 0 45 45" class="icon-dark-45-1" name="random" />Случайный рецепт</button>
    </div>
    <div class="mascot">
      <img src="/img/mascot.png" alt="Test Image">
    </div>
  </section>
</template>

<style lang="scss">
@use "../assets/styles/variables" as *;

.hero {
  display: flex;
  flex-direction: row;
  align-items: center;
  width: 100%;
  // height: 36rem;
  align-content: center;
  // background-color: $accent-color-2;

  h1 {
    font-weight: 600;
    font-size: 6.25rem;
  }

  .search {
    box-shadow: $shadow;
  }

  .container-col {
    justify-content: space-between;
    height: 19rem;
  }
}

.slogan {
  font-weight: 300;
  font-size: 2rem;
}

.mascot {
  display: flex;
  width: 100%;
  height: 38rem;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}
</style>