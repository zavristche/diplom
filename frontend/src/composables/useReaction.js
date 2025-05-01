import { ref } from "vue";
import { useAuthStore } from "../stores/auth";
import RecipeReactionService from "../api/RecipeReactionService";
import CollectionReactionService from "../api/CollectionReactionService";

export function useReaction(entityType, entityId, initialLikes) {
  const authStore = useAuthStore();
  const isLiked = ref(false);
  const likesCount = ref(initialLikes || 0); // Инициализируем количеством лайков из пропса

  console.log(`useReaction initialized with entityType: ${entityType}, entityId: ${entityId}, initialLikes: ${initialLikes}`);

  const service = entityType === "recipe" ? RecipeReactionService : CollectionReactionService;
  const entityKey = entityType === "recipe" ? "recipe_id" : "collection_id";

  console.log(`Using service: ${entityType === "recipe" ? "RecipeReactionService" : "CollectionReactionService"}`);

  const checkInitialLike = async () => {
    if (!authStore.isAuthenticated) {
      isLiked.value = false;
      return;
    }

    try {
      const response = await service.check({
        [entityKey]: entityId,
        user_id: authStore.userId,
      });
      console.log(`Check response for ${entityType}:`, response.data);
      isLiked.value = response.data.isLiked || false;
    } catch (error) {
      console.error(`Ошибка при проверке лайка для ${entityType}:`, error);
      isLiked.value = false;
    }
  };

  const toggleLike = async () => {
    if (!authStore.isAuthenticated) {
      alert("Пожалуйста, войдите в систему, чтобы оставить реакцию.");
      return;
    }

    try {
      if (isLiked.value) {
        console.log(`Removing like for ${entityType} with ID ${entityId}`);
        await service.delete({
          [entityKey]: entityId,
          user_id: authStore.userId,
        });
        isLiked.value = false;
        likesCount.value -= 1; // Уменьшаем количество лайков
      } else {
        console.log(`Adding like for ${entityType} with ID ${entityId}`);
        const response = await service.create({
          [entityKey]: entityId,
          user_id: authStore.userId,
        });
        if (!response.data.success) {
          alert(response.data.message);
          return;
        }
        isLiked.value = true;
        likesCount.value += 1; // Увеличиваем количество лайков
      }
    } catch (error) {
      console.error(`Ошибка при переключении лайка для ${entityType}:`, error);
      const message = error.response?.data?.message || error.message;
      alert(`Не удалось ${isLiked.value ? "убрать" : "добавить"} лайк: ${message}`);
    }
  };

  return {
    isLiked,
    toggleLike,
    checkInitialLike,
    likesCount, // Возвращаем локальное состояние для количества лайков
  };
}