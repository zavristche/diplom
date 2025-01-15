import axios from 'axios';

const apiClient = axios.create({
    baseURL: 'http://cook/api', //адрес API
    headers: {
        'Content-Type': 'application/json',
    },
});

export default apiClient;
