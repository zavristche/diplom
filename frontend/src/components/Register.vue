<script setup>
import { ref } from "vue";
import BaseIcon from "./BaseIcon.vue";
import Input from "./Input.vue";
import userService from "../api/UserService";

defineProps(["isOpen"]);
const emit = defineEmits(["close"]);

const form = ref({
  name: "",
  surname: "",
  login: "",
  email: "",
  password: "",
  password_repeat: "",
});
const errors = ref({});
const serverError = ref("");
const isLoading = ref(false);

const closeModal = () => {
  resetForm();
  emit("close");
};

const resetForm = () => {
  form.value = { name: "", surname: "", login: "", email: "", password: "", password_repeat: "" };
  errors.value = {};
  serverError.value = "";
};

const submitForm = async () => {
  isLoading.value = true;
  errors.value = {};
  serverError.value = "";

  try {
    const response = await userService.register(form.value);
    if (response.data.success) {
      console.log("Регистрация успешна:", response.data.user);
      closeModal();
    } else {
      Object.assign(errors.value, response.data.errors);
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.assign(errors.value, error.response.data.errors);
    } else {
      serverError.value = "Ошибка сервера. Попробуйте позже.";
    }
    console.error("Ошибка при регистрации:", error);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <transition name="fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal-container">
        <div class="modal-header">
          <button class="btn-icon" @click="closeModal">
            <BaseIcon viewBox="0 0 65 65" class="icon-dark-65-2" name="close" />
          </button>
        </div>
        <div class="content">
          <div class="logo">
            <BaseIcon class="logo" name="logo" />
          </div>
          <h1 class="title">Добро пожаловать в <br />Рецептище</h1>
          <form @submit.prevent="submitForm" class="form">
            <Input
              label="Имя"
              name="name"
              placeholder="Введите имя"
              v-model="form.name"
              :is-invalid="!!errors.name"
              :error-message="errors.name"
            />
            <Input
              label="Фамилия"
              name="surname"
              placeholder="Введите фамилию"
              v-model="form.surname"
              :is-invalid="!!errors.surname"
              :error-message="errors.surname"
            />
            <Input
              label="Логин"
              name="login"
              placeholder="Введите логин"
              v-model="form.login"
              :is-invalid="!!errors.login"
              :error-message="errors.login"
            />
            <Input
              label="Email"
              name="email"
              placeholder="Введите email"
              v-model="form.email"
              :is-invalid="!!errors.email"
              :error-message="errors.email"
            />
            <Input
              label="Пароль"
              name="password"
              type="password"
              placeholder="Введите пароль"
              v-model="form.password"
              :is-invalid="!!errors.password"
              :error-message="errors.password"
            />
            <Input
              label="Повтор пароля"
              name="password_repeat"
              type="password"
              placeholder="Введите повтор пароля"
              v-model="form.password_repeat"
              :is-invalid="!!errors.password_repeat"
              :error-message="errors.password_repeat"
            />
            <input
              class="btn-dark"
              type="submit"
              :value="isLoading ? 'Регистрация...' : 'Зарегистрироваться'"
              :disabled="isLoading"
            />
          </form>
          <div v-if="serverError" class="error-message">{{ serverError }}</div>
        </div>
      </div>
    </div>
  </transition>
</template>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;
@use "../assets/styles/modal" as *;


</style>