import EntityService from './EntityService';

class RecipeService extends EntityService {
    constructor() {
        super('recipe'); // Указываем путь для рецептов
    }
}

export default new RecipeService();
