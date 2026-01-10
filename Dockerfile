# This image should match the development environment
FROM php:8.4-apache
RUN a2enmod rewrite
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Allow .htaccess to override settings in /var/www/html
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Begin production-specific setup

# Install git and unzip for composer dependencies
RUN apt-get update
RUN apt-get install -y git unzip

# Copy only what is necessary for production
COPY content/ /var/www/content/
COPY html/ /var/www/html/
COPY src/ /var/www/src/
COPY composer.json /var/www/
COPY composer.lock /var/www/

# Install composer deps for production
WORKDIR /var/www/
RUN composer install --no-dev --optimize-autoloader