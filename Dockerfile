FROM php:8.2-apache

# Ang PHP 8.2-apache ay may built-in na mysqli at pdo_mysql, i-enable lang natin kung kinakailangan
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Kopyahin ang buong project files sa web root ng Apache
COPY . /var/www/html/

# Ayusin ang permissions
RUN chown -R www-data:www-data /var/www/html
