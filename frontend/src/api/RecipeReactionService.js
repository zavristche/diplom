import apiClient from './apiClient';

class RecipeReactionService {
  constructor() {
    this.resource = 'api/recipe-reaction';
  }

  create(data) {
    return apiClient.post(`/${this.resource}`, data);
  }

  delete(data) {
    return apiClient.delete(`/${this.resource}`, { data });
  }

  check(data) {
    console.log(`Checking reaction with data:`, data);
    return apiClient
      .post(`/${this.resource}/check`, data)
      .catch(error => {
        console.error('Error checking reaction:', error);
        if (error.response?.status === 401) {
          console.warn('Unauthorized: Token missing or invalid, defaulting to not liked');
          return { liked: false, count: 0 }; // Дефолтные значения при отсутствии авторизации
        }
        throw error;
      });
  }
}

export default new RecipeReactionService();