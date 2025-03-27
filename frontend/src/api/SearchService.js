import EntityService from './EntityService';
import apiClient from './apiClient'; // Добавляем импорт apiClient

class SearchService extends EntityService {
    constructor() {
        super('search');
    }

    getData() {
        return apiClient.get(`/${this.resource}/data`);
    }
}

export default new SearchService();