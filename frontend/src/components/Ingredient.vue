<script setup>
import { defineProps, defineEmits, onMounted } from 'vue';
import BaseIcon from './BaseIcon.vue';

const props = defineProps({
  ingredient: {
    type: Object,
    required: true,
  },
  index: {
    type: Number,
    required: true,
  },
  data: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  isEditable: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['remove']);

const formatErrorMessage = (message) => {
  if (Array.isArray(message)) {
    return message.join(', ');
  }
  return message;
};

// Отладка входных данных
onMounted(() => {
  console.log('Ingredient props:', {
    ingredient: props.ingredient,
    index: props.index,
    dataProducts: props.data.products,
    dataMeasures: props.data.measures,
    errors: props.errors,
  });
});
</script>

<template>
  <label class="ingredient" :for="'products[' + index + ']'">
    <button v-if="isEditable" type="button" @click="emit('remove', index)">
      <BaseIcon viewBox="0 0 65 65" class="icon-dark-45-2" name="minus" />
    </button>
    <div class="ingredient__container" :class="{ invalid: errors[`products[${index}]`] }">
      <div class="ingredient__fields">
        <div class="select-wrapper product-select">
          <select
            v-model="ingredient.product_id"
            :name="'products[' + index + '][product_id]'"
            :disabled="!isEditable"
            class="custom-select"
          >
            <option :value="null" disabled>Выберите продукт</option>
            <option v-for="(option, key) in data.products" :key="key" :value="key">
              {{ option.title || option }}
            </option>
          </select>
          <BaseIcon
            viewBox="0 0 29 16"
            class="icon-dark-25-2 select-arrow"
            name="arrow"
          />
        </div>
        <input
          v-model.number="ingredient.count"
          :name="'products[' + index + '][count]'"
          type="number"
          placeholder="N"
          :disabled="!isEditable"
          class="count-input"
        />
        <div class="select-wrapper measure-select">
          <select
            v-model="ingredient.measure_id"
            :name="'products[' + index + '][measure_id]'"
            :disabled="!isEditable"
            class="custom-select"
          >
            <option :value="null" disabled>Ед.</option>
            <option v-for="(option, key) in data.measures" :key="key" :value="key">
              {{ option.title || option }}
            </option>
          </select>
          <BaseIcon
            viewBox="0 0 29 16"
            class="icon-dark-25-2 select-arrow"
            name="arrow"
          />
        </div>
      </div>
      <div v-if="errors[`products[${index}]`]" class="error-message">
        {{ formatErrorMessage(errors[`products[${index}]`]) }}
      </div>
    </div>
  </label>
</template>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;

.ingredient {
  display: flex;
  align-items: center;
  gap: 15px;
  width: 100%;
}

.ingredient__container {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
  border-bottom: 1px solid $text-info-light;
  padding: 10px 0;

  &.invalid {
    border-bottom: 2px solid $error;
  }
}

.ingredient__fields {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
}

.select-wrapper {
  position: relative;
}

.product-select {
  flex: 3 1 auto;
  min-width: 200px;
}

.measure-select {
  flex: 1 1 auto;
  min-width: 100px;
}

.custom-select {
  width: 100%;
  padding: 10px 40px 10px 10px;
  font-size: 20px;
  font-weight: 400;
  border: none;
  background: transparent;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;

  &:disabled {
    background: $background;
    cursor: not-allowed;
  }

  @media (max-width: 768px) {
    font-size: 18px;
    padding: 8px 36px 8px 8px;
  }

  @media (max-width: 480px) {
    font-size: 16px;
    padding: 6px 32px 6px 6px;
  }
}

.select-arrow {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;

  @media (max-width: 768px) {
    right: 8px;
    width: 22px;
    height: 22px;
  }

  @media (max-width: 480px) {
    right: 6px;
    width: 20px;
    height: 20px;
  }
}

.count-input {
  flex: 0 0 60px;
  text-align: center;
  border: none;
  padding: 10px 0;
  font-size: 20px;
  font-weight: 400;
  background: transparent;
  width: 30px;

  &:disabled {
    background: $background;
    cursor: not-allowed;
  }

  &::placeholder {
    font-weight: 300;
  }

  @media (max-width: 768px) {
    flex: 0 0 50px;
    font-size: 18px;
    padding: 8px 0;
  }

  @media (max-width: 480px) {
    flex: 0 0 40px;
    font-size: 16px;
    padding: 6px 0;
  }
}

.error-message {
  font-size: 16px;
  color: $error;
  margin-top: 4px;

  @media (max-width: 768px) {
    font-size: 14px;
  }

  @media (max-width: 480px) {
    font-size: 13px;
  }
}
</style>