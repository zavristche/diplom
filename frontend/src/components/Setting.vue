<script setup>
import { ref, defineProps, defineEmits, nextTick } from "vue";
import BaseIcon from "./BaseIcon.vue";
import Input from "./Input.vue";
import SettingService from "../api/SettingService";

const props = defineProps(["isOpen", "profile"]);
const emit = defineEmits(["close"]);

const initialData = {
  name: props.profile?.name || "",
  surname: props.profile?.surname || "",
  login: props.profile?.login || "",
  email: props.profile?.email || "",
  password: "",
  avatar: props.profile?.avatar || "",
  photo_header: props.profile?.photo_header || "",
};

const userData = ref({ ...initialData });
const avatarInput = ref(null);
const backgroundInput = ref(null);
const avatarPreview = ref(userData.value.avatar);
const backgroundPreview = ref(userData.value.photo_header);

const uploadFile = async (inputType) => {
  await nextTick(); // Ждем, пока DOM обновится
  let input;
  if (inputType === "avatar") {
    input = avatarInput.value || document.querySelector('input[name="avatar"]');
  } else if (inputType === "photo_header") {
    input = backgroundInput.value || document.querySelector('input[name="photo_header"]');
  }
  if (input && typeof input.click === "function") {
    input.click();
  } else {
    console.error(`Input for ${inputType} is not ready or invalid:`, input);
  }
};

const handleFileChange = (event, field) => {
  const file = event.target.files[0];
  if (file) {
    const previewUrl = URL.createObjectURL(file);
    if (field === "avatar") avatarPreview.value = previewUrl;
    else if (field === "photo_header") backgroundPreview.value = previewUrl;
  }
};

const submitForm = async () => {
  try {
    const formData = new FormData();
    formData.append("name", userData.value.name);
    formData.append("surname", userData.value.surname);
    formData.append("login", userData.value.login);
    formData.append("email", userData.value.email);
    if (userData.value.password) formData.append("password", userData.value.password);
    if (avatarInput.value?.files[0]) formData.append("avatar", avatarInput.value.files[0]);
    if (backgroundInput.value?.files[0]) formData.append("photo_header", backgroundInput.value.files[0]);

    console.log("Sending FormData:");
    for (let [key, value] of formData.entries()) {
      console.log(`${key}: ${value instanceof File ? value.name : value}`);
    }

    const response = await SettingService.update(props.profile.id, formData);
    console.log("Server response:", response.data); // Логируем ответ сервера

    closeModal();
    window.location.reload();
  } catch (error) {
    console.error("Ошибка при сохранении настроек:", error.response?.data || error.message);
  }
};

const closeModal = () => {
  if (avatarPreview.value?.startsWith("blob:")) URL.revokeObjectURL(avatarPreview.value);
  if (backgroundPreview.value?.startsWith("blob:")) URL.revokeObjectURL(backgroundPreview.value);
  userData.value = { ...initialData };
  avatarPreview.value = initialData.avatar;
  backgroundPreview.value = initialData.photo_header;
  [avatarInput, backgroundInput].forEach((input) => input.value && (input.value.value = ""));
  emit("close");
};
</script>

<template>
  <div v-if="isOpen" class="modal-overlay setting">
    <div class="modal-container">
      <div class="modal-header">
        <h1 class="title">Настройки</h1>
        <button class="btn-icon" @click="closeModal">
          <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-2" name="close" />
        </button>
      </div>
      <div class="content">
        <form class="form" @submit.prevent="submitForm">
          <div class="setting-photo">
            <div
              class="setting-photo__background"
              :style="{ backgroundImage: backgroundPreview ? `url(${backgroundPreview})` : 'none' }"
            ></div>
            <div
              class="setting-photo__avatar"
              :style="{ backgroundImage: avatarPreview ? `url(${avatarPreview})` : 'none' }"
            ></div>
            <div class="setting-photo__actions">
              <button type="button" class="action-button" @click="uploadFile('avatar')">
                Загрузить аватар
              </button>
              <input
                ref="avatarInput"
                type="file"
                name="avatar"
                accept="image/*"
                @change="handleFileChange($event, 'avatar')"
                hidden
              />
              <button type="button" class="action-button" @click="uploadFile('photo_header')">
                Загрузить фон
              </button>
              <input
                ref="backgroundInput"
                type="file"
                name="photo_header"
                accept="image/*"
                @change="handleFileChange($event, 'photo_header')"
                hidden
              />
            </div>
          </div>
          <Input label="Имя" name="name" placeholder="Введите имя" v-model="userData.name" />
          <Input label="Фамилия" name="surname" placeholder="Введите фамилию" v-model="userData.surname" />
          <Input label="Логин" name="login" placeholder="Введите логин" v-model="userData.login" />
          <Input label="Email" name="email" placeholder="Введите email" v-model="userData.email" />
          <Input
            label="Новый пароль"
            name="password"
            type="password"
            placeholder="Введите новый пароль"
            autocomplete="current-password"
            v-model="userData.password"
          />
          <input class="btn-dark" type="submit" value="Сохранить" />
        </form>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;

.setting-photo {
  width: 100%;
  height: 180px;
  position: relative;

  &__background {
    width: 100%;
    height: 125px;
    position: absolute;
    top: 0;
    left: 0;
    border-radius: $border;
    background-color: $light;
    background-size: cover;
    background-position: center;
  }

  &__avatar {
    width: 140px;
    height: 140px;
    position: absolute;
    top: 20px;
    left: 0;
    background-color: $light;
    border-radius: 50%;
    outline: 3px solid $background;
    outline-offset: -1.5px;
    background-size: cover;
    background-position: center;
  }

  &__actions {
    position: absolute;
    left: 187px;
    top: 141px;
    display: flex;
    gap: 20px;
  }
}

.action-button {
  color: $text-info-light;
  font: map-get(map-get($font-weather, "medium"), "weight")
    map-get(map-get($font-weather, "medium"), "size") "Rubik",
    sans-serif;
  background: none;
  border: none;
  cursor: pointer;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  overflow-y: auto;
  padding: 20px;

  &.setting .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
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
  margin: auto;

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
  font: 600 32px sans-serif;
}

.form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
</style>