import EntityService from './EntityService';

class RecipeService extends EntityService {
    constructor() {
        super('recipe');
    }

    // getRandom() {
    //     return apiClient.get(`/${this.resource}/data`);
    // }
}

export default new RecipeService();
