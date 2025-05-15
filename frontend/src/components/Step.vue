<script setup>
import { ref, watch } from 'vue';
import BaseIcon from './BaseIcon.vue';

const props = defineProps({
  step: { type: Object, required: true },
  index: { type: Number, required: true },
  mode: { type: String, default: 'view', validator: (value) => ['edit', 'view'].includes(value) },
  isEditable: { type: Boolean, default: true },
  errors: { type: Object, default: () => ({}) },
});

defineEmits(['remove', 'file-selected']);

const textareaRef = ref(null);

// Отладка пропсов
watch(
  () => props,
  (newProps) => {
    console.log('Step.vue props:', {
      step: newProps.step,
      index: newProps.index,
      mode: newProps.mode,
      isEditable: newProps.isEditable,
      errors: newProps.errors,
    });
  },
  { immediate: true }
);
</script>

<template>
  <section class="step">
    <div v-if="mode === 'edit' && isEditable" class="step-actions">
      <button class="btn-light btn-icon btn-small" @click="$emit('remove', index)">
        <BaseIcon viewBox="0 0 65 65" class="icon-white-65-2" name="close" />
      </button>
    </div>
    <div v-if="mode === 'edit'" class="photo-upload-wrapper">
      <label v-if="isEditable" class="btn-dark line full-width">
        <input
          type="file"
          accept="image/*"
          :name="'step_photos[' + index + ']'"
          style="display: none"
          @change="$emit('file-selected', $event, index)"
        />
        <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
        Загрузить фото
      </label>
      <div v-if="errors[`step_photos_${index}`]" class="photo-error">
        {{ errors[`step_photos_${index}`] }}
      </div>
    </div>
    <div v-if="step.previewUrl || step.photo" class="step-preview">
      <img :src="step.previewUrl || step.photo" :alt="mode === 'view' ? '' : 'Превью шага'" />
    </div>
    <div class="step__info">
      <div v-if="mode === 'edit'" class="input-title-wrapper">
        <input
          type="text"
          class="input-title"
          :value="`Шаг ${index + 1}`"
          readonly
        />
      </div>
      <h3 v-else>{{ step.title || `Шаг ${index + 1}` }}</h3>
      <div v-if="mode === 'edit'" class="input-description-wrapper">
        <textarea
          v-model="step.description"
          ref="textareaRef"
          :name="'steps[' + index + '][description]'"
          class="input-description"
          placeholder="Описание"
          rows="3"
          :class="{ invalid: errors[`steps[${index}][description]`] }"
          :disabled="!isEditable"
        ></textarea>
        <div v-if="errors[`steps[${index}][description]`]" class="error-message">
          {{ errors[`steps[${index}][description]`] }}
        </div>
      </div>
      <p v-else>{{ step.description }}</p>
    </div>
  </section>
</template>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;
@use "../assets/styles/form" as *;

.step {
  display: flex;
  flex-direction: column;
  gap: 1.875rem; // 30px
  width: 100%;
}

.step-actions {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 0.625rem; // 10px
}

.photo-upload-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.125rem; // 2px
  width: 100%;
}

.btn-dark.full-width {
  width: 100%;
  box-sizing: border-box;
}

.step-preview {
  display: flex;
  flex-shrink: 0;
  width: 100%;
  height: 18.75rem; // 300px
  aspect-ratio: 4/3; // Прямоугольное соотношение сторон
  img {
    box-shadow: $shadow;
    object-fit: cover;
    width: 100%;
    height: 100%;
    border-radius: $border;
  }
}

.step__info {
  display: flex;
  flex-direction: column;
  gap: 0.625rem; // 10px;

  h3 {
    font-size: 1.5625rem; // 25px
    font-weight: 500;
    color: $background-dark;
  }

  p {
    line-height: 150%;
    font-size: 1rem; // 16px
    color: $text-info;
  }
}

.photo-error {
  font-size: 1rem; // 16px
  color: $error;
  margin-top: 0.125rem; // 2px
}

@media (max-width: 1200px) {
  .step {
    gap: 1.25rem; // 20px
  }

  .step-preview {
    height: 15.625rem; // 250px
  }

  .step__info {
    h3 {
      font-size: 1.375rem; // 22px
    }

    p {
      font-size: 0.875rem; // 14px
    }
  }

  .photo-error {
    font-size: 0.875rem; // 14px
  }
}

@media (max-width: 768px) {
  .step {
    gap: 1rem; // 16px
  }

  .step-preview {
    height: 12.5rem; // 200px
  }

  .step__info {
    h3 {
      font-size: 1.25rem; // 20px
    }

    p {
      font-size: 0.75rem; // 12px
    }
  }

  .photo-error {
    font-size: 0.75rem; // 12px
  }
}

@media (max-width: 480px) {
  .step {
    gap: 0.75rem; // 12px
  }

  .step-actions {
    margin-bottom: 0.5rem; // 8px
  }

  .step-preview {
    height: 9.375rem; // 150px
  }

  .step__info {
    h3 {
      font-size: 1rem; // 16px
    }

    p {
      font-size: 0.625rem; // 10px
    }
  }

  .photo-error {
    font-size: 0.625rem; // 10px
  }
}
</style>