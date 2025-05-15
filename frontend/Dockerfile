FROM node:20-alpine

WORKDIR /app

# Кэшируем зависимости отдельно
COPY package.json package-lock.json ./
RUN npm ci --no-cache --legacy-peer-deps

# Копируем остальные файлы
COPY . .

# Устанавливаем переменные окружения
ENV VITE_HOST=0.0.0.0
ENV VITE_PORT=5173
ENV NODE_ENV=development

# Фикс прав для Alpine
RUN chown -R node:node /app
USER node

EXPOSE 5173
CMD ["npm", "run", "dev"]