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
        <form @submit.prevent="submitForm" class="form">
          <Input
            label="Логин"
            name="login"
            placeholder="Введите логин"
            v-model="form.login"
            :is-invalid="hasResponse && !!errors.login"
            :is-valid="hasResponse && form.login && !errors.login"
            :error-message="errors.login"
          />
          <Input
            label="Пароль"
            name="password"
            type="password"
            placeholder="Введите пароль"
            v-model="form.password"
            :is-invalid="hasResponse && !!errors.password"
            :is-valid="hasResponse && form.password && !errors.password"
            :error-message="errors.password"
          />
          <input
            class="btn-dark"
            type="submit"
            value="Войти"
            :disabled="isLoading"
          />
        </form>
        <div v-if="serverError" class="error-message">{{ serverError }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import BaseIcon from "./BaseIcon.vue";
import Input from "./Input.vue";
import userService from "../api/UserService";
import { defineProps, defineEmits } from "vue";

defineProps(["isOpen"]);
const emit = defineEmits(["close"]);

const form = ref({
  login: "",
  password: "",
});

const errors = ref({});
const serverError = ref("");
const isLoading = ref(false);
const hasResponse = ref(false);

const closeModal = () => {
  resetForm();
  emit("close");
};

const resetForm = () => {
  form.value = {
    login: "",
    password: "",
  };
  errors.value = {};
  serverError.value = "";
  hasResponse.value = false;
};

const submitForm = async () => {
  isLoading.value = true;
  hasResponse.value = false; // Сбрасываем перед запросом
  errors.value = {};
  serverError.value = "";

  try {
    const response = await userService.login(form.value);
    hasResponse.value = true; // Устанавливаем после ответа
    console.log("Ответ сервера:", response.data);

    if (response.data.success) {
      console.log("Авторизация успешна:", response.data);
      closeModal();
    } else {
      Object.keys(response.data.errors).forEach((key) => {
        errors.value[key] = response.data.errors[key][0];
      });
      console.log("Ошибки авторизации:", errors.value);
    }
  } catch (error) {
    hasResponse.value = true; // Устанавливаем даже при ошибке
    if (error.response && error.response.data && error.response.data.errors) {
      Object.keys(error.response.data.errors).forEach((key) => {
        errors.value[key] = error.response.data.errors[key][0];
      });
    } else {
      serverError.value = "Ошибка сервера. Попробуйте позже.";
    }
    console.error("Ошибка при авторизации:", error);
  } finally {
    isLoading.value = false;
  }
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
  overflow-y: auto;
  padding: 20px;
}

.modal-container {
  width: 455px;
  padding: 30px;
  background: $background;
  box-shadow: $shadow;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  margin: auto;
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

.btn-dark {
  width: 100%;
  padding: 11px 18px;
  background: $background-dark;
  border-radius: $border;
  color: $light-text;
  font-size: 18px;
  font-family: Rubik;
  font-weight: map-get($font-weather, "small", "weight");
  border: none;
  cursor: pointer;
}

.error-message {
  color: $error;
  font-size: 14px;
  text-align: center;
}
</style>