# Gamitin ang opisyal na PHP image na may Apache
FROM php:8.2-apache

# I-install ang MySQL extensions para kumonekta sa database
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Kopyahin ang project files sa tamang web directory ng container
COPY . /var/www/html/

# Gumawa ng uploads folder at i-set sa 777 permission para hindi mag-permission denied
RUN mkdir -p /var/www/html/uploads && chmod -R 777 /var/www/html/uploads

# I-enable ang Apache rewrite module kung kailangan
RUN a2enmod rewrite
