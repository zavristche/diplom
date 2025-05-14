<script setup>
import { useRouter } from "vue-router";

const router = useRouter();

// Функция для перехода на главную страницу
const goToHome = () => {
  router.push("/");
};
</script>

<template>
  <div class="hero column">
    <img
      src="../assets/img/NotFound.svg"
      alt="Not Found"
      class="not-found-svg"
    />
    <h1>Страница не найдена</h1>
    <button class="btn-dark" @click="goToHome">Вернуться на главную</button>
  </div>
</template>

<style scoped lang="scss">
@use "../assets/styles/_variables.scss" as *;

.hero {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
  width: 100%;

  &.column {
    flex-direction: column;

    h1 {
    font-weight: 600;
    font-size: 40px;
    margin: 0;
    text-align: left;
    white-space: nowrap; // Текст в одну строку
    overflow: hidden; // Обрезаем, если не помещается
    text-overflow: ellipsis; // Добавляем многоточие при обрезке
  }
  }
}

.not-found-svg {
  width: auto;
  height: 500px; // Ограничиваем высоту SVG
  object-fit: contain;
}

.btn-salat {
  padding: 10px 20px;
  background-color: $accent-color-1;
  color: $light-text;
  border: none;
  border-radius: $border;
  font-size: map-get(map-get($font-weather, "medium"), "size");
  font-weight: map-get(map-get($font-weather, "medium"), "weight");
  cursor: pointer;
  transition: background-color 0.3s;

  &:hover {
    background-color: darken($accent-color-1, 10%);
  }
}
</style>
