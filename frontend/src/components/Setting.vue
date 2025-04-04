<script setup>
import { ref, defineProps, defineEmits } from "vue";
import BaseIcon from "./BaseIcon.vue";
import Input from "./Input.vue";

defineProps(["isOpen", "profile"]); // Добавляем profile в пропсы
const emit = defineEmits(["close"]);

const closeModal = () => {
  emit("close");
};
</script>

<template>
  <div v-if="isOpen" class="modal-overlay setting"> <!-- Убрано @click.self -->
    <div class="modal-container">
      <div class="modal-header">
        <h1 class="title">Настройки</h1>
        <button class="btn-icon" @click="closeModal">
          <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-2" name="close" />
        </button>
      </div>
      <div class="content">
        <form action="" method="POST" class="form">
          <Input
            label="Имя"
            name="name"
            type="text"
            placeholder="Введите имя"
            :value="profile.name"
          />
          <Input
            label="Фамилия"
            name="surname"
            type="text"
            placeholder="Введите фамилию"
            :value="profile.surname"
          />
          <Input
            label="Логин"
            name="login"
            type="text"
            placeholder="Введите логин"
            :value="profile.login"
          />
          <Input
            label="Email"
            name="email"
            type="text"
            placeholder="Введите email"
            :value="profile.email"
          />
          <Input
            label="Новый пароль"
            name="password"
            type="password"
            placeholder="Введите новый пароль"
            autocomplete="current-password"
          />
          <input class="btn-dark" type="submit" value="Войти" />
        </form>
      </div>
    </div>
  </div>
</template>

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

  &.setting {
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 0 20px 0;
    }
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

  .btn-dark {
    width: 100%;
  }
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
