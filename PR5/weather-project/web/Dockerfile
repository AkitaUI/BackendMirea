FROM php:8.2-apache

# Устанавливаем расширения mysqli и pdo_mysql
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
  && docker-php-ext-install mysqli pdo pdo_mysql

# установить pecl и redis extension
RUN apt-get install -y make autoconf gcc g++ \
  && pecl channel-update pecl.php.net \
  && pecl install redis \
  && docker-php-ext-enable redis

# Включаем модуль rewrite
RUN a2enmod rewrite

# Копируем конфиг виртуального хоста
COPY apache-site.conf /etc/apache2/sites-available/000-default.conf

# Создадим нестандартную директорию как DocumentRoot
RUN mkdir -p /var/www/weather_site
WORKDIR /var/www/weather_site

# Права (в контейнере)
RUN chown -R www-data:www-data /var/www/weather_site

EXPOSE 80
