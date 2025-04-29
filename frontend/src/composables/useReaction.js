import { ref } from 'vue';
import RecipeReactionService from '../api/RecipeReactionService';
import CollectionReactionService from '../api/CollectionReactionService';
import { useAuthStore } from '../stores/auth';

export function useReaction(entityType, entityId) {
  const authStore = useAuthStore();
  const isLiked = ref(false);
  const isLoading = ref(false);
  const error = ref(null);

  const service = entityType === 'recipe' ? RecipeReactionService : CollectionReactionService;
  const idKey = entityType === 'recipe' ? 'recipe_id' : 'collection_id';

  // Ключ для кэширования
  const cacheKey = `like_${entityType}_${entityId}_${authStore.user?.id || 'guest'}`;

  // Проверка, лайкнул ли пользователь
  const checkInitialLike = () => {
    // Проверяем кэш
    const cachedLike = localStorage.getItem(cacheKey);
    if (cachedLike !== null) {
      isLiked.value = cachedLike === 'true';
      return Promise.resolve();
    }

    if (!authStore.isAuthenticated) {
      return Promise.resolve();
    }

    isLoading.value = true;
    const data = { [idKey]: entityId, user_id: authStore.user.id };

    return service.check(data)
      .then((response) => {
        if (response.data.success) {
          isLiked.value = response.data.isLiked || false;
          // Сохраняем в кэш
          localStorage.setItem(cacheKey, isLiked.value.toString());
        }
      })
      .catch((err) => {
        console.error('Ошибка проверки лайка:', err);
      })
      .finally(() => {
        isLoading.value = false;
      });
  };

  // Переключение лайка
  const toggleLike = async () => {
    if (!authStore.isAuthenticated) {
      error.value = 'Пожалуйста, войдите, чтобы оценить';
      alert(error.value);
      error.value = null;
      return;
    }

    isLoading.value = true;
    error.value = null;

    const data = { [idKey]: entityId, user_id: authStore.user.id };

    try {
      if (isLiked.value) {
        const response = await service.delete(data);
        if (response.data.success) {
          isLiked.value = false;
          localStorage.setItem(cacheKey, 'false');
        } else {
          error.value = response.data.message || 'Ошибка при удалении лайка';
          alert(error.value);
        }
      } else {
        const response = await service.create(data);
        if (response.data.success) {
          isLiked.value = true;
          localStorage.setItem(cacheKey, 'true');
        } else {
          error.value = response.data.message || 'Ошибка при добавлении лайка';
          alert(error.value);
        }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка сервера';
      alert(error.value);
    } finally {
      isLoading.value = false;
      error.value = null;
    }
  };

  // Запускаем проверку сразу, но не ждём её
  checkInitialLike();

  return { isLiked, isLoading, toggleLike, checkInitialLike };
}