# Set up base image
FROM php:8.4-apache

# Install things and set settings
RUN apt-get update
RUN apt-get install -y git unzip
RUN a2enmod rewrite
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy only what is necessary for production
COPY content/ /var/www/content/
COPY html/ /var/www/html/
COPY src/ /var/www/src/
COPY composer.json /var/www/
COPY composer.lock /var/www/

# Install composer deps for production
WORKDIR /var/www/
RUN composer install --no-dev --optimize-autoloader