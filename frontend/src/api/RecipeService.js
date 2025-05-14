import EntityService from "./EntityService";
import apiClient from './apiClient'; // Добавляем импорт apiClient

class RecipeService extends EntityService {
  constructor() {
    super("recipe");
  }

  getRandom() {
    return apiClient.get(`/${this.resource}/random`);
  }
}

export default new RecipeService();
