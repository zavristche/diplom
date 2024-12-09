<script setup>
import BaseIcon from "./BaseIcon.vue";
</script>

<template>
  <footer>
    <div class="container">
      <div class="container-row">
        <nav>
          <a href="/recept/all">
            <div class="label-item">Заготовки</div>
          </a>
          <a href="/recept/all">
            <div class="label-item">Супы</div>
          </a>
          <a href="/recept/all">
            <div class="label-item">Десерты</div>
          </a>
        </nav>
        <a href="/recept/all"><BaseIcon viewBox="0 0 35 35" class="icon-dark-45-0" name="vk"/></a>
        <a href="/">
          <BaseIcon class="logo"  name="logo"/>
        </a>
        <a href="/recept/all"><BaseIcon viewBox="0 0 35 35" class="icon-dark-45-0" name="tg"/></a>
        <nav>
          <a href="/recept/all">
            <div class="label-item">Гарниры</div>
          </a>
          <a href="/recept/all">
            <div class="label-item">Закуски</div>
          </a>
          <a href="/recept/all">
            <div class="label-item">Выпечка</div>
          </a>
        </nav>
      </div>
      <span class="slogan">Готовь просто – удивляй вкусно!</span>
      <span class="slogan">© 2024 Заврище. Все права защищены.</span>
    </div>
  </footer>
</template>

<style lang="scss">
  footer {
    display: flex;
    justify-content: center;
    width: 100%;
    height: 11rem;
    font-weight: 500;

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

      .slogan{
        font-size: inherit;
      }

      .container-row{
        gap: 0.3rem;
      }
    }
  }
</style>