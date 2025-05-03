import EntityService from './EntityService';

class CommentService extends EntityService {
    constructor() {
        super('comment');
    }
}

export default new CommentService();