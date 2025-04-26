// src/api/apiClient.js
import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080", // адрес API
  timeout: 3000,
});

// Интерцептор для обработки ответов
apiClient.interceptors.response.use(
  (response) => response,
  (error) => Promise.reject(error)
);

// Функция для настройки интерцептора запросов
export function setupAuthInterceptor(authStore) {
  apiClient.interceptors.request.use((config) => {
    if (authStore.authKey) {
      config.headers["Authorization"] = `Bearer ${authStore.authKey}`;
    }
    return config;
  });
}

export default apiClient;