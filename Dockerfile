FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

WORKDIR /var/www/html

COPY ./backend /var/www/html

RUN chown -R www-data:www-data /var/www/html

CMD ["apache2-foreground"]