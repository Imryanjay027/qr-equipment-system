FROM php:8.2-apache

# I-update ang package lists at i-install ang mga kinakailangang dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Kopyahin ang project files sa tamang web directory ng container
COPY . /var/www/html/

# I-set ang tamang permissions kung kinakailangan
RUN chown -www-data:www-data /var/www/html -R
