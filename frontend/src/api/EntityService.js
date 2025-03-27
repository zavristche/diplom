import apiClient from './apiClient';

export default class EntityService {
    constructor(resource) {
        this.resource = 'api/' + resource;
    }

    getAll() {
        return apiClient.get(`/${this.resource}`);
    }

    getById(id) {
        return apiClient.get(`/${this.resource}/${id}`);
    }

    getCreateData() {
        return apiClient.get(`/${this.resource}/create-data`);
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
