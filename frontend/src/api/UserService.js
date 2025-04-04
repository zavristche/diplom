import apiClient from './apiClient';

class UserService {
  register(data) {
    return apiClient.post('/api/register', data);
  }
}

export default new UserService();