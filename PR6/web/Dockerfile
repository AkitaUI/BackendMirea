FROM php:8.2-apache

# Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# GD для работы с изображениями (водяной знак)
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads

