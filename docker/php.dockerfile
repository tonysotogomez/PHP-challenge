FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev libzip-dev zip unzip git \
    && docker-php-ext-install pdo_mysql gd zip

WORKDIR /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer