<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useProfileAuth } from '../composables/useProfileAuth';
import { useAuthStore } from '../stores/auth';
import CommentService from '../api/CommentService';
import BaseIcon from './BaseIcon.vue';
import Login from "../components/Login.vue";

const props = defineProps({
  comments: { type: Array, required: true, default: () => [] },
  recipeId: { type: [Number, String], required: true },
});

const isLoginOpen = ref(false);
const { isAuthenticated, currentUser } = useProfileAuth();
const authStore = useAuthStore();

const commentText = ref('');
const commentPhoto = ref(null);
const previewUrl = ref(null);
const formVisibility = ref({});
const replyText = ref({});
const replyPhoto = ref({});
const replyPreviewUrl = ref({});
const errors = ref({ main: null });
const isSubmitting = ref(false);

const localComments = ref([...props.comments]);

watch(() => props.comments, (newComments) => {
  const existingIds = new Set(localComments.value.map(c => c.id));
  const newCommentsToAdd = newComments.filter(c => !existingIds.has(c.id));
  localComments.value = [...localComments.value, ...newCommentsToAdd];
  console.log('Synced localComments:', localComments.value);
}, { deep: true });

const groupedComments = computed(() => {
  const commentMap = new Map();
  localComments.value.forEach(comment => {
    commentMap.set(comment.id, { ...comment, replies: [] });
  });

  const result = [];
  localComments.value.forEach(comment => {
    if (comment.answer_id) {
      const parent = commentMap.get(comment.answer_id);
      if (parent) {
        parent.replies.push(commentMap.get(comment.id));
      } else {
        console.warn(`Parent comment ${comment.answer_id} not found, adding to result`);
        result.push(commentMap.get(comment.id));
      }
    } else {
      result.push(commentMap.get(comment.id));
    }
  });

  const setLevels = (comments, level = 0) => {
    comments.forEach(comment => {
      comment.level = level;
      if (comment.replies && comment.replies.length > 0) {
        setLevels(comment.replies, level + 1);
      }
    });
  };

  setLevels(result);
  console.log('Comment tree:', result);

  const parseDate = (dateStr) => {
    if (!dateStr) return new Date(0);
    if (dateStr.includes(' в ')) {
      const [date, time] = dateStr.split(' в ');
      const [day, month, year] = date.split('.');
      return new Date(`${year}-${month}-${day}T${time}:00`);
    }
    return new Date(dateStr);
  };

  const flattenComments = (comments, acc = []) => {
    comments
      .sort((a, b) => parseDate(b.created_at) - parseDate(a.created_at))
      .forEach(comment => {
        acc.push(comment);
        if (comment.replies && comment.replies.length > 0) {
          flattenComments(comment.replies, acc);
        }
      });
    return acc;
  };

  const flattened = flattenComments(result);
  console.log('Grouped comments:', flattened);
  return flattened;
});

const onFileSelected = (event, commentId = null) => {
  const file = event.target.files[0];
  if (file) {
    const url = URL.createObjectURL(file);
    if (commentId) {
      replyPhoto.value[commentId] = file;
      replyPreviewUrl.value[commentId] = url;
      replyText.value[commentId] = replyText.value[commentId] || '';
    } else {
      commentPhoto.value = file;
      previewUrl.value = url;
    }
  }
};

const submitComment = async (parentId = null) => {
  console.log('Initial localComments:', localComments.value);
  const text = parentId ? replyText.value[parentId] || '' : commentText.value;
  const photo = parentId ? replyPhoto.value[parentId] : commentPhoto.value;

  const formData = new FormData();
  formData.append('recipe_id', props.recipeId);
  formData.append('text', text);
  if (photo) formData.append('photo', photo);
  if (parentId) formData.append('answer_id', Number(parentId));

  try {
    isSubmitting.value = true;
    if (parentId) {
      errors.value[parentId] = null;
    } else {
      errors.value.main = null;
    }
    const response = await CommentService.create(formData);
    console.log('API response:', response.data);

    if (response.data.success) {
      const newComment = {
        id: response.data.collection.id,
        recipe_id: props.recipeId,
        text: response.data.collection.text,
        photo: response.data.collection.photo || null,
        created_at: response.data.collection.created_at,
        user: {
          id: authStore.user.id,
          login: authStore.user.login,
          avatar: authStore.avatar,
        },
        answer_id: parentId || null,
        answer: parentId ? localComments.value.find(c => c.id === parentId) || null : null,
        replies: [],
      };

      localComments.value.push(newComment);
      console.log('New comment added:', newComment);
      console.log('Updated localComments:', localComments.value);

      if (parentId) {
        replyText.value[parentId] = '';
        replyPhoto.value[parentId] = null;
        replyPreviewUrl.value[parentId] = null;
        formVisibility.value[parentId] = false;
      } else {
        commentText.value = '';
        commentPhoto.value = null;
        previewUrl.value = null;
      }
    } else {
      const errorMsg = response.data.message || 'Ошибка: API вернул success=false';
      if (parentId) {
        errors.value[parentId] = errorMsg;
      } else {
        errors.value.main = errorMsg;
      }
      console.error('API error:', response.data);
    }
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'Ошибка при отправке комментария';
    if (parentId) {
      errors.value[parentId] = errorMsg;
    } else {
      errors.value.main = errorMsg;
    }
    console.error('Request error:', err);
  } finally {
    isSubmitting.value = false;
  }
};

const deleteComment = async (commentId) => {
  if (!confirm('Вы уверены, что хотите удалить комментарий?')) return;

  try {
    console.log('Deleting comment ID:', commentId);
    const response = await CommentService.delete(commentId);
    if (response.data.success) {
      console.log('Before deletion:', localComments.value);
      localComments.value = removeCommentAndReplies(localComments.value, commentId);
      console.log('After deletion:', localComments.value);
    } else {
      errors.value.main = response.data.message || 'Ошибка при удалении комментария';
    }
  } catch (err) {
    errors.value.main = err.response?.data?.message || 'Ошибка при удалении комментария';
    console.error('Delete error:', err);
  }
};

// Рекурсивное удаление комментария и всех его дочерних комментариев
const removeCommentAndReplies = (commentsArray, commentId) => {
  // Собираем все id дочерних комментариев рекурсивно
  const collectChildIds = (parentId, childIds = new Set()) => {
    commentsArray.forEach((comment) => {
      if (comment.answer_id === parentId) {
        childIds.add(comment.id);
        collectChildIds(comment.id, childIds);
      }
    });
    return childIds;
  };

  const childIds = collectChildIds(commentId);
  // Удаляем сам комментарий и все его дочерние
  return commentsArray.filter((comment) => comment.id !== commentId && !childIds.has(comment.id));
};

const toggleReplyForm = (event, commentId) => {
  event.stopPropagation();
  formVisibility.value[commentId] = !formVisibility.value[commentId];
  replyText.value[commentId] = replyText.value[commentId] || '';
};

const closeReplyForms = (event) => {
  const replyForms = document.querySelectorAll('.reply-form');
  const commentActions = document.querySelectorAll('.comment-actions');
  const isClickInActions = Array.from(commentActions).some(action => action.contains(event.target));

  if (!isClickInActions) {
    replyForms.forEach(form => {
      if (!form.contains(event.target)) {
        Object.keys(formVisibility.value).forEach(commentId => {
          formVisibility.value[commentId] = false;
        });
      }
    });
  }
};

onMounted(() => {
  console.log('Initial props.comments:', props.comments);
  document.addEventListener('click', closeReplyForms);
});

onUnmounted(() => {
  document.removeEventListener('click', closeReplyForms);
});
</script>

<template>
  <Login :isOpen="isLoginOpen" @close="isLoginOpen = false" />
  <section class="comments">
    <h2>Комментарии</h2>
    <div v-if="errors.main" class="error-message">{{ errors.main }}</div>
    <form v-if="isAuthenticated && !authStore.isAdmin" @submit.prevent="submitComment()" class="comment-form">
      <textarea
        v-model="commentText"
        rows="4"
        cols="50"
        placeholder="Введите текст комментария"
        :disabled="isSubmitting"
      ></textarea>
      <div class="btn-group end">
        <div v-if="previewUrl" class="preview-container">
          <img :src="previewUrl" alt="Превью" class="preview-img" />
          <span class="file-name">{{ commentPhoto?.name }}</span>
        </div>
        <label class="btn-dark line">
          <input
            type="file"
            name="photo"
            accept="image/*"
            style="display: none"
            @change="onFileSelected"
            :disabled="isSubmitting"
          />
          <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
          Загрузить фото
        </label>
        <button class="btn-dark" type="submit" :disabled="isSubmitting">Отправить</button>
      </div>
    </form>
    <div v-else-if="authStore.isAdmin" class="no-auth-message">
      Администратор не может оставлять комментарии
    </div>
    <div v-else class="no-auth-message">
      <a class="link" @click="isLoginOpen = true">Войдите</a>, чтобы оставить комментарий.
    </div>
    <div v-if="groupedComments.length === 0" class="no-comments">Комментариев пока нет. Будьте первыми!</div>
    <div v-else class="comment-list">
      <div
        v-for="comment in groupedComments"
        :key="comment.id"
        class="comment"
        :class="{ 'reply-comment': comment.level >= 1 }"
        :style="{ 'margin-left': comment.level * 20 + 'px' }"
      >
        <span class="time">{{ comment.created_at }}</span>
        <div class="author">
          <img :src="comment.user.avatar" alt="Аватар" />
          {{ comment.user.login }}
        </div>
        <p>{{ comment.text }}</p>
        <div v-if="comment.photo" class="comment-photo">
          <img :src="comment.photo" alt="Фото комментария" />
        </div>
        <div v-if="isAuthenticated && !authStore.isAdmin" class="comment-actions">
          <button class="btn-small" @click="toggleReplyForm($event, comment.id)">Ответить</button>
          <button
            v-if="comment.user.id === currentUser?.id"
            class="btn-small"
            @click="deleteComment(comment.id)"
          >Удалить</button>
        </div>
        <form
          v-if="formVisibility[comment.id] && isAuthenticated"
          @submit.prevent="submitComment(comment.id)"
          class="comment-form reply-form"
        >
          <div v-if="errors[comment.id]" class="error-message">{{ errors[comment.id] }}</div>
          <textarea
            v-model="replyText[comment.id]"
            rows="4"
            cols="50"
            placeholder="Введите текст ответа"
            :disabled="isSubmitting"
          ></textarea>
          <div class="btn-group end">
            <div v-if="replyPreviewUrl[comment.id]" class="preview-container">
              <img :src="replyPreviewUrl[comment.id]" alt="Превью" class="preview-img" />
              <span class="file-name">{{ replyPhoto[comment.id]?.name }}</span>
            </div>
            <label class="btn-dark line">
              <input
                type="file"
                accept="image/*"
                style="display: none"
                @change="onFileSelected($event, comment.id)"
                :disabled="isSubmitting"
              />
              <BaseIcon viewBox="0 0 29 29" class="icon-dark-30-1" name="img" />
              Загрузить фото
            </label>
            <button class="btn-dark" type="submit" :disabled="isSubmitting">Отправить</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<style lang="scss" scoped>
@use "../assets/styles/variables" as *;

.comments {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.comment-form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 30px;

  textarea {
    box-shadow: 0rem 0rem 0.61rem 0rem rgba(0, 0, 0, 0.1);
    background: $background;
    border-radius: 25px;
    padding: 30px;
    font-family: Rubik;
  }
}

.reply-form {
  margin-top: 20px;
  margin-left: 20px;
  padding-left: 10px;
}

.reply-comment {
  border-left: 1px solid $text-info-light;
  padding-left: 20px;
}

.comment-list {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.comment {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 15px;
}

.time {
  color: $text-info;
  font-size: 16px;
}

.author {
  display: flex;
  align-items: center;
  flex-direction: row;
  gap: 10px;
  font-family: Rubik;
  font-weight: 400;

  img {
    object-fit: cover;
    width: 40px;
    height: 40px;
    border-radius: 100%;
  }
}

.reply-to {
  color: $text-info;
  font-size: 16px;
}

.comment-photo {
  margin-top: 10px;

  img {
    max-width: 70%;
    object-fit: cover;
    border-radius: $border;
    box-shadow: $shadow;
  }
}

.comment-actions {
  display: flex;
  gap: 20px;
}

.preview-container {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 20px;
  color: $text-info;
}

.preview-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: $border;
}

.error-message {
  font-size: 16px;
  color: $error;
  text-align: center;
  padding: 10px;
  background-color: rgba($error, 0.1);
  border-radius: $border;
}

.no-auth-message {
  color: $text-info;
  display: flex;

  .link {
    padding: 0 0 0 3px;
  }
}

.no-comments {
  text-align: center;
  color: $text-info;
}
</style>