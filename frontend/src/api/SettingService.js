import EntityService from './EntityService';

class SettingService extends EntityService {
    constructor() {
        super('profile/setting/account');
    }
}

export default new SettingService();
