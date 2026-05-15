FROM php:8.2-apache

# Instalar extensiones necesarias para MySQL/PDO
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
