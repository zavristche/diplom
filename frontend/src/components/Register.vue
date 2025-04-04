<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <div class="modal-header">
        <button class="btn-icon" @click="closeModal">
          <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-2" name="close" />
        </button>
      </div>
      <div class="content">
        <div class="logo">
          <BaseIcon class="logo" name="logo" />
        </div>
        <h1 class="title">Добро пожаловать в <br />Рецептище</h1>
        <form action="" method="POST" class="form">
          <Input
            label="Имя"
            name="name"
            placeholder="Введите имя"
            :is-valid="true"
          />
          <Input label="Фамилия" name="surname" placeholder="Введите фамилию" />
          <Input label="Логин" name="login" placeholder="Введите логин" />
          <Input label="Email" name="email" placeholder="Введите email" />
          <Input
            label="Пароль"
            name="password"
            type="password"
            placeholder="Введите пароль"
          />
          <Input
            label="Повтор пароля"
            name="password_repeat"
            type="password"
            placeholder="Введите повтор пароля"
          />
        </form>
        <input class="btn-dark" type="submit" value="Зарегистрироваться" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from "vue";
import BaseIcon from "./BaseIcon.vue";
import Input from "./Input.vue";

defineProps(["isOpen"]);
const emit = defineEmits(["close"]);

const closeModal = () => {
  emit("close");
};
</script>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  overflow-y: auto; // добавляем скролл при необходимости
  padding: 20px; // небольшой отступ, чтобы окно не прилипало к краям

  .btn-dark {
    width: 100%;
  }
}

.modal-container {
  width: 455px;
  padding: 30px;
  background: $background;
  box-shadow: $shadow;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  margin: auto; // центрирование даже при наличии скролла
}

.modal-header {
  display: flex;
  justify-content: flex-end;
}

.content {
  width: 395px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 40px;
}

.title {
  text-align: center;
  font-size: 32px;
  font-weight: 600;
}

.form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
</style>
