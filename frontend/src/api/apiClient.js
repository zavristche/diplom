import axios from "axios";

const apiClient = axios.create({
  baseURL: `http://${window.location.hostname}:8080`,
  timeout: 3000,
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => Promise.reject(error)
);

export function setupAuthInterceptor(authStore) {
  apiClient.interceptors.request.use((config) => {

    if (authStore.authKey) {
      config.headers["Authorization"] = `Bearer ${authStore.authKey}`;
    }

    if (config.data instanceof FormData) {
      config.headers["Content-Type"] = "multipart/form-data";
    }
    return config;
  });
}

export default apiClient;



