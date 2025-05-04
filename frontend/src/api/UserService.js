import apiClient from "./apiClient";

class UserService {
  register(data) {
    return apiClient.post("/api/register", data);
  }

  login(data) {
    return apiClient.post("/api/login", data);
  }

  logout() {
    return apiClient.post("/api/logout");
  }

  getCurrentUser(authKey) {
    return apiClient.get("/api/user/current", {
      headers: {
        Authorization: `Bearer ${authKey}`,
      },
    });
  }

  search(params) {
    return apiClient.get(`/api/user/search`, { params });
  }
}

export default new UserService();
