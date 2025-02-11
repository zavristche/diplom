# Используем официальный образ Node.js
FROM node:20

# Устанавливаем рабочую директорию
WORKDIR /app

# Копируем package.json и package-lock.json для установки зависимостей
COPY package*.json ./

# Устанавливаем зависимости
RUN npm install

# Копируем остальные файлы проекта
COPY . .

# Открываем порт для dev-сервера
EXPOSE 8282

# Устанавливаем переменные для корректной работы горячей перезагрузки
ENV CHOKIDAR_USEPOLLING=true

# Запускаем Vue CLI сервер
CMD ["npm", "run", "serve"]