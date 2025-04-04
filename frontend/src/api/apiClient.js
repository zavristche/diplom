import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8080", //адрес API
  headers: {
    "Content-Type": "application/json",
  },
  timeout: 3000,
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => Promise.reject(error)
);

export default apiClient;
