import apiClient from './apiClient';

class UserService {
  register(data) {
    return apiClient.post('/api/register', data);
  }

  login(data) {
    return apiClient.post('/api/login', data);
  }

  logout() {
    return apiClient.post('/api/logout');
  }
}

export default new UserService();