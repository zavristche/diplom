import apiClient from './apiClient';

class UserService {
  register(data) {
    return apiClient.post('/api/register', data);
  }

  login(data) {
    return apiClient.post('/api/login', data);
  }
}

export default new UserService();