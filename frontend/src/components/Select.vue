<template>
    <label :for="name" class="label">
      {{ label }}
      <select
        :name="name"
        class="input-form custom-select"
        :class="{ invalid: isInvalid }"
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
      >
        <option :value="defaultValue" disabled selected>{{ placeholder }}</option>
        <option v-for="(option, key) in options" :key="key" :value="key">
          {{ option.title || option }}
        </option>
      </select>
      <BaseIcon
        viewBox="0 0 29 16"
        class="icon-dark-25-2 select-arrow"
        name="arrow"
      />
      <transition name="fade-slide">
        <span v-if="isInvalid && errorMessage" class="error-message">
          {{ formatErrorMessage(errorMessage) }}
        </span>
      </transition>
    </label>
  </template>
  
  <script setup>
  import { defineProps, defineEmits } from "vue";
  import BaseIcon from "../components/BaseIcon.vue"; // Укажи правильный путь
  
  defineProps({
    label: String,
    name: String,
    placeholder: String,
    options: Object,
    isInvalid: {
      type: Boolean,
      default: false,
    },
    errorMessage: {
      type: String,
      default: "",
    },
    modelValue: {
      type: [String, Number, null],
      default: null,
    },
    defaultValue: {
      type: [String, Number, null],
      default: "",
    },
  });
  
  defineEmits(["update:modelValue"]);
  
  const formatErrorMessage = (message) => {
    if (Array.isArray(message)) {
      return message.join(", ");
    }
    return message;
  };
  </script>
  
  <style lang="scss" scoped>
  @use "../assets/styles/variables" as *;
  
  .label {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    position: relative;
  }
  
  .input-form {
    padding: 15px 20px;
    border-radius: $border;
    font-size: 20px;
    font-weight: 400;
    border: 1px solid $text-info-light;
    width: 100%;
  
    &.invalid {
      border: 2px solid $error;
    }
  }
  
  .custom-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding-right: 40px;
  }
  
  .select-arrow {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
  }
  
  .error-message {
    font-size: 16px;
    color: $error;
    margin-top: 4px;
  }
  
  .fade-slide-enter-active,
  .fade-slide-leave-active {
    transition: all 0.3s ease;
  }
  .fade-slide-enter-from,
  .fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-4px);
  }
  .fade-slide-enter-to,
  .fade-slide-leave-from {
    opacity: 1;
    transform: translateY(0);
  }
  </style>