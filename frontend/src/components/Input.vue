<template>
    <label :for="name" class="label small">
      {{ label }}
      <input
        :type="type"
        :name="name"
        class="input-form"
        :placeholder="placeholder"
      />
      <span v-if="isInvalid" class="invalid-feedback">Text</span>
    </label>
  </template>
  
  <script setup>
  import { defineProps } from "vue";
  
  defineProps({
    label: String,
    name: String,
    type: {
      type: String,
      default: "text",
    },
    placeholder: String,
    isInvalid: {
      type: Boolean,
      default: false, // По умолчанию скрыто
    },
  });
  </script>
  
  <style lang="scss" scoped>
  @use "../assets/styles/variables" as *;
  
  .input-form {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    border-radius: $border;
    font-size: 20px;
    font-weight: 400;
    border: 1px solid $text-info-light;
    width: 100%;
  
    &::placeholder {
      font-weight: 300;
    }
  
    &.invalid {
      border: 2px solid $error;
    }
  
    &.valid {
      border: 2px solid $accent-color-1;
    }
  }
  
  .invalid-feedback {
    font-size: 16px;
    color: $error;
    display: none; // По умолчанию скрыто
  }
  </style>
  