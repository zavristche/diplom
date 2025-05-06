import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080", // Адрес API
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



