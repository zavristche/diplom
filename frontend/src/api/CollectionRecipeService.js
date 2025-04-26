import EntityService from './EntityService';

class CollectionRecipeService extends EntityService {
    constructor() {
        super('collection-recipe');
    }
}

export default new CollectionRecipeService();
