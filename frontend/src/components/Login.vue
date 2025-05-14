<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import BaseIcon from "./BaseIcon.vue";
import Input from "./Input.vue";
import userService from "../api/UserService";

defineProps(["isOpen"]);
const emit = defineEmits(["close"]);
const router = useRouter();
const authStore = useAuthStore();

const form = ref({ login: "", password: "" });
const errors = ref({});
const serverError = ref("");
const isLoading = ref(false);

const closeModal = () => {
  resetForm();
  emit("close");
};

const resetForm = () => {
  form.value = { login: "", password: "" };
  errors.value = {};
  serverError.value = "";
};

const submitForm = async () => {
  isLoading.value = true;
  errors.value = {};
  serverError.value = "";

  try {
    const response = await userService.login(form.value);
    if (response.data.success) {
      authStore.setUser(response.data.auth_key, response.data.user);
      if (authStore.isAdmin) {
        router.push(`/admin`);
      } else {
        router.push(`/profile/${response.data.user.id}`);
      }
      closeModal();
    } else {
      errors.value = response.data.errors;
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      serverError.value = "Ошибка сервера. Попробуйте позже.";
    }
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
          <div class="logo-container">
            <BaseIcon class="logo" name="logo" />
          </div>
          <h1 class="title">Добро пожаловать в <br />Рецептище</h1>
          <form @submit.prevent="submitForm" class="form">
            <Input
              label="Логин"
              name="login"
              placeholder="Введите логин"
              v-model="form.login"
              :is-invalid="!!errors.login"
              :error-message="errors.login"
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
            <input
              class="btn-dark"
              type="submit"
              :value="isLoading ? 'Вход...' : 'Войти'"
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
