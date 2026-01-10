# Set up image to match production environment
FROM php:8.4-apache
RUN a2enmod rewrite
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Allow .htaccess to override settings in /var/www/html
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Begin dev environment setup

# Run apt update
RUN apt-get update

# Install git
RUN apt-get install -y git

# Enable xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
RUN echo 'xdebug.mode=debug,develop' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.start_with_request=trigger' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.client_port=9003' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
