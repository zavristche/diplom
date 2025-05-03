<template>
    <label :for="name" class="label">
      {{ label }}
      <input
        :type="type"
        :name="name"
        class="input-form"
        :class="{ invalid: isInvalid }"
        :placeholder="placeholder"
        :value="modelValue"
        :disabled="disabled"
        @input="handleInput"
      />
      <transition name="fade-slide">
        <span v-if="isInvalid && errorMessage" class="error-message">
          {{ formatErrorMessage(errorMessage) }}
        </span>
      </transition>
    </label>
  </template>
  
  <script setup>
  import { defineProps, defineEmits, watch } from "vue";
  
  const props = defineProps({
    label: {
      type: String,
      default: "",
    },
    name: String,
    type: {
      type: String,
      default: "number",
    },
    placeholder: String,
    isInvalid: {
      type: Boolean,
      default: false,
    },
    errorMessage: {
      type: String,
      default: "",
    },
    modelValue: {
      type: [Number, null],
      default: null,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  });
  
  const emit = defineEmits(["update:modelValue"]);
  
  const handleInput = (event) => {
    const value = event.target.value === "" ? null : Number(event.target.value);
    console.log(`IngredientCount ${props.name} updated: ${value}`);
    emit("update:modelValue", value);
  };
  
  const formatErrorMessage = (message) => {
    if (Array.isArray(message)) {
      return message.join(", ");
    }
    return message;
  };
  
  watch(
    () => props.modelValue,
    (newValue) => {
      console.log(`IngredientCount ${props.name} modelValue changed: ${newValue}`);
    }
  );
  </script>
  
  <style lang="scss" scoped>
  @use "../assets/styles/variables" as *;
  
  .label {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }
  
  .input-form {
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
  
    &:disabled {
      background-color: $background;
      cursor: not-allowed;
    }
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