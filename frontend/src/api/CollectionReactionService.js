import apiClient from './apiClient';

class CollectionReactionService {
  constructor() {
    this.resource = 'api/collection-reaction';
  }

  create(data) {
    return apiClient.post(`/${this.resource}`, data);
  }

  delete(data) {
    return apiClient.delete(`/${this.resource}`, { data });
  }

  // Проверка, лайкнул ли пользователь
  check(data) {
    return apiClient.post(`/${this.resource}/check`, data);
  }
}

export default new CollectionReactionService();