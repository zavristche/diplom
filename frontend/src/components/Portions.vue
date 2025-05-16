
  <script setup>
  import { defineProps, defineEmits } from 'vue';
  import BaseIcon from './BaseIcon.vue';
  import Input from './Input.vue';
  
  const props = defineProps({
    portions: {
      type: Number,
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
  
  const emit = defineEmits(['update:portions']);
  
  const updatePortions = (delta) => {
    if (props.portions + delta >= 1) {
      emit('update:portions', props.portions + delta);
    }
  };
  </script>
  
  <template>
    <div class="portions-container">
      <span>Порции</span>
      <div class="portions">
        <button type="button" @click="updatePortions(-1)" :disabled="!isEditable">
          <BaseIcon viewBox="0 0 65 65" class="icon-dark-45-2" name="minus" />
        </button>
        <Input
          :model-value="portions"
          name="portions"
          type="number"
          :is-invalid="!!errors.portions"
          :error-message="errors.portions"
          :disabled="!isEditable"
          @update:model-value="emit('update:portions', Number($event))"
        />
        <button type="button" @click="updatePortions(1)" :disabled="!isEditable">
          <BaseIcon viewBox="0 0 65 65" class="icon-dark-45-2" name="plus" />
        </button>
      </div>
    </div>
  </template>
  
  <style lang="scss" scoped>
  @use "../assets/styles/variables" as *;
  
  .portions-container {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 0.625rem; // 10px
    font-weight: 400;
    width: 100%;
    justify-content: flex-end;
  }
  
  .portions {
    display: flex;
    flex-direction: row;
    align-items: center;
    border: 1px solid $text-info;
    border-radius: $border;
    font-size: 1.5rem; // 24px
    font-weight: 400;
  
    .input-form {
      width: 4.375rem; // 70px
      text-align: center;
      border: none;
      background: transparent;
      padding: 0.625rem 0; // 10px
      font-size: 1.5rem; // 24px
      line-height: 1;
  
      &:focus {
        outline: none;
      }
  
      &.invalid {
        border: none;
        box-shadow: inset 0 0 0 2px $error;
      }
  
      &:disabled {
        background: transparent;
        cursor: not-allowed;
      }
    }
  }
  </style>
