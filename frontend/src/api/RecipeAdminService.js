import apiClient from './apiClient';

export default class RecipeAdminService {
    constructor() {
        this.resource = 'api/admin/recipe';
    }

    getAll() {
        return apiClient.get(`/${this.resource}`);
    }

    getById(id) {
        return apiClient.get(`/${this.resource}/${id}`);
    }

    cancel(id, data) {
        console.log(`Sending cancel request for recipe_id: ${id}, data:`, data);
        return apiClient.patch(`/${this.resource}/${id}/cancel`, data);
    }

    apply(id) {
        return apiClient.post(`/${this.resource}/${id}/apply`);
    }
}