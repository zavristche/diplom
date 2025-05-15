<script setup>
import { defineProps, defineEmits, onMounted } from 'vue';
import BaseIcon from './BaseIcon.vue';
import Input from './Input.vue';
import Select from './Select.vue';

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
        <Select
          v-model="ingredient.product_id"
          :name="'products[' + index + '][product_id]'"
          placeholder="Выберите продукт"
          :options="data.products || {}"
          :disabled="!isEditable"
        />
        <Input
          v-model.number="ingredient.count"
          :name="'products[' + index + '][count]'"
          type="number"
          placeholder="N"
          :disabled="!isEditable"
          class="count-input"
        />
        <Select
          v-model="ingredient.measure_id"
          :name="'products[' + index + '][measure_id]'"
          placeholder="Ед."
          :options="data.measures || {}"
          :disabled="!isEditable"
        />
      </div>
      <div v-if="errors[`products[${index}]`]" class="error-message">
        {{ errors[`products[${index}]`] }}
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

  .ingredient__container {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
    border-bottom: 1px solid $text-info-light;
    padding: 10px 0;

    &.invalid {
      border-bottom: 2px solid $error;
    }

    .ingredient__fields {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;

      .count-input {
        padding: 15px 20px;
        font-size: 20px;
        font-weight: 400;
        border: none;
        border-bottom: 1px solid $text-info-light;
        flex-shrink: 0;
        min-width: 80px;
        max-width: 120px;
        text-align: center;

        &:disabled {
          background: $background;
          cursor: not-allowed;
        }

        @media (max-width: 768px) {
          padding: 12px 16px;
          font-size: 18px;
          min-width: 60px;
          max-width: 100px;
        }

        @media (max-width: 480px) {
          padding: 10px 14px;
          font-size: 16px;
          min-width: 50px;
          max-width: 80px;
        }
      }

      .custom-select {
        padding-right: 40px;
      }

      .select-arrow {
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
      }

      > *:first-child {
        flex: 3 1 200px; // Основной селект растягивается больше
        min-width: 200px;
      }

      > *:nth-child(2) {
        flex: 1 1 80px; // Инпут для количества
        min-width: 80px;
        max-width: 120px;
      }

      > *:last-child {
        flex: 2 1 120px; // Селект для меры
        min-width: 120px;
        max-width: 150px;
      }

      @media (max-width: 768px) {
        > *:first-child {
          flex: 3 1 150px;
          min-width: 150px;
        }

        > *:nth-child(2) {
          flex: 1 1 60px;
          min-width: 60px;
          max-width: 100px;
        }

        > *:last-child {
          flex: 2 1 100px;
          min-width: 100px;
          max-width: 130px;
        }
      }

      @media (max-width: 480px) {
        > *:first-child {
          flex: 3 1 120px;
          min-width: 120px;
        }

        > *:nth-child(2) {
          flex: 1 1 50px;
          min-width: 50px;
          max-width: 80px;
        }

        > *:last-child {
          flex: 2 1 80px;
          min-width: 80px;
          max-width: 110px;
        }
      }
    }
  }
}
</style>