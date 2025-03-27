import EntityService from './EntityService';

class CollectionService extends EntityService {
    constructor() {
        super('collection');
    }
}

export default new CollectionService();
