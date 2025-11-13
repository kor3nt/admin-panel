FROM php:7.4-apache

# Install extension for PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Settings Apache
RUN a2enmod rewrite

# Set workspace
WORKDIR /var/www/html

# copy app files
COPY ./www /var/www/html

COPY ./config/default.conf /etc/apache2/sites-enabled/000-default.conf

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html