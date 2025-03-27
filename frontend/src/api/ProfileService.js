import EntityService from './EntityService';

class ProfileService extends EntityService {
    constructor() {
        super('profile');
    }
}

export default new ProfileService();
