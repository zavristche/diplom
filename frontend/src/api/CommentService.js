import EntityService from './EntityService';

class CommentService extends EntityService {
    constructor() {
        super('comment');
    }

    // // Переопределяем create для поддержки FormData
    // create(data) {
    //     return apiClient.post(`/${this.resource}`, data, {
    //         headers: {
    //             'Content-Type': 'multipart/form-data',
    //         },
    //     });
    // }
}

export default new CommentService();