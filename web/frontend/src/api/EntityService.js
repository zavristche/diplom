import apiClient from './apiClient';

export default class EntityService {
    constructor(resource) {
        this.resource = resource;
    }

    getAll() {
        return apiClient.get(`/${this.resource}`);
    }

    getById(id) {
        return apiClient.get(`/${this.resource}/${id}`);
    }

    create(data) {
        return apiClient.post(`/${this.resource}`, data);
    }

    update(id, data) {
        return apiClient.patch(`/${this.resource}/${id}`, data);
    }

    delete(id) {
        return apiClient.delete(`/${this.resource}/${id}`);
    }
}
