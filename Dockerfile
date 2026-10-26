FROM php:8.2-apache

# Kopyahin ang buong project files sa web root ng Apache
COPY . /var/www/html/

# Ayusin ang permissions
RUN chown -R www-data:www-data /var/www/html
