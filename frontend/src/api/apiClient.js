import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080", // Адрес API
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
    // Добавляем Authorization, если есть authKey
    if (authStore.authKey) {
      config.headers["Authorization"] = `Bearer ${authStore.authKey}`;
    }
    // Устанавливаем Content-Type для FormData
    if (config.data instanceof FormData) {
      config.headers["Content-Type"] = "multipart/form-data";
    }
    return config;
  });
}

export default apiClient;