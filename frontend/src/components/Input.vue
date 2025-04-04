<template>
  <label :for="name" class="label small">
    {{ label }}
    <input
      :type="type"
      :name="name"
      class="input-form"
      :class="{ 'invalid': isInvalid, 'valid': isValid && !isInvalid }"
      :placeholder="placeholder"
      :value="modelValue"
      :autocomplete="autocomplete"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <span v-if="isInvalid && errorMessage" class="invalid-feedback">{{ errorMessage }}</span>
  </label>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";

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
    default: false,
  },
  isValid: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: "",
  },
  autocomplete: {
    type: String,
    default: "off",
  },
  modelValue: {
    type: [String, Number, null],
    default: null,
  },
});

defineEmits(["update:modelValue"]);
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
  display: block;
}
</style>